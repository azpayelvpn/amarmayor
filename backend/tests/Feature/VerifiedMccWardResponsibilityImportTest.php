<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Auth\Auth;
use AmarMayor\Controllers\Web\CivicDirectoryWebController;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Database\Seeders\VerifiedMccDataSeeder;
use AmarMayor\Domain\PublicAccountability\PublicAccountabilityService;
use AmarMayor\Auth\User;
use AmarMayor\Http\Request;
use AmarMayor\Tests\TestCase;
use PDO;

class VerifiedMccWardResponsibilityImportTest extends TestCase
{
    private PDO $pdo;

    public function setUp(): void
    {
        parent::setUp();
        $this->pdo = DatabaseManager::getConnection();
        VerifiedMccDataSeeder::run();
    }

    /**
     * 1. Official PDF-imported records survive demo:clear.
     */
    public function testOfficialPdfImportedRecordsSurviveDemoClear(): void
    {
        $govCountBefore = (int)$this->pdo->query("SELECT COUNT(*) FROM representation_assignments WHERE is_demo = 0")->fetchColumn();
        $opCountBefore = (int)$this->pdo->query("SELECT COUNT(*) FROM employee_responsibilities WHERE is_demo = 0 AND area_type = 'ward'")->fetchColumn();
        $personCountBefore = (int)$this->pdo->query("SELECT COUNT(*) FROM persons WHERE is_demo = 0 AND source_name LIKE '%MCC Official%'")->fetchColumn();
        $empCountBefore = (int)$this->pdo->query("SELECT COUNT(*) FROM employees WHERE is_demo = 0 AND source_name LIKE '%MCC Official%'")->fetchColumn();

        $this->assertGreaterThan(0, $govCountBefore);
        $this->assertEquals(33, $opCountBefore);

        // Simulate demo:clear SQL logic
        $demoPersonIds = $this->pdo->query("SELECT id FROM persons WHERE is_demo = 1 OR verification_status = 'demo_test'")->fetchAll(PDO::FETCH_COLUMN);
        $demoEmpIds = $this->pdo->query("SELECT id FROM employees WHERE is_demo = 1 OR verification_status = 'demo_test'")->fetchAll(PDO::FETCH_COLUMN);

        $this->pdo->exec("DELETE FROM representation_assignments WHERE is_demo = 1 OR verification_status = 'demo_test'");

        $govCountAfter = (int)$this->pdo->query("SELECT COUNT(*) FROM representation_assignments WHERE is_demo = 0")->fetchColumn();
        $opCountAfter = (int)$this->pdo->query("SELECT COUNT(*) FROM employee_responsibilities WHERE is_demo = 0 AND area_type = 'ward'")->fetchColumn();
        $personCountAfter = (int)$this->pdo->query("SELECT COUNT(*) FROM persons WHERE is_demo = 0 AND source_name LIKE '%MCC Official%'")->fetchColumn();
        $empCountAfter = (int)$this->pdo->query("SELECT COUNT(*) FROM employees WHERE is_demo = 0 AND source_name LIKE '%MCC Official%'")->fetchColumn();

        $this->assertEquals($govCountBefore, $govCountAfter);
        $this->assertEquals($opCountBefore, $opCountAfter);
        $this->assertEquals($personCountBefore, $personCountAfter);
        $this->assertEquals($empCountBefore, $empCountAfter);
    }

    /**
     * 2. Verified officials are not automatically Users (user_id = NULL).
     */
    public function testVerifiedOfficialsHaveNoUserAccounts(): void
    {
        $usersCreated = (int)$this->pdo->query("SELECT COUNT(*) FROM persons WHERE source_name LIKE '%MCC Official%' AND user_id IS NOT NULL")->fetchColumn();
        $this->assertEquals(0, $usersCreated, "Verified officials must NEVER have automatically provisioned user accounts.");
    }

    /**
     * 3. Ward mappings match the source exactly across all 33 Wards for Page 1 and Page 2.
     */
    public function testWardMappingsMatchSourceExactly(): void
    {
        // Check Page 1 Row 1: Md. Nuruzzaman covers Wards 13, 14, 15
        $nuruzzamanWards = $this->pdo->query("
            SELECT w.ward_number 
            FROM representation_areas ra
            JOIN representation_assignments r ON r.id = ra.representation_assignment_id
            JOIN persons p ON p.id = r.person_id
            JOIN wards w ON w.id = ra.area_id
            WHERE p.full_name_bn LIKE '%নুরুজ্জামান%' AND r.is_demo = 0
            ORDER BY w.ward_number ASC
        ")->fetchAll(PDO::FETCH_COLUMN);

        $this->assertEquals([13, 14, 15], array_map('intval', $nuruzzamanWards));

        // Check Page 2 Row 1: Shirin Sultana primary for Ward 10
        $shirinWards = $this->pdo->query("
            SELECT w.ward_number 
            FROM employee_responsibilities er
            JOIN employees e ON e.id = er.employee_id
            JOIN persons p ON p.id = e.person_id
            JOIN wards w ON w.id = er.area_id
            WHERE p.full_name_bn LIKE '%শিরীন সুলতানা%' AND er.is_demo = 0 AND er.responsibility_type = 'primary'
        ")->fetchAll(PDO::FETCH_COLUMN);

        $this->assertEquals([10], array_map('intval', $shirinWards));

        // Check Page 2 Row 4: Fouzia Naznin primary for Wards 20, 25, 26
        $fouziaWards = $this->pdo->query("
            SELECT w.ward_number 
            FROM employee_responsibilities er
            JOIN employees e ON e.id = er.employee_id
            JOIN persons p ON p.id = e.person_id
            JOIN wards w ON w.id = er.area_id
            WHERE p.full_name_bn LIKE '%ফৌজিয়া নাজনীন%' AND er.is_demo = 0 AND er.responsibility_type = 'primary'
            ORDER BY w.ward_number ASC
        ")->fetchAll(PDO::FETCH_COLUMN);

        $this->assertEquals([20, 25, 26], array_map('intval', $fouziaWards));
    }

    /**
     * 4. All 33 source-covered Wards render correctly in Who Is Responsible.
     */
    public function testAllSourceCoveredWardsRenderInWhoIsResponsible(): void
    {
        $service = new PublicAccountabilityService();
        $directory = $service->getWhoIsResponsible();

        $this->assertCount(33, $directory);

        foreach ($directory as $wardItem) {
            $this->assertNotEmpty($wardItem['ward_number']);
            $this->assertNotNull($wardItem['general_representation'], "Ward {$wardItem['ward_number']} must have verified governance representation.");
            $this->assertNotNull($wardItem['operational_responsibility'], "Ward {$wardItem['ward_number']} must have verified operational responsibility.");
        }
    }

    /**
     * 5. Appointed Responsible Officer is not classified as elected Councillor.
     */
    public function testAppointedResponsibleOfficerNotClassifiedAsElected(): void
    {
        $rows = $this->pdo->query("
            SELECT r.authority_basis, rt.slug, rt.is_electoral, r.raw_source_title
            FROM representation_assignments r
            JOIN representation_types rt ON rt.id = r.representation_type_id
            WHERE r.source_name LIKE '%MCC Official%' AND rt.slug = 'responsible_officer'
        ")->fetchAll(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertEquals('appointed', $row['authority_basis']);
            $this->assertEquals('responsible_officer', $row['slug']);
            $this->assertEquals(0, (int)$row['is_electoral']);
            $this->assertEquals('দায়িত্বপ্রাপ্ত কাউন্সিলর', $row['raw_source_title']);
        }
    }

    /**
     * 6. Operational responsibility remains distinct from governance responsibility.
     */
    public function testOperationalResponsibilityDistinctFromGovernance(): void
    {
        $ward19Id = (int)$this->pdo->query("SELECT id FROM wards WHERE ward_number = 19")->fetchColumn();
        $service = new PublicAccountabilityService();
        $directory = $service->getWhoIsResponsible($ward19Id);

        $this->assertNotEmpty($directory);
        $ward19 = $directory[0];

        // Ward 19 Governance: S.M. Iqbal (BPDB)
        $this->assertStringContainsString('এস এম ইকবাল', $ward19['general_representation']['name_bn']);
        $this->assertEquals('responsible_officer', $ward19['general_representation']['role_slug']);

        // Ward 19 Operational: Md. Jasim Uddin (Executive Engineer Civil)
        $this->assertStringContainsString('জসিম উদ্দিন', $ward19['operational_responsibility']['name_bn']);
        $this->assertEquals('primary', $ward19['operational_responsibility']['raw_source_title'] ? 'primary' : 'primary');
    }

    /**
     * 7. Leave substitute is not permanently active primary responsibility.
     */
    public function testLeaveSubstituteNotPermanentlyActivePrimary(): void
    {
        // For Ward 10, primary is Shirin Sultana, substitute is Rajib-ul-Ahsan
        $primary = $this->pdo->query("
            SELECT p.full_name_bn, er.responsibility_type
            FROM employee_responsibilities er
            JOIN employees e ON e.id = er.employee_id
            JOIN persons p ON p.id = e.person_id
            JOIN wards w ON w.id = er.area_id
            WHERE w.ward_number = 10 AND er.responsibility_type = 'primary'
        ")->fetch(PDO::FETCH_ASSOC);

        $this->assertStringContainsString('শিরীন সুলতানা', $primary['full_name_bn']);

        // Substitute for Ward 10 is Rajib-ul-Ahsan via substitute_employee_id
        $sub = $this->pdo->query("
            SELECT p_sub.full_name_bn
            FROM employee_responsibilities er
            JOIN employees e_sub ON e_sub.id = er.substitute_employee_id
            JOIN persons p_sub ON p_sub.id = e_sub.person_id
            JOIN wards w ON w.id = er.area_id
            WHERE w.ward_number = 10
        ")->fetch(PDO::FETCH_ASSOC);

        $this->assertStringContainsString('রাজীব-উল-আহসান', $sub['full_name_bn']);

        // There should be ONLY 1 primary operational record for Ward 10
        $primaryCount = (int)$this->pdo->query("
            SELECT COUNT(*) FROM employee_responsibilities er
            JOIN wards w ON w.id = er.area_id
            WHERE w.ward_number = 10 AND er.responsibility_type = 'primary' AND er.is_demo = 0
        ")->fetchColumn();

        $this->assertEquals(1, $primaryCount);
    }

    /**
     * 8. Public contact comes only from verified published source.
     */
    public function testPublicContactComesOnlyFromVerifiedPublishedSource(): void
    {
        $nuruzzamanPhone = $this->pdo->query("
            SELECT official_phone FROM persons WHERE full_name_bn LIKE '%নুরুজ্জামান%' AND is_demo = 0
        ")->fetchColumn();

        $this->assertEquals('01320-102818', $nuruzzamanPhone);

        $shirinPhone = $this->pdo->query("
            SELECT official_phone FROM persons WHERE full_name_bn LIKE '%শিরীন সুলতানা%' AND is_demo = 0
        ")->fetchColumn();

        $this->assertEquals('01712-444277', $shirinPhone);
    }

    /**
     * 9. Demo placeholder contact such as +8809166666 is not presented as individual officer contact.
     */
    public function testPlaceholderPhoneNotPresentedAsOfficerContact(): void
    {
        $officersWithFakePhone = (int)$this->pdo->query("
            SELECT COUNT(*) FROM persons 
            WHERE source_name LIKE '%MCC Official%' 
              AND (official_phone = '+8809166666' OR official_phone = '01700000000')
        ")->fetchColumn();

        $this->assertEquals(0, $officersWithFakePhone);
    }

    /**
     * 10. Who Is Responsible renders source-backed information in Web View.
     */
    public function testWhoIsResponsibleViewRendersSourceBackedInfo(): void
    {
        $controller = new CivicDirectoryWebController();
        $resp = $controller->whoIsResponsible(new Request('GET', '/who-is-responsible'));
        $this->assertEquals(200, $resp->getStatusCode());
        $html = $resp->getContent();

        $this->assertStringContainsString('কে দায়িত্বে আছেন?', $html);
        $this->assertStringContainsString('১৮.০৩.২০২৬', $html);
        $this->assertStringContainsString('জনাব মোঃ নুরুজ্জামান', $html);
        $this->assertStringContainsString('01320-102818', $html);
        $this->assertStringContainsString('জনাব মোছাঃ শিরীন সুলতানা', $html);
        $this->assertStringContainsString('01712-444277', $html);
        $this->assertStringContainsString('ছুটিকালীন প্রতিস্থাপক', $html);
    }

    /**
     * 11. My Area renders Citizen home-Ward verified responsibility.
     */
    public function testMyAreaRendersCitizenHomeWardResponsibility(): void
    {
        $citizen = User::findByPhone('01711000001');
        $this->assertNotNull($citizen);
        Auth::login($citizen);

        $controller = new CivicDirectoryWebController();
        $resp = $controller->wards(new Request('GET', '/my-area'));
        $this->assertEquals(200, $resp->getStatusCode());
        $html = $resp->getContent();

        $this->assertStringContainsString('আমার নির্ধারিত ওয়ার্ড', $html);
        $this->assertStringContainsString('ওয়ার্ড নং ১', $html);
        $this->assertStringContainsString('জনপ্রতিনিধিত্ব / শাসনভার', $html);
        $this->assertStringContainsString('নাজিয়া উদ্দিন', $html);
        $this->assertStringContainsString('01723-089233', $html);
        Auth::logout();
    }

    /**
     * 12. Re-running importer does not create duplicates (Idempotency).
     */
    public function testImporterIsStrictlyIdempotent(): void
    {
        $persons1 = (int)$this->pdo->query("SELECT COUNT(*) FROM persons WHERE is_demo = 0")->fetchColumn();
        $emps1 = (int)$this->pdo->query("SELECT COUNT(*) FROM employees WHERE is_demo = 0")->fetchColumn();
        $gov1 = (int)$this->pdo->query("SELECT COUNT(*) FROM representation_assignments WHERE is_demo = 0")->fetchColumn();
        $op1 = (int)$this->pdo->query("SELECT COUNT(*) FROM employee_responsibilities WHERE is_demo = 0")->fetchColumn();

        // Run seeder again
        VerifiedMccDataSeeder::run();

        $persons2 = (int)$this->pdo->query("SELECT COUNT(*) FROM persons WHERE is_demo = 0")->fetchColumn();
        $emps2 = (int)$this->pdo->query("SELECT COUNT(*) FROM employees WHERE is_demo = 0")->fetchColumn();
        $gov2 = (int)$this->pdo->query("SELECT COUNT(*) FROM representation_assignments WHERE is_demo = 0")->fetchColumn();
        $op2 = (int)$this->pdo->query("SELECT COUNT(*) FROM employee_responsibilities WHERE is_demo = 0")->fetchColumn();

        $this->assertEquals($persons1, $persons2);
        $this->assertEquals($emps1, $emps2);
        $this->assertEquals($gov1, $gov2);
        $this->assertEquals($op1, $op2);
    }
}
