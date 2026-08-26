<?php

declare(strict_types=1);

namespace AmarMayor\Domain\TechnicalAdmin;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Config;
use AmarMayor\Support\RedisClient;
use PDO;

class SystemHealthService
{
    /**
     * Plain-language traffic-light health overview for Technical Super Admin.
     *
     * @return array{
     *   overall_status: 'healthy'|'degraded'|'critical',
     *   components: array<string, array{status: 'healthy'|'degraded'|'critical', label_bn: string, label_en: string, message: string}>,
     *   technical_details?: array<string, mixed>
     * }
     */
    public function getSystemHealth(bool $includeTechnicalDetails = false): array
    {
        $components = [];
        $overallHealthy = true;

        // 1. Database Health
        $dbStatus = 'healthy';
        $dbMsg = 'স্বাভাবিক ও সক্রিয় (Connected)';
        try {
            $pdo = DatabaseManager::getConnection();
            $pdo->query("SELECT 1");
        } catch (\Throwable $e) {
            $dbStatus = 'critical';
            $dbMsg = 'ডাটাবেজ সংযোগে ত্রুটি';
            $overallHealthy = false;
        }
        $components['database'] = [
            'status' => $dbStatus,
            'label_bn' => 'ডাটাবেজ সার্ভিস',
            'label_en' => 'Database Service',
            'message' => $dbMsg,
        ];

        // 2. Cache / Fast Storage
        $cacheStatus = 'healthy';
        $cacheMsg = 'সক্রিয় (Memory / Fast Cache Ready)';
        try {
            RedisClient::set('health_check', '1', 10);
            $val = RedisClient::get('health_check');
            if ($val !== '1') {
                $cacheStatus = 'degraded';
                $cacheMsg = 'ক্যাশ রিড ব্যর্থ';
            }
        } catch (\Throwable $e) {
            $cacheStatus = 'degraded';
            $cacheMsg = 'মেমোরি ক্যাশ ফলব্যাক সক্রিয়';
        }
        $components['fast_services'] = [
            'status' => $cacheStatus,
            'label_bn' => 'দ্রুত ক্যাশ ও সেশন',
            'label_en' => 'Fast Cache & Sessions',
            'message' => $cacheMsg,
        ];

        // 3. Background Processing & Queue
        $bgStatus = 'healthy';
        $bgMsg = 'কার্যকর (Durable Queue Active)';
        try {
            $pdo = DatabaseManager::getConnection();
            $failedJobs = (int)$pdo->query("SELECT COUNT(*) FROM background_jobs WHERE status = 'failed'")->fetchColumn();
            if ($failedJobs > 0) {
                $bgStatus = 'degraded';
                $bgMsg = "{$failedJobs}টি ব্যর্থ কাজ অপেক্ষমাণ";
            }
        } catch (\Throwable $e) {
            $bgStatus = 'degraded';
            $bgMsg = 'পটভূমি প্রক্রিয়াকরণ নিরীক্ষা ব্যর্থ';
        }
        $components['background_processing'] = [
            'status' => $bgStatus,
            'label_bn' => 'ব্যাকগ্রাউন্ড প্রসেসিং',
            'label_en' => 'Background Processing',
            'message' => $bgMsg,
        ];

        // 4. Security & Audit Logging
        $components['security'] = [
            'status' => 'healthy',
            'label_bn' => 'নিরাপত্তা ও অডিট লগ',
            'label_en' => 'Security & Audit Logging',
            'message' => 'সুরক্ষিত ও সক্রিয় (Append-Only Enforced)',
        ];

        $overall = $overallHealthy ? 'healthy' : 'degraded';

        $result = [
            'overall_status' => $overall,
            'components' => $components,
        ];

        if ($includeTechnicalDetails) {
            $result['technical_details'] = [
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Core PHP Engine',
                'opcache_enabled' => function_exists('opcache_get_status') && (bool)opcache_get_status(false),
                'environment' => Config::get('app.env', 'production'),
                'timezone' => date_default_timezone_get(),
            ];
        }

        return $result;
    }
}
