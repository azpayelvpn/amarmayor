<?php

declare(strict_types=1);

namespace AmarMayor\Domain\Notifications;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Logger;
use PDO;

class NotificationService
{
    /**
     * Queues and logs a notification for a user.
     */
    public function notifyUser(
        int $userId,
        string $titleBn,
        string $titleEn,
        string $bodyBn,
        string $bodyEn,
        string $notificationType = 'complaint_status_update',
        ?array $dataPayload = null
    ): int {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO notifications (
                user_id, notification_type, title_bn, title_en, body_bn, body_en, data_payload, is_read, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([
            $userId,
            $notificationType,
            $titleBn,
            $titleEn,
            $bodyBn,
            $bodyEn,
            $dataPayload ? json_encode($dataPayload) : null,
        ]);
        $notificationId = (int)$pdo->lastInsertId();

        Logger::info("Notification Queued", [
            'notification_id' => $notificationId,
            'user_id' => $userId,
            'type' => $notificationType,
        ]);

        return $notificationId;
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUserNotifications(int $userId): array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
