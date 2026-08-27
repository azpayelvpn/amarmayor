<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

$pdo = \AmarMayor\Database\DatabaseManager::getConnection();

$officialPersons = (int)$pdo->query("SELECT COUNT(*) FROM persons WHERE source_name LIKE '%MCC Official%'")->fetchColumn();
$officialEmployees = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE source_name LIKE '%MCC Official%'")->fetchColumn();
$govAssignments = (int)$pdo->query("SELECT COUNT(*) FROM representation_assignments WHERE source_name LIKE '%MCC Official%'")->fetchColumn();
$govWards = (int)$pdo->query("SELECT COUNT(DISTINCT area_id) FROM representation_areas ra JOIN representation_assignments r ON r.id = ra.representation_assignment_id WHERE r.source_name LIKE '%MCC Official%' AND ra.area_type = 'ward'")->fetchColumn();
$opAssignments = (int)$pdo->query("SELECT COUNT(*) FROM employee_responsibilities WHERE source_name LIKE '%MCC Official%' AND area_type = 'ward'")->fetchColumn();
$opWards = (int)$pdo->query("SELECT COUNT(DISTINCT area_id) FROM employee_responsibilities WHERE source_name LIKE '%MCC Official%' AND area_type = 'ward'")->fetchColumn();
$substituteRelationships = (int)$pdo->query("SELECT COUNT(*) FROM employee_responsibilities WHERE source_name LIKE '%MCC Official%' AND substitute_employee_id IS NOT NULL")->fetchColumn();
$publishedContacts = (int)$pdo->query("SELECT COUNT(*) FROM persons WHERE source_name LIKE '%MCC Official%' AND official_phone IS NOT NULL")->fetchColumn();
$verifiedWithUsers = (int)$pdo->query("SELECT COUNT(*) FROM persons WHERE source_name LIKE '%MCC Official%' AND user_id IS NOT NULL")->fetchColumn();

// Missing wards
$allWards = range(1, 33);
$govCovered = $pdo->query("SELECT DISTINCT w.ward_number FROM representation_areas ra JOIN representation_assignments r ON r.id = ra.representation_assignment_id JOIN wards w ON w.id = ra.area_id WHERE r.source_name LIKE '%MCC Official%' AND ra.area_type = 'ward'")->fetchAll(PDO::FETCH_COLUMN);
$govMissing = array_diff($allWards, array_map('intval', $govCovered));

$opCovered = $pdo->query("SELECT DISTINCT w.ward_number FROM employee_responsibilities er JOIN wards w ON w.id = er.area_id WHERE er.source_name LIKE '%MCC Official%' AND er.area_type = 'ward'")->fetchAll(PDO::FETCH_COLUMN);
$opMissing = array_diff($allWards, array_map('intval', $opCovered));

echo "==================================================\n";
echo "MCC VERIFIED WARD RESPONSIBILITY BREAKDOWN\n";
echo "==================================================\n";
echo "Unique verified official persons:   {$officialPersons}\n";
echo "Verified official employees:        {$officialEmployees}\n";
echo "Governance assignments (total):     {$govAssignments} (14 Ward rows + 2 City Executive)\n";
echo "Governance Ward coverage:           {$govWards} / 33 Wards\n";
echo "Operational Ward assignments:       {$opAssignments} (covering 33 Wards across 21 rows)\n";
echo "Operational Ward coverage:          {$opWards} / 33 Wards\n";
echo "Leave substitute relationships:     {$substituteRelationships}\n";
echo "Published official contacts:        {$publishedContacts}\n";
echo "Wards missing governance:           " . (empty($govMissing) ? 'None (0)' : implode(',', $govMissing)) . "\n";
echo "Wards missing operational:          " . (empty($opMissing) ? 'None (0)' : implode(',', $opMissing)) . "\n";
echo "Verified officials with Users:      {$verifiedWithUsers} (Expected: 0)\n";
echo "==================================================\n";
