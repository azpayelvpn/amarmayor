<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Domain\City\CityStructureService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class CityController
{
    private CityStructureService $cityService;

    public function __construct(?CityStructureService $cityService = null)
    {
        $this->cityService = $cityService ?: new CityStructureService();
    }

    public function getProfile(Request $request): Response
    {
        $profile = $this->cityService->getCityProfile();
        return Response::json($profile);
    }

    public function getZones(Request $request): Response
    {
        $includeWards = $request->query('include_wards', 'true') !== 'false';
        $zones = $this->cityService->getZones($includeWards);
        return Response::json($zones);
    }

    public function getWard(Request $request): Response
    {
        $wardId = (int)$request->getAttribute('id', 0);
        $ward = $this->cityService->getWardDetails($wardId);

        if (!$ward) {
            return Response::error('NOT_FOUND', 'Ward not found', 404);
        }

        return Response::json($ward);
    }

    public function getReservedSeats(Request $request): Response
    {
        $seats = $this->cityService->getReservedSeats();
        return Response::json($seats);
    }
}
