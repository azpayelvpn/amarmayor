<?php

declare(strict_types=1);

namespace AmarMayor\Controllers\Web;

use AmarMayor\Auth\Otp\DevOtpInboxService;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Config;
use AmarMayor\Support\Translator;
use PDO;

class DevTestingController
{
    /**
     * Local Development Mock OTP Inbox.
     * Strictly disabled in production.
     */
    public function otpInbox(Request $request): Response
    {
        if (Config::get('app.env') === 'production') {
            return Response::html("<h1>404 Not Found</h1>", 404);
        }

        $otps = DevOtpInboxService::getRecentOtps();

        return view('dev/otp_inbox', [
            'locale' => Translator::getLocale(),
            'otps' => $otps,
        ]);
    }

    /**
     * Local Development Testing Access & Demo Accounts Directory.
     * Strictly disabled in production.
     */
    public function testingAccess(Request $request): Response
    {
        if (Config::get('app.env') === 'production') {
            return Response::html("<h1>404 Not Found</h1>", 404);
        }

        $pdo = DatabaseManager::getConnection();

        // Query all demo users seeded in the database
        $stmt = $pdo->query("
            SELECT u.id, u.email, u.phone, u.user_type, r.slug as role_slug, r.name_bn as role_name_bn, r.name_en as role_name_en,
                   p.full_name_bn, p.full_name_en,
                   e.designation_bn, e.designation_en
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            LEFT JOIN persons p ON p.user_id = u.id
            LEFT JOIN employees e ON e.person_id = p.id
            WHERE u.email LIKE '%@demo.local' OR u.phone LIKE '0171100000%'
            ORDER BY r.id ASC, u.id ASC
        ");
        $demoUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return view('dev/testing_access', [
            'locale' => Translator::getLocale(),
            'demoUsers' => $demoUsers,
        ]);
    }
}
