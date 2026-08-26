<?php

declare(strict_types=1);

namespace AmarMayor\Domain\ComplaintCore;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintConfig\DeadlineConfigService;
use AmarMayor\Domain\ComplaintRouting\ComplaintRoutingEngine;
use AmarMayor\Support\Security;
use InvalidArgumentException;
use PDO;

class ComplaintService
{
    private DeadlineConfigService $deadlineService;
    private ?ComplaintRoutingEngine $routingEngine;

    public function __construct(
        ?DeadlineConfigService $deadlineService = null,
        ?ComplaintRoutingEngine $routingEngine = null
    ) {
        $this->deadlineService = $deadlineService ?: new DeadlineConfigService();
        $this->routingEngine = $routingEngine;
    }

    /**
     * Submit a new citizen civic complaint.
     *
     * @param array{
     *   citizen_user_id: int,
     *   created_by_user_id?: ?int,
     *   category_id: int,
     *   subcategory_id: int,
     *   ward_id: int,
     *   description: string,
     *   latitude?: float,
     *   longitude?: float,
     *   approximate_address?: ?string,
     *   landmark?: ?string,
     *   priority?: ?string,
     *   operational_classification?: ?string,
     *   is_sensitive?: bool,
     *   media?: array<array{media_type: string, file_path: string, mime_type: string, file_size: int, is_live?: bool}>
     * } $data
     */
    public function createComplaint(array $data): array
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Resolve Ward & Zone
        $wardStmt = $pdo->prepare("SELECT id, zone_id FROM wards WHERE id = ? AND status = 'active' LIMIT 1");
        $wardStmt->execute([(int)$data['ward_id']]);
        $ward = $wardStmt->fetch(PDO::FETCH_ASSOC);

        if (!$ward) {
            throw new InvalidArgumentException("Invalid or inactive ward ID: {$data['ward_id']}");
        }
        $zoneId = (int)$ward['zone_id'];

        // 2. Resolve Subcategory Defaults (Priority & Classification)
        $subStmt = $pdo->prepare("SELECT default_priority, default_classification FROM complaint_subcategories WHERE id = ? LIMIT 1");
        $subStmt->execute([(int)$data['subcategory_id']]);
        $sub = $subStmt->fetch(PDO::FETCH_ASSOC);

        $priority = $data['priority'] ?? ($sub['default_priority'] ?? 'p3_normal');
        $classification = $data['operational_classification'] ?? ($sub['default_classification'] ?? 'quick_action');

        // 3. Calculate SLA Deadline (if configured)
        $now = now_dhaka();
        $deadlineAt = $this->deadlineService->calculateDeadline(
            (int)$data['category_id'],
            (int)$data['subcategory_id'],
            $priority,
            $classification,
            $now
        );
        $deadlineStr = $deadlineAt ? $deadlineAt->format('Y-m-d H:i:s') : null;

        // 4. Generate Unique Public Complaint Number: MCC-YYMM-XXXXX
        $prefix = 'MCC-' . date('ym') . '-';
        $randomSeq = sprintf('%05d', random_int(10000, 99999));
        $complaintNumber = $prefix . $randomSeq;

        $pdo->beginTransaction();
        try {
            // 5. Insert Complaint
            $stmt = $pdo->prepare("
                INSERT INTO complaints (
                    public_complaint_number, citizen_user_id, created_by_user_id,
                    category_id, subcategory_id, ward_id, zone_id,
                    priority, operational_classification, internal_status, citizen_status,
                    description, is_sensitive, submitted_at, deadline_at, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', 'received', ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $complaintNumber,
                $data['citizen_user_id'],
                $data['created_by_user_id'] ?? null,
                $data['category_id'],
                $data['subcategory_id'],
                $data['ward_id'],
                $zoneId,
                $priority,
                $classification,
                $data['description'],
                !empty($data['is_sensitive']) ? 1 : 0,
                $now->format('Y-m-d H:i:s'),
                $deadlineStr,
            ]);
            $complaintId = (int)$pdo->lastInsertId();

            // 6. Insert Location (Exact Internal Coordinates + Privacy-Safe Public Representation)
            $lat = (float)($data['latitude'] ?? 24.7471); // Default Mymensingh center
            $lng = (float)($data['longitude'] ?? 90.4203);
            $approxAddr = $data['approximate_address'] ?? null;
            $landmark = $data['landmark'] ?? null;
            // Public safe representation strictly avoids household numbers or private building identifiers
            $publicSafeAddr = $landmark ?: "ওয়ার্ড নং {$data['ward_id']}, ময়মনসিংহ";
            $publicLat = null; // Private household coordinates not exposed to public
            $publicLng = null;

            $locStmt = $pdo->prepare("
                INSERT INTO complaint_locations (
                    complaint_id, latitude, longitude, approximate_address, landmark,
                    public_safe_address, public_latitude, public_longitude
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $locStmt->execute([
                $complaintId, $lat, $lng, $approxAddr, $landmark, $publicSafeAddr, $publicLat, $publicLng
            ]);

            // 7. Insert Media / Evidence (if provided)
            if (!empty($data['media'])) {
                $mStmt = $pdo->prepare("
                    INSERT INTO complaint_media (
                        complaint_id, uploader_user_id, media_type, original_file_path,
                        mime_type, file_size_bytes, is_live_capture, moderation_status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'approved', NOW())
                ");
                foreach ($data['media'] as $media) {
                    $mStmt->execute([
                        $complaintId,
                        $data['citizen_user_id'],
                        $media['media_type'] ?? 'image',
                        $media['file_path'],
                        $media['mime_type'] ?? 'image/jpeg',
                        $media['file_size'] ?? 102400,
                        !empty($media['is_live']) ? 1 : 0,
                    ]);
                }
            }

            // 8. Record Initial Append-Only Status History
            $histStmt = $pdo->prepare("
                INSERT INTO complaint_status_history (
                    complaint_id, from_internal_status, to_internal_status,
                    from_citizen_status, to_citizen_status, action_name, actor_user_id, created_at
                ) VALUES (?, NULL, 'submitted', NULL, 'received', 'submitted', ?, NOW())
            ");
            $histStmt->execute([$complaintId, $data['citizen_user_id']]);

            $pdo->commit();

            // 9. Dispatch Automatic Deterministic Routing Engine (Phase 8)
            $routing = $this->routingEngine ?: new ComplaintRoutingEngine();
            $routing->routeComplaint($complaintId);

            return $this->getComplaintById($complaintId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Get complete complaint record by ID for authorized operational views.
     */
    public function getComplaintById(int $id): ?array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   cc.name_bn as category_name_bn, cc.name_en as category_name_en,
                   cs.name_bn as subcategory_name_bn, cs.name_en as subcategory_name_en,
                   w.ward_number, w.name_bn as ward_name_bn,
                   z.zone_number, z.name_bn as zone_name_bn,
                   d.name_bn as dept_name_bn, d.name_en as dept_name_en,
                   su.name_bn as unit_name_bn, su.name_en as unit_name_en,
                   loc.latitude, loc.longitude, loc.approximate_address, loc.landmark, loc.public_safe_address
            FROM complaints c
            INNER JOIN complaint_categories cc ON cc.id = c.category_id
            INNER JOIN complaint_subcategories cs ON cs.id = c.subcategory_id
            INNER JOIN wards w ON w.id = c.ward_id
            INNER JOIN zones z ON z.id = c.zone_id
            LEFT JOIN departments d ON d.id = c.department_id
            LEFT JOIN service_units su ON su.id = c.service_unit_id
            LEFT JOIN complaint_locations loc ON loc.complaint_id = c.id
            WHERE c.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        // Attach media
        $mStmt = $pdo->prepare("SELECT * FROM complaint_media WHERE complaint_id = ? ORDER BY id ASC");
        $mStmt->execute([$id]);
        $row['media'] = $mStmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach status timeline
        $hStmt = $pdo->prepare("
            SELECT csh.*, u.user_type as actor_type 
            FROM complaint_status_history csh
            LEFT JOIN users u ON u.id = csh.actor_user_id
            WHERE csh.complaint_id = ? 
            ORDER BY csh.id ASC
        ");
        $hStmt->execute([$id]);
        $row['status_history'] = $hStmt->fetchAll(PDO::FETCH_ASSOC);

        return $row;
    }

    /**
     * Get privacy-safe public tracking representation by public complaint tracking number.
     */
    public function getComplaintByTrackingNumber(string $trackingNumber, ?int $viewingUserId = null): ?array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.id, c.public_complaint_number, c.citizen_user_id, c.citizen_status, c.internal_status,
                   c.priority, c.operational_classification, c.submitted_at, c.deadline_at,
                   c.completion_attempts, c.reopen_count, c.verified_at, c.citizen_confirmed_at, c.closed_at,
                   cc.name_bn as category_name_bn, cc.name_en as category_name_en,
                   cs.name_bn as subcategory_name_bn, cs.name_en as subcategory_name_en,
                   w.ward_number, w.name_bn as ward_name_bn,
                   z.zone_number, z.name_bn as zone_name_bn,
                   loc.public_safe_address, loc.public_latitude, loc.public_longitude
            FROM complaints c
            INNER JOIN complaint_categories cc ON cc.id = c.category_id
            INNER JOIN complaint_subcategories cs ON cs.id = c.subcategory_id
            INNER JOIN wards w ON w.id = c.ward_id
            INNER JOIN zones z ON z.id = c.zone_id
            LEFT JOIN complaint_locations loc ON loc.complaint_id = c.id
            WHERE c.public_complaint_number = ?
            LIMIT 1
        ");
        $stmt->execute([trim($trackingNumber)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $isOwner = ($viewingUserId !== null && (int)$row['citizen_user_id'] === $viewingUserId);

        // Status history summary
        $hStmt = $pdo->prepare("
            SELECT to_citizen_status as status, action_name, created_at 
            FROM complaint_status_history 
            WHERE complaint_id = ? 
            ORDER BY id ASC
        ");
        $hStmt->execute([(int)$row['id']]);
        $row['timeline'] = $hStmt->fetchAll(PDO::FETCH_ASSOC);

        // If viewing as citizen owner, include full description and uploaded media
        if ($isOwner) {
            $desc = $pdo->query("SELECT description FROM complaints WHERE id = {$row['id']}")->fetchColumn();
            $row['description'] = $desc;

            $mStmt = $pdo->prepare("SELECT id, media_type, original_file_path, created_at FROM complaint_media WHERE complaint_id = ?");
            $mStmt->execute([(int)$row['id']]);
            $row['media'] = $mStmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            unset($row['citizen_user_id']);
        }

        return $row;
    }

    /**
     * Get all complaints submitted by a citizen user.
     */
    public function getCitizenComplaints(int $citizenUserId): array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.id, c.public_complaint_number, c.citizen_status, c.internal_status,
                   c.submitted_at, c.deadline_at, c.reopen_count,
                   cc.name_bn as category_name_bn, cc.name_en as category_name_en,
                   cs.name_bn as subcategory_name_bn, cs.name_en as subcategory_name_en,
                   w.ward_number, loc.public_safe_address
            FROM complaints c
            INNER JOIN complaint_categories cc ON cc.id = c.category_id
            INNER JOIN complaint_subcategories cs ON cs.id = c.subcategory_id
            INNER JOIN wards w ON w.id = c.ward_id
            LEFT JOIN complaint_locations loc ON loc.complaint_id = c.id
            WHERE c.citizen_user_id = ?
            ORDER BY c.submitted_at DESC
        ");
        $stmt->execute([$citizenUserId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
