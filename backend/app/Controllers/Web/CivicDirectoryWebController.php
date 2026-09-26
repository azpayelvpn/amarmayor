<?php

declare(strict_types=1);

namespace AmarMayor\Controllers\Web;

use AmarMayor\Domain\City\CityStructureService;
use AmarMayor\Domain\PublicAccountability\PublicAccountabilityService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Translator;

class CivicDirectoryWebController
{
    private PublicAccountabilityService $publicService;
    private CityStructureService $cityService;

    public function __construct(
        ?PublicAccountabilityService $publicService = null,
        ?CityStructureService $cityService = null
    ) {
        $this->publicService = $publicService ?: new PublicAccountabilityService();
        $this->cityService = $cityService ?: new CityStructureService();
    }

    /**
     * Show "Who is Responsible?" civic directory by ward.
     */
    public function whoIsResponsible(Request $request): Response
    {
        $directory = $this->publicService->getWhoIsResponsible();
        $selectedWard = $request->input('ward_id') ? (int)$request->input('ward_id') : null;

        return view('civic/who_is_responsible', [
            'locale' => Translator::getLocale(),
            'directory' => $directory,
            'selectedWard' => $selectedWard,
        ]);
    }

    /**
     * Show Wards & Zones overview (with personalized "My Area" for authenticated citizens).
     */
    public function wards(Request $request): Response
    {
        $zones = $this->cityService->getZones(true);
        $profile = $this->cityService->getCityProfile();
        $user = \AmarMayor\Auth\Auth::user();

        $homeWard = null;
        $homeRepresentation = null;
        $homeSnapshot = null;

        if ($user) {
            $pdo = \AmarMayor\Database\DatabaseManager::getConnection();
            $person = $pdo->query("SELECT * FROM persons WHERE user_id = {$user->id} LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
            if (!empty($person['home_ward_id'])) {
                $homeWardId = (int)$person['home_ward_id'];
                $homeWard = $this->cityService->getWardDetails($homeWardId);
                
                // Get accountability item for this ward
                $directory = $this->publicService->getWhoIsResponsible();
                foreach ($directory as $dirItem) {
                    if ((int)($dirItem['ward_id'] ?? 0) === $homeWardId) {
                        $homeRepresentation = $dirItem;
                        break;
                    }
                }

                // Public-safe ward activity snapshot
                $stmtSnap = $pdo->prepare("
                    SELECT 
                        COUNT(*) as total_in_ward,
                        SUM(CASE WHEN internal_status IN ('in_progress', 'work_completed') THEN 1 ELSE 0 END) as active_in_ward,
                        SUM(CASE WHEN internal_status = 'resolved' THEN 1 ELSE 0 END) as resolved_in_ward
                    FROM complaints
                    WHERE ward_id = ?
                ");
                $stmtSnap->execute([$homeWardId]);
                $homeSnapshot = $stmtSnap->fetch(\PDO::FETCH_ASSOC) ?: [];
            }
        }

        return view('wards/index', [
            'locale' => Translator::getLocale(),
            'zones' => $zones,
            'profile' => $profile,
            'user' => $user,
            'homeWard' => $homeWard,
            'homeRepresentation' => $homeRepresentation,
            'homeSnapshot' => $homeSnapshot,
        ]);
    }

    /**
     * Show Public Notices.
     */
    public function notices(Request $request): Response
    {
        $notices = $this->publicService->getPublicNotices();

        return view('civic/notices', [
            'locale' => Translator::getLocale(),
            'notices' => $notices,
        ]);
    }

    /**
     * Show Citizen Charter & Operational Schedules (Waste pickup, Mosquito spray, SLA).
     */
    public function schedules(Request $request): Response
    {
        return view('civic/schedules', [
            'locale' => Translator::getLocale(),
        ]);
    }
}
