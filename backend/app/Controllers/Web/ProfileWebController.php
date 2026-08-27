<?php

declare(strict_types=1);

namespace AmarMayor\Controllers\Web;

use AmarMayor\Auth\Auth;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\City\CityStructureService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Security;
use AmarMayor\Support\Translator;
use PDO;

class ProfileWebController
{
    private CityStructureService $cityService;

    public function __construct(?CityStructureService $cityService = null)
    {
        $this->cityService = $cityService ?: new CityStructureService();
    }

    /**
     * Show authenticated citizen or staff profile page.
     */
    public function show(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $user = Auth::user();
        $pdo = DatabaseManager::getConnection();
        $locale = Translator::getLocale();

        // 1. Fetch Person record
        $pStmt = $pdo->prepare("SELECT * FROM persons WHERE user_id = ? LIMIT 1");
        $pStmt->execute([$user->id]);
        $person = $pStmt->fetch(PDO::FETCH_ASSOC);

        if (!$person) {
            // Auto-create blank person profile for user if missing
            $insP = $pdo->prepare("
                INSERT INTO persons (user_id, full_name_bn, full_name_en, is_public_visible, created_at)
                VALUES (?, '', '', 1, NOW())
            ");
            $insP->execute([$user->id]);
            $personId = (int)$pdo->lastInsertId();
            $person = [
                'id' => $personId,
                'user_id' => $user->id,
                'full_name_bn' => '',
                'full_name_en' => '',
                'home_ward_id' => null,
                'home_area' => '',
                'notification_prefs' => '{"sms":true,"app":true}',
            ];
        }

        // 2. Fetch Employee record (if staff)
        $employee = null;
        if (!empty($person['id'])) {
            $eStmt = $pdo->prepare("
                SELECT e.*, d.name_bn as dept_name_bn, d.name_en as dept_name_en,
                       su.name_bn as unit_name_bn, su.name_en as unit_name_en
                FROM employees e
                LEFT JOIN employee_postings ep ON ep.employee_id = e.id AND (ep.effective_to IS NULL OR ep.effective_to >= NOW())
                LEFT JOIN departments d ON d.id = ep.department_id
                LEFT JOIN service_units su ON su.id = ep.service_unit_id
                WHERE e.person_id = ?
                LIMIT 1
            ");
            $eStmt->execute([(int)$person['id']]);
            $employee = $eStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        // 3. Fetch Wards for Dropdown
        $wards = $pdo->query("SELECT id, ward_number, name_bn, name_en FROM wards WHERE status = 'active' ORDER BY ward_number ASC")->fetchAll(PDO::FETCH_ASSOC);

        // 4. Parse notification prefs
        $notifPrefs = [];
        if (!empty($person['notification_prefs'])) {
            $decoded = json_decode($person['notification_prefs'], true);
            if (is_array($decoded)) {
                $notifPrefs = $decoded;
            }
        }

        return view('profile/index', [
            'locale' => $locale,
            'user' => $user,
            'person' => $person,
            'employee' => $employee,
            'wards' => $wards,
            'notifPrefs' => $notifPrefs,
            'success' => $request->query('success'),
            'error' => $request->query('error'),
        ]);
    }

    /**
     * Update authenticated profile details.
     */
    public function update(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        $user = Auth::user();
        $pdo = DatabaseManager::getConnection();

        $nameBn = trim((string)$request->input('name_bn', ''));
        $nameEn = trim((string)$request->input('name_en', ''));
        $email = trim((string)$request->input('email', ''));
        $homeWardId = (int)$request->input('home_ward_id', 0);
        $homeArea = trim((string)$request->input('home_area', ''));
        $prefLang = in_array($request->input('preferred_language'), ['bn', 'en'], true) ? (string)$request->input('preferred_language') : 'bn';

        $smsAlerts = $request->input('notif_sms') === '1';
        $appAlerts = $request->input('notif_app') === '1';
        $notifJson = json_encode(['sms' => $smsAlerts, 'app' => $appAlerts]);

        // Email uniqueness check if provided
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $eCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
            $eCheck->execute([strtolower($email), $user->id]);
            if ($eCheck->fetchColumn()) {
                return Response::redirect('/profile?error=email_taken');
            }
        } else {
            $email = null;
        }

        // Validate Home Ward
        if ($homeWardId > 0) {
            $wCheck = (bool)$pdo->query("SELECT 1 FROM wards WHERE id = {$homeWardId} AND status = 'active'")->fetchColumn();
            if (!$wCheck) {
                $homeWardId = null;
            }
        } else {
            $homeWardId = null;
        }

        $pdo->beginTransaction();
        try {
            // Update users table
            $uStmt = $pdo->prepare("UPDATE users SET email = ?, preferred_language = ?, updated_at = NOW() WHERE id = ?");
            $uStmt->execute([$email, $prefLang, $user->id]);

            // Update or insert persons table
            $pExists = (bool)$pdo->query("SELECT 1 FROM persons WHERE user_id = {$user->id}")->fetchColumn();
            if ($pExists) {
                $pStmt = $pdo->prepare("
                    UPDATE persons SET 
                        full_name_bn = ?, full_name_en = ?,
                        home_ward_id = ?, home_area = ?, notification_prefs = ?,
                        updated_at = NOW()
                    WHERE user_id = ?
                ");
                $pStmt->execute([$nameBn, $nameEn, $homeWardId, $homeArea, $notifJson, $user->id]);
            } else {
                $pIns = $pdo->prepare("
                    INSERT INTO persons (user_id, full_name_bn, full_name_en, home_ward_id, home_area, notification_prefs, is_public_visible, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
                ");
                $pIns->execute([$user->id, $nameBn, $nameEn, $homeWardId, $homeArea, $notifJson]);
            }

            // Update session locale
            $_SESSION['locale'] = $prefLang;
            Translator::setLocale($prefLang);

            $pdo->commit();

            return Response::redirect('/profile?success=profile_updated');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return Response::redirect('/profile?error=' . urlencode($e->getMessage()));
        }
    }
}
