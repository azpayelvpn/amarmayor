<?php

declare(strict_types=1);

namespace AmarMayor\Domain\ComplaintRouting;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintConfig\RoutingConfigService;
use PDO;

class ComplaintRoutingEngine
{
    private RoutingConfigService $routingConfigService;

    public function __construct(?RoutingConfigService $routingConfigService = null)
    {
        $this->routingConfigService = $routingConfigService ?: new RoutingConfigService();
    }

    /**
     * Executes automatic deterministic routing on a complaint.
     *
     * @param int $complaintId
     * @param int|null $actorUserId Optional user initiating re-route (null for system auto-route)
     * @return bool True if rule was matched and routed, false if marked for manual review
     */
    public function routeComplaint(int $complaintId, ?int $actorUserId = null): bool
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Fetch current complaint details
        $stmt = $pdo->prepare("
            SELECT id, category_id, subcategory_id, ward_id, department_id, current_supervisor_employee_id, internal_status, citizen_status
            FROM complaints
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$complaintId]);
        $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$complaint) {
            return false;
        }

        // 2. Resolve deterministic rule via 4-tier cascade
        $rule = $this->routingConfigService->resolveRoutingRule(
            (int)$complaint['category_id'],
            (int)$complaint['subcategory_id'],
            (int)$complaint['ward_id']
        );

        $pdo->beginTransaction();
        try {
            if ($rule) {
                $deptId = (int)$rule['department_id'];
                $unitId = $rule['service_unit_id'] ? (int)$rule['service_unit_id'] : null;
                $supervisorId = $rule['assigned_supervisor_employee_id'] ? (int)$rule['assigned_supervisor_employee_id'] : null;
                $teamId = $rule['assigned_team_id'] ? (int)$rule['assigned_team_id'] : null;

                $newInternalStatus = ($supervisorId || $teamId) ? 'assigned' : 'routed';
                $newCitizenStatus = ($supervisorId || $teamId) ? 'assigned' : 'received';

                // Update complaint responsibility
                $upStmt = $pdo->prepare("
                    UPDATE complaints
                    SET department_id = ?,
                        service_unit_id = ?,
                        current_supervisor_employee_id = ?,
                        current_team_id = ?,
                        internal_status = ?,
                        citizen_status = ?
                    WHERE id = ?
                ");
                $upStmt->execute([$deptId, $unitId, $supervisorId, $teamId, $newInternalStatus, $newCitizenStatus, $complaintId]);

                // Append status history
                $histStmt = $pdo->prepare("
                    INSERT INTO complaint_status_history (
                        complaint_id, from_internal_status, to_internal_status,
                        from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at
                    ) VALUES (?, ?, ?, ?, ?, 'auto_routed', ?, 'Deterministic rule applied', NOW())
                ");
                $histStmt->execute([
                    $complaintId,
                    $complaint['internal_status'],
                    $newInternalStatus,
                    $complaint['citizen_status'],
                    $newCitizenStatus,
                    $actorUserId,
                ]);

                // If supervisor is assigned, record ownership history
                if ($supervisorId) {
                    $validActor = $actorUserId;
                    if (!$validActor || !(bool)$pdo->query("SELECT 1 FROM users WHERE id = {$validActor}")->fetchColumn()) {
                        $validActor = (int)$pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();
                    }

                    $ownStmt = $pdo->prepare("
                        INSERT INTO complaint_ownership_history (
                            complaint_id, from_supervisor_employee_id, to_supervisor_employee_id,
                            from_department_id, to_department_id, transfer_reason, transferred_by_user_id, created_at
                        ) VALUES (?, ?, ?, ?, ?, 'Initial automatic routing', ?, NOW())
                    ");
                    $ownStmt->execute([
                        $complaintId,
                        $complaint['current_supervisor_employee_id'] ?: $supervisorId,
                        $supervisorId,
                        $complaint['department_id'] ?: $deptId,
                        $deptId,
                        $validActor,
                    ]);
                }

                $pdo->commit();
                return true;
            }

            // No rule resolved: Mark as review_required for control room triage
            $upStmt = $pdo->prepare("
                UPDATE complaints
                SET internal_status = 'review_required',
                    citizen_status = 'received'
                WHERE id = ?
            ");
            $upStmt->execute([$complaintId]);

            $histStmt = $pdo->prepare("
                INSERT INTO complaint_status_history (
                    complaint_id, from_internal_status, to_internal_status,
                    from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at
                ) VALUES (?, ?, 'review_required', ?, 'received', 'unrouted_review_required', ?, 'No deterministic routing rule configured', NOW())
            ");
            $histStmt->execute([
                $complaintId,
                $complaint['internal_status'],
                $complaint['citizen_status'],
                $actorUserId,
            ]);

            $pdo->commit();
            return false;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
