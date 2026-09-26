<?php

declare(strict_types=1);

namespace AmarMayor\Database\Seeders;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use PDO;

class WardSupervisorsSeeder
{
    private const DEFAULT_PASSWORD = 'password123';

    public static function run(): void
    {
        $pdo = DatabaseManager::getConnection();
        $passwordHash = Security::hashPassword(self::DEFAULT_PASSWORD);

        $supervisorRoleId = (int)$pdo->query("SELECT id FROM roles WHERE slug = 'supervisor' LIMIT 1")->fetchColumn();
        if (!$supervisorRoleId) {
            echo "Error: Supervisor role not found.\n";
            return;
        }

        $deptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'waste_management' LIMIT 1")->fetchColumn() ?: 1;

        for ($w = 1; $w <= 33; $w++) {
            $email = "supervisor.ward{$w}@demo.local";
            $phone = sprintf('017110010%02d', $w);
            $lookupHash = Security::phoneLookupHash($phone);
            $nameBn = "ওয়ার্ড " . to_bn_number((string)$w) . " পরিদর্শক";
            $nameEn = "Ward {$w} Supervisor";
            $desigBn = "ওয়ার্ড পরিদর্শক (ওয়ার্ড " . to_bn_number((string)$w) . ")";
            $desigEn = "Ward Conservancy Inspector (Ward {$w})";
            $empCode = sprintf('MCC-SUP-%03d', $w);

            // 1. Create or Find User
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $userId = $stmt->fetchColumn();

            if (!$userId) {
                $uStmt = $pdo->prepare("
                    INSERT INTO users (uuid, phone, phone_lookup_hash, email, password_hash, user_type, status, is_demo, created_at)
                    VALUES (UUID(), ?, ?, ?, ?, 'staff', 'active', 1, NOW())
                ");
                $uStmt->execute([$phone, $lookupHash, $email, $passwordHash]);
                $userId = (int)$pdo->lastInsertId();
            } else {
                $userId = (int)$userId;
                $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$passwordHash, $userId]);
            }

            // 2. Attach Role (user_roles: user_id, role_id)
            $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $supervisorRoleId]);

            // 3. Person Record (official_phone, official_email)
            $pStmt = $pdo->prepare("SELECT id FROM persons WHERE user_id = ? LIMIT 1");
            $pStmt->execute([$userId]);
            $personId = $pStmt->fetchColumn();

            if (!$personId) {
                $inPerson = $pdo->prepare("
                    INSERT INTO persons (user_id, full_name_bn, full_name_en, official_phone, official_email, is_demo, created_at)
                    VALUES (?, ?, ?, ?, ?, 1, NOW())
                ");
                $inPerson->execute([$userId, $nameBn, $nameEn, $phone, $email]);
                $personId = (int)$pdo->lastInsertId();
            } else {
                $personId = (int)$personId;
            }

            // 4. Employee Record
            $eStmt = $pdo->prepare("SELECT id FROM employees WHERE person_id = ? OR employee_code = ? LIMIT 1");
            $eStmt->execute([$personId, $empCode]);
            $empId = $eStmt->fetchColumn();

            if (!$empId) {
                $inEmp = $pdo->prepare("
                    INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, employment_type, duty_status, created_at)
                    VALUES (?, ?, ?, ?, 'permanent', 'active', NOW())
                ");
                $inEmp->execute([$personId, $empCode, $desigBn, $desigEn]);
                $empId = (int)$pdo->lastInsertId();
            } else {
                $empId = (int)$empId;
                $pdo->prepare("UPDATE employees SET person_id = ?, designation_bn = ?, designation_en = ? WHERE id = ?")
                    ->execute([$personId, $desigBn, $desigEn, $empId]);
            }

            // 5. Ward Responsibility (area_type: ward, area_id: ward_number)
            $pdo->prepare("DELETE FROM employee_responsibilities WHERE employee_id = ?")->execute([$empId]);
            $respStmt = $pdo->prepare("
                INSERT INTO employee_responsibilities (employee_id, area_type, area_id, responsibility_type, effective_from, verification_status, is_demo, created_at)
                VALUES (?, 'ward', ?, 'primary', CURDATE(), 'demo_test', 1, NOW())
            ");
            $respStmt->execute([$empId, $w]);

            // 6. Employee Posting
            $pdo->prepare("DELETE FROM employee_postings WHERE employee_id = ?")->execute([$empId]);
            $postStmt = $pdo->prepare("
                INSERT INTO employee_postings (employee_id, department_id, posting_type, effective_from, created_at)
                VALUES (?, ?, 'primary', CURDATE(), NOW())
            ");
            $postStmt->execute([$empId, $deptId]);
        }

        // 7. Re-link existing complaints and field tasks for each ward to its dedicated supervisor
        $pdo->exec("
            UPDATE complaints c
            JOIN wards w ON w.id = c.ward_id
            JOIN employee_responsibilities er ON er.area_type = 'ward' AND er.area_id = w.ward_number AND (er.effective_to IS NULL OR er.effective_to >= NOW())
            JOIN employees e ON e.id = er.employee_id
            JOIN persons p ON p.id = e.person_id
            JOIN users u ON u.id = p.user_id
            JOIN user_roles ur ON ur.user_id = u.id
            JOIN roles r ON r.id = ur.role_id AND r.slug = 'supervisor'
            SET c.current_supervisor_employee_id = e.id
        ");

        $pdo->exec("
            UPDATE field_tasks ft
            JOIN complaints c ON c.id = ft.complaint_id
            SET ft.supervisor_employee_id = c.current_supervisor_employee_id
            WHERE c.current_supervisor_employee_id IS NOT NULL
        ");
    }
}