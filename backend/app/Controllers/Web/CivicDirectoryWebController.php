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
     * Show Wards & Zones overview.
     */
    public function wards(Request $request): Response
    {
        $zones = $this->cityService->getZones(true);
        $profile = $this->cityService->getCityProfile();

        return view('wards/index', [
            'locale' => Translator::getLocale(),
            'zones' => $zones,
            'profile' => $profile,
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
}
