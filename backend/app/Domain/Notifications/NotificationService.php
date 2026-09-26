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
     * Get recent notifications for a user.
     */
    public function getUserNotifications(int $userId, int $limit = 50): array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT " . (int)$limit . "
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get unread notifications count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?");
        return $stmt->execute([$notificationId, $userId]);
    }

    /**
     * Mark all notifications for a user as read.
     */
    public function markAllAsRead(int $userId): bool
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
        return $stmt->execute([$userId]);
    }

    /**
     * Notify all users possessing a specific role slug (e.g. mayor, ceo, control_room_officer).
     */
    public function notifyRole(
        string $roleSlug,
        string $titleBn,
        string $titleEn,
        string $bodyBn,
        string $bodyEn,
        string $notificationType = 'system_alert',
        ?array $dataPayload = null
    ): int {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT DISTINCT ur.user_id 
            FROM user_roles ur
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE r.slug = ?
        ");
        $stmt->execute([$roleSlug]);
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $count = 0;
        foreach ($userIds as $uId) {
            $this->notifyUser((int)$uId, $titleBn, $titleEn, $bodyBn, $bodyEn, $notificationType, $dataPayload);
            $count++;
        }

        return $count;
    }

    /**
     * Notify the specific assigned supervisor for a ward.
     */
    public function notifyWardSupervisor(
        int $wardNumber,
        string $titleBn,
        string $titleEn,
        string $bodyBn,
        string $bodyEn,
        string $notificationType = 'task_assigned',
        ?array $dataPayload = null
    ): int {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.user_id
            FROM employee_responsibilities er
            INNER JOIN employees e ON e.id = er.employee_id
            INNER JOIN persons p ON p.id = e.person_id
            INNER JOIN user_roles ur ON ur.user_id = p.user_id
            INNER JOIN roles r ON r.id = ur.role_id AND r.slug = 'supervisor'
            WHERE er.area_type = 'ward' AND er.area_id = ?
              AND (er.effective_to IS NULL OR er.effective_to >= NOW())
            LIMIT 1
        ");
        $stmt->execute([$wardNumber]);
        $userId = $stmt->fetchColumn();

        if ($userId) {
            return $this->notifyUser((int)$userId, $titleBn, $titleEn, $bodyBn, $bodyEn, $notificationType, $dataPayload);
        }

        return 0;
    }

    /**
     * Notify the head/officers of a department.
     */
    public function notifyDepartmentHead(
        int $deptId,
        string $titleBn,
        string $titleEn,
        string $bodyBn,
        string $bodyEn,
        string $notificationType = 'department_action',
        ?array $dataPayload = null
    ): int {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.user_id
            FROM employee_postings ep
            INNER JOIN employees e ON e.id = ep.employee_id
            INNER JOIN persons p ON p.id = e.person_id
            INNER JOIN user_roles ur ON ur.user_id = p.user_id
            INNER JOIN roles r ON r.id = ur.role_id AND r.slug IN ('department_head', 'department_officer')
            WHERE ep.department_id = ?
              AND (ep.effective_to IS NULL OR ep.effective_to >= NOW())
        ");
        $stmt->execute([$deptId]);
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $count = 0;
        foreach ($userIds as $uId) {
            $this->notifyUser((int)$uId, $titleBn, $titleEn, $bodyBn, $bodyEn, $notificationType, $dataPayload);
            $count++;
        }

        return $count;
    }
}
