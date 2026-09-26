<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Auth\Auth;
use AmarMayor\Auth\AuthService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class AuthController
{
    private AuthService $authService;

    public function __construct(?AuthService $authService = null)
    {
        $this->authService = $authService ?: new AuthService();
    }

    /**
     * Web: Show unified bilingual login interface.
     */
    public function showLogin(Request $request): Response
    {
        if (Auth::check()) {
            return Response::redirect('/dashboard');
        }

        return view('auth/login', [
            'page_title' => __('auth.login'),
            'error' => $request->query('error'),
            'phone' => $request->query('phone'),
            'step' => $request->query('step', 'request'),
            'demo_otp' => $request->query('demo_otp'),
        ]);
    }

    /**
     * Web: Authenticate staff / officer via Password.
     */
    public function loginPassword(Request $request): Response
    {
        $identifier = (string)$request->input('identifier', '');
        $password = (string)$request->input('password', '');

        $user = $this->authService->authenticateWithPassword($identifier, $password);

        if (!$user) {
            return Response::redirect('/login?error=invalid_credentials&tab=password');
        }

        Auth::login($user);

        return Response::redirect('/dashboard');
    }

    /**
     * Web: Request OTP for citizen login.
     */
    public function requestOtp(Request $request): Response
    {
        $phone = (string)$request->input('phone', '');
        $result = $this->authService->requestOtp($phone, $request->getClientIp());

        if (!$result['success']) {
            return Response::redirect('/login?error=' . urlencode($result['message']) . '&tab=otp');
        }

        $demoOtpParam = !empty($result['mock_otp']) ? '&demo_otp=' . urlencode($result['mock_otp']) : '';
        return Response::redirect('/login?step=verify&phone=' . urlencode($phone) . $demoOtpParam . '&tab=otp');
    }

    /**
     * Web: Verify OTP and log in.
     */
    public function verifyOtp(Request $request): Response
    {
        $phone = (string)$request->input('phone', '');
        $otp = (string)$request->input('otp_code', '');

        $user = $this->authService->verifyOtp($phone, $otp);

        if (!$user) {
            return Response::redirect('/login?step=verify&phone=' . urlencode($phone) . '&error=invalid_otp&tab=otp');
        }

        Auth::login($user);

        return Response::redirect('/dashboard');
    }

    /**
     * Web: Logout user session.
     */
    public function logout(Request $request): Response
    {
        Auth::logout();
        return Response::redirect('/login');
    }

    /**
     * API: Request OTP for Citizen mobile app.
     */
    public function apiOtpRequest(Request $request): Response
    {
        $phone = (string)$request->input('phone', '');
        $result = $this->authService->requestOtp($phone, $request->getClientIp());

        if (!$result['success']) {
            return Response::json([
                'success' => false,
                'error' => [
                    'code' => strtoupper($result['message']),
                    'message' => __('auth.otp_invalid'),
                ],
            ], 422);
        }

        $data = ['message' => __('auth.otp_sent')];
        if (isset($result['mock_otp'])) {
            $data['mock_otp'] = $result['mock_otp'];
        }

        return Response::json($data);
    }

    /**
     * API: Verify OTP and return Bearer API token.
     */
    public function apiOtpVerify(Request $request): Response
    {
        $phone = (string)$request->input('phone', '');
        $otp = (string)$request->input('otp_code', '');
        $deviceName = (string)$request->input('device_name', 'Citizen App');
        $deviceId = (string)$request->input('device_id', '');

        $user = $this->authService->verifyOtp($phone, $otp);

        if (!$user) {
            return Response::error('INVALID_OTP', __('auth.otp_invalid'), 401);
        }

        $token = $this->authService->issueApiToken($user, $deviceName, $deviceId ?: null);

        return Response::json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->toArray(),
        ]);
    }

    /**
     * API: Staff / Officer password login returning Bearer token.
     */
    public function apiLogin(Request $request): Response
    {
        $identifier = (string)$request->input('identifier', '');
        $password = (string)$request->input('password', '');
        $deviceName = (string)$request->input('device_name', 'Officer App');

        $user = $this->authService->authenticateWithPassword($identifier, $password);

        if (!$user) {
            return Response::error('INVALID_CREDENTIALS', __('auth.invalid_credentials'), 401);
        }

        $token = $this->authService->issueApiToken($user, $deviceName);

        return Response::json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->toArray(),
        ]);
    }

    /**
     * API: Get currently authenticated user profile.
     */
    public function apiMe(Request $request): Response
    {
        $user = Auth::user();

        return Response::json([
            'user' => $user ? $user->toArray(true) : null,
            'permissions' => $user ? $user->getPermissionSlugs() : [],
            'scopes' => $user ? array_map(fn($s) => [
                'scope_type' => $s->scopeType,
                'scope_id' => $s->scopeId,
            ], $user->getScopes()) : [],
        ]);
    }

    /**
     * API: Revoke active Bearer token.
     */
    public function apiLogout(Request $request): Response
    {
        $authHeader = $request->getHeader('authorization');
        if ($authHeader && str_starts_with(strtolower($authHeader), 'bearer ')) {
            $token = trim(substr($authHeader, 7));
            $this->authService->revokeApiToken($token);
        }

        return Response::json([
            'message' => __('auth.logout_success'),
        ]);
    }
}
