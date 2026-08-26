<?php

declare(strict_types=1);

namespace AmarMayor\Controllers\Web;

use AmarMayor\Auth\Auth;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\City\CityStructureService;
use AmarMayor\Domain\ComplaintConfig\TaxonomyService;
use AmarMayor\Domain\ComplaintCore\ComplaintService;
use AmarMayor\Domain\ResolutionQuality\ResolutionService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Security;
use AmarMayor\Support\Translator;
use PDO;

class ComplaintWebController
{
    private ComplaintService $complaintService;
    private TaxonomyService $taxonomyService;
    private CityStructureService $cityService;
    private ResolutionService $resolutionService;

    public function __construct(
        ?ComplaintService $complaintService = null,
        ?TaxonomyService $taxonomyService = null,
        ?CityStructureService $cityService = null,
        ?ResolutionService $resolutionService = null
    ) {
        $this->complaintService = $complaintService ?: new ComplaintService();
        $this->taxonomyService = $taxonomyService ?: new TaxonomyService();
        $this->cityService = $cityService ?: new CityStructureService();
        $this->resolutionService = $resolutionService ?: new ResolutionService();
    }

    /**
     * Show complaint submission form.
     */
    public function create(Request $request): Response
    {
        $categories = $this->taxonomyService->getCategories(true);
        $zones = $this->cityService->getZones(true);

        return view('complaints/create', [
            'locale' => Translator::getLocale(),
            'categories' => $categories,
            'zones' => $zones,
            'user' => Auth::user(),
            'error' => null,
        ]);
    }

    /**
     * Store submitted complaint.
     */
    public function store(Request $request): Response
    {
        $user = Auth::user();
        $citizenUserId = $user ? $user->id : null;

        // If not logged in, obtain or register citizen phone
        $phone = trim((string)$request->input('phone', ''));
        if (!$citizenUserId) {
            if (empty($phone)) {
                $phone = '01700000000'; // Default public submission fallback
            }

            $pdo = DatabaseManager::getConnection();
            $hash = Security::phoneLookupHash($phone);
            $stmt = $pdo->prepare("SELECT id FROM users WHERE phone_lookup_hash = ? OR phone = ? LIMIT 1");
            $stmt->execute([$hash, $phone]);
            $existingId = $stmt->fetchColumn();

            if ($existingId) {
                $citizenUserId = (int)$existingId;
            } else {
                $uuid = Security::uuid();
                $ins = $pdo->prepare("
                    INSERT INTO users (uuid, user_type, phone, phone_lookup_hash, status, preferred_language, created_at)
                    VALUES (?, 'citizen', ?, ?, 'active', 'bn', NOW())
                ");
                $ins->execute([$uuid, $phone, $hash]);
                $citizenUserId = (int)$pdo->lastInsertId();
            }
        }

        $categoryId = (int)$request->input('category_id');
        $subcategoryId = (int)$request->input('subcategory_id');
        $wardId = (int)$request->input('ward_id');
        $description = trim((string)$request->input('description', ''));
        $landmark = trim((string)$request->input('landmark', ''));
        $approximateAddress = trim((string)$request->input('approximate_address', ''));

        if ($subcategoryId <= 0 || $wardId <= 0 || empty($description)) {
            $categories = $this->taxonomyService->getCategories(true);
            $zones = $this->cityService->getZones(true);

            return view('complaints/create', [
                'locale' => Translator::getLocale(),
                'categories' => $categories,
                'zones' => $zones,
                'user' => $user,
                'error' => 'সমস্যার ধরন, ওয়ার্ড এবং বিবরণ সঠিকভাবে পূরণ করুন।',
            ]);
        }

        // Auto-resolve category if not passed
        if ($categoryId <= 0) {
            $sub = $this->taxonomyService->getSubcategory($subcategoryId);
            $categoryId = $sub ? (int)$sub['category_id'] : 1;
        }

        try {
            $complaint = $this->complaintService->createComplaint([
                'citizen_user_id' => $citizenUserId,
                'category_id' => $categoryId,
                'subcategory_id' => $subcategoryId,
                'ward_id' => $wardId,
                'description' => $description,
                'landmark' => !empty($landmark) ? $landmark : null,
                'approximate_address' => !empty($approximateAddress) ? $approximateAddress : null,
            ]);

            return view('complaints/success', [
                'locale' => Translator::getLocale(),
                'complaint' => $complaint,
                'trackingNumber' => $complaint['public_complaint_number'],
            ]);
        } catch (\Throwable $e) {
            $categories = $this->taxonomyService->getCategories(true);
            $zones = $this->cityService->getZones(true);

            return view('complaints/create', [
                'locale' => Translator::getLocale(),
                'categories' => $categories,
                'zones' => $zones,
                'user' => $user,
                'error' => 'অভিযোগ দাখিল করার সময় একটি সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।',
            ]);
        }
    }

    /**
     * Show tracking lookup form or result.
     */
    public function track(Request $request, ?string $trackingNumber = null): Response
    {
        $number = $trackingNumber ?: trim((string)$request->input('tracking_number', ''));
        $user = Auth::user();
        $viewingUserId = $user ? $user->id : null;

        $complaint = null;
        $error = null;

        if (!empty($number)) {
            $complaint = $this->complaintService->getComplaintByTrackingNumber($number, $viewingUserId);
            if (!$complaint) {
                $error = 'এই ট্র্যাকিং নম্বরের কোনো অভিযোগ পাওয়া যায়নি। অনুগ্রহ করে সঠিক নম্বর দিন।';
            }
        }

        return view('complaints/track', [
            'locale' => Translator::getLocale(),
            'trackingNumber' => $number,
            'complaint' => $complaint,
            'error' => $error,
            'user' => $user,
            'success' => $request->input('success'),
        ]);
    }

    /**
     * Citizen confirms resolution or requests rework.
     */
    public function confirmResolution(Request $request, string $id): Response
    {
        $complaintId = (int)$id;
        $action = (string)$request->input('action'); // 'confirm' or 'reject'
        $user = Auth::user();
        $citizenUserId = $user ? $user->id : 1;

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("SELECT public_complaint_number, citizen_user_id FROM complaints WHERE id = ? LIMIT 1");
        $stmt->execute([$complaintId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return Response::redirect('/track');
        }

        $trackingNumber = $row['public_complaint_number'];
        $actualCitizenId = (int)$row['citizen_user_id'];

        if ($action === 'confirm') {
            $rating = max(1, min(5, (int)$request->input('rating', 5)));
            $notes = trim((string)$request->input('feedback_notes', ''));
            $this->resolutionService->citizenConfirm($complaintId, $actualCitizenId, true, $rating, !empty($notes) ? $notes : null);
            
            return Response::redirect("/track/{$trackingNumber}?success=" . urlencode('আপনার সন্তুষ্টির মতামত গ্রহণ করা হয়েছে। ধন্যবাদ!'));
        } elseif ($action === 'reject') {
            $reason = trim((string)$request->input('reopen_reason', 'কাজ অসম্পূর্ণ রয়েছে'));
            $notes = trim((string)$request->input('feedback_notes', ''));
            $this->resolutionService->citizenConfirm($complaintId, $actualCitizenId, false, null, !empty($notes) ? $notes : null, $reason);

            return Response::redirect("/track/{$trackingNumber}?success=" . urlencode('আপনার মতামত রেকর্ড করা হয়েছে। আবার কাজের নির্দেশ প্রদান করা হয়েছে।'));
        }

        return Response::redirect("/track/{$trackingNumber}");
    }

    /**
     * List complaints submitted by logged-in citizen.
     */
    public function myComplaints(Request $request): Response
    {
        $user = Auth::user();
        if (!$user) {
            return Response::redirect('/login?tab=citizen&error=auth_required');
        }

        // If authenticated as non-citizen staff/representative, safely redirect to their dashboard
        if ($user->userType !== 'citizen' && !$user->hasRole('citizen')) {
            return Response::redirect('/dashboard');
        }

        $complaints = $this->complaintService->getCitizenComplaints($user->id);

        return view('complaints/my_complaints', [
            'locale' => Translator::getLocale(),
            'user' => $user,
            'complaints' => $complaints,
        ]);
    }
}
