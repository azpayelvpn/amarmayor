<?php

declare(strict_types=1);

namespace AmarMayor\Domain\Communication;

use AmarMayor\Database\DatabaseManager;
use InvalidArgumentException;
use PDO;

class CommunicationService
{
    /**
     * Sends a complaint-linked message between citizen and responsible staff.
     */
    public function sendComplaintMessage(
        int $complaintId,
        int $senderUserId,
        string $body,
        string $messageType = 'inquiry'
    ): int {
        $body = trim($body);
        if (empty($body)) {
            throw new InvalidArgumentException("Message body cannot be empty.");
        }

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO complaint_messages (complaint_id, sender_user_id, message_type, body, is_moderated, created_at)
            VALUES (?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$complaintId, $senderUserId, $messageType, $body]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Gets all conversation messages for a complaint.
     */
    public function getComplaintMessages(int $complaintId): array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT cm.*, u.user_type as sender_type
            FROM complaint_messages cm
            INNER JOIN users u ON u.id = cm.sender_user_id
            WHERE cm.complaint_id = ?
            ORDER BY cm.created_at ASC
        ");
        $stmt->execute([$complaintId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adds an internal staff note (strict staff-only visibility).
     */
    public function addInternalNote(
        int $complaintId,
        int $authorUserId,
        string $text,
        string $noteType = 'internal_memo'
    ): int {
        $text = trim($text);
        if (empty($text)) {
            throw new InvalidArgumentException("Note text cannot be empty.");
        }

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$complaintId, $authorUserId, $noteType, $text]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Sends a formal citizen query to the Mayor or CEO Office.
     */
    public function sendOfficeMessage(
        int $citizenUserId,
        string $officeType,
        string $category,
        string $body,
        ?string $subject = null,
        ?int $linkedComplaintId = null
    ): int {
        $body = trim($body);
        if (empty($body)) {
            throw new InvalidArgumentException("Message body cannot be empty.");
        }

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO office_messages (citizen_user_id, office_type, category, linked_complaint_id, subject, body, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'received', NOW())
        ");
        $stmt->execute([$citizenUserId, $officeType, $category, $linkedComplaintId, $subject, $body]);

        return (int)$pdo->lastInsertId();
    }
}
