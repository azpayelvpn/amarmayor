<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\Governance\GovernanceService;
use AmarMayor\Tests\TestCase;
use PDO;

class GovernanceServiceTest extends TestCase
{
    private GovernanceService $governanceService;

    public function setUp(): void
    {
        parent::setUp();
        $this->governanceService = new GovernanceService();
    }

    public function testAssignMultiWardResponsibleOfficerAndHistory(): void
    {
        $pdo = DatabaseManager::getConnection();
        $ward1Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();
        $ward2Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 2")->fetchColumn();

        $assignmentId = 0;
        try {
            // Assign Responsible Officer covering Ward 1 AND Ward 2
            $assignmentId = $this->governanceService->assignRepresentative([
                'full_name_bn' => 'পরীক্ষামূলক দায়িত্বপ্রাপ্ত কর্মকর্তা',
                'full_name_en' => 'Test Responsible Officer',
                'phone' => '01700000099',
                'representation_type_slug' => 'responsible_officer',
                'authority_basis' => 'appointed',
                'effective_from' => '2026-01-01 00:00:00',
                'order_number' => 'MCC-GOV-ORDER-2026-01',
                'ward_ids' => [$ward1Id, $ward2Id],
            ]);

            $this->assert($assignmentId > 0);

            // Verify active representatives in Ward 1
            $ward1Reps = $this->governanceService->getActiveRepresentatives('ward', $ward1Id);
            $foundW1 = false;
            foreach ($ward1Reps as $rep) {
                if ($rep['assignment_id'] === $assignmentId) {
                    $foundW1 = true;
                    $this->assertEquals('responsible_officer', $rep['role_slug']);
                    $this->assertEquals('appointed', $rep['authority_basis']);
                }
            }
            $this->assertTrue($foundW1, "Officer must be active representative for Ward 1");

            // Verify active representatives in Ward 2
            $ward2Reps = $this->governanceService->getActiveRepresentatives('ward', $ward2Id);
            $foundW2 = false;
            foreach ($ward2Reps as $rep) {
                if ($rep['assignment_id'] === $assignmentId) {
                    $foundW2 = true;
                }
            }
            $this->assertTrue($foundW2, "Officer must be active representative for Ward 2");

            // End Tenure
            $this->governanceService->endRepresentativeTenure($assignmentId, '2026-08-26 23:59:59', 'MCC-END-01');

            // History should contain this tenure
            $history = $this->governanceService->getRepresentativeHistory($ward1Id);
            $foundHistory = false;
            foreach ($history as $h) {
                if ($h['assignment_id'] === $assignmentId) {
                    $foundHistory = true;
                    $this->assertEquals('2026-08-26 23:59:59', $h['effective_to']);
                }
            }
            $this->assertTrue($foundHistory, "Ward history must preserve past tenure");
        } finally {
            if ($assignmentId) {
                $pdo->exec("DELETE FROM representation_areas WHERE representation_assignment_id = {$assignmentId}");
                $pdo->exec("DELETE FROM representation_assignments WHERE id = {$assignmentId}");
            }
        }
    }
}
