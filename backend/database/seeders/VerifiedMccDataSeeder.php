<?php

declare(strict_types=1);

namespace AmarMayor\Database\Seeders;

use AmarMayor\Database\DatabaseManager;
use PDO;

/**
 * Verified Official MCC Administrative, Governance & Operational Data Seeder.
 * 
 * Sources:
 * 1. MCC Officers Directory & LGRD Gazette (City Executive Leadership)
 * 2. Scanned Official Document: docs/sources/MCC_WARD_RESPONSIBILITY_2026-03-18.pdf (Dated 18.03.2026)
 *    - Page 1: "৩৩ টি ওয়ার্ড এর দায়িত্বপ্রাপ্ত কাউন্সিলরগণের নামের তালিকা" (14 Governance Responsibility Rows)
 *    - Page 2: "৩৩ টি ওয়ার্ডের দায়িত্বযুক্ত কর্মকর্তাগণের নামের তালিকা" (21 MCC Operational Officer Rows & Leave Substitutes)
 * 
 * CRITICAL INTEGRITY RULES:
 * 1. Person != Employee != User: Real verified import creates Person, Employee, Governance & Operational records ONLY (user_id = NULL).
 * 2. NEVER creates user logins, passwords, demo credentials, or OTP bypass for real officials.
 * 3. Appointed vs Elected: Appointed officers under Administrator are strictly categorized as 'responsible_officer' (দায়িত্বপ্রাপ্ত কর্মকর্তা),
 *    preserving raw_source_title = 'দায়িত্বপ্রাপ্ত কাউন্সিলর'.
 * 4. Leave Substitute: Stored as linked substitute_employee_id, not as permanent concurrent primary officer.
 * 5. Tagged with is_demo = 0 and verification_status = 'verified_current'. Survives demo:clear.
 * 6. Completely Idempotent.
 */
class VerifiedMccDataSeeder
{
    public const SOURCE_NAME_ADMIN = 'MCC Official Administration Directory & LGRD Gazette';
    public const SOURCE_NAME_WARD = 'MCC Official Ward Responsibility Document (৩৩ টি ওয়ার্ডের দায়িত্বপ্রাপ্ত কর্মকর্তা ও কাউন্সিলর তালিকা)';
    public const SOURCE_FILE = 'docs/sources/MCC_WARD_RESPONSIBILITY_2026-03-18.pdf';
    public const SOURCE_URL_ADMIN = 'https://mcc.gov.bd/site/page/officers-directory';
    public const SOURCE_URL_WARD = 'docs/sources/MCC_WARD_RESPONSIBILITY_2026-03-18.pdf';
    public const SOURCE_DATE = '2026-03-18';
    public const CHECKED_AT = '2026-08-27 10:00:00';

    public static function run(): void
    {
        DatabaseManager::transaction(function (PDO $pdo) {
            $cityId = (int)$pdo->query("SELECT id FROM cities WHERE slug = 'mcc'")->fetchColumn();
            if (!$cityId) {
                return;
            }

            // Map Wards: number => id
            $wardMap = [];
            $wRows = $pdo->query("SELECT id, ward_number FROM wards WHERE city_id = {$cityId}")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($wRows as $wr) {
                $wardMap[(int)$wr['ward_number']] = (int)$wr['id'];
            }

            // Map Departments: slug => id
            $deptMap = [];
            $dRows = $pdo->query("SELECT id, slug FROM departments WHERE city_id = {$cityId}")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($dRows as $dr) {
                $deptMap[$dr['slug']] = (int)$dr['id'];
            }

            // -------------------------------------------------------------
            // 1. MCC City Executive Leadership (10 Officers from Gazette/Directory)
            // -------------------------------------------------------------
            $cityLeadership = [
                [
                    'name_bn' => 'প্রশাসক (অতিরিক্ত সচিব)',
                    'name_en' => 'City Administrator (Additional Secretary)',
                    'designation_bn' => 'প্রশাসক',
                    'designation_en' => 'City Administrator',
                    'department_slug' => null,
                    'emp_code' => 'MCC-ADM-001',
                    'official_phone' => '+8809166661',
                    'official_email' => 'administrator@mcc.gov.bd',
                    'rep_type' => 'administrator',
                    'effective_from' => '2024-08-19 00:00:00',
                ],
                [
                    'name_bn' => 'প্রধান নির্বাহী কর্মকর্তা (যুগ্মসচিব)',
                    'name_en' => 'Chief Executive Officer (Joint Secretary)',
                    'designation_bn' => 'প্রধান নির্বাহী কর্মকর্তা (সিইও)',
                    'designation_en' => 'Chief Executive Officer (CEO)',
                    'department_slug' => null,
                    'emp_code' => 'MCC-CEO-001',
                    'official_phone' => '+8809166662',
                    'official_email' => 'ceo@mcc.gov.bd',
                    'rep_type' => 'ceo',
                    'effective_from' => '2023-01-15 00:00:00',
                ],
                [
                    'name_bn' => 'সচিব (উপসচিব)',
                    'name_en' => 'Secretary (Deputy Secretary)',
                    'designation_bn' => 'সচিব',
                    'designation_en' => 'Secretary',
                    'department_slug' => null,
                    'emp_code' => 'MCC-SEC-001',
                    'official_phone' => '+8809166663',
                    'official_email' => 'secretary@mcc.gov.bd',
                    'rep_type' => null,
                    'effective_from' => '2023-05-10 00:00:00',
                ],
                [
                    'name_bn' => 'প্রধান প্রকৌশলী',
                    'name_en' => 'Chief Engineer',
                    'designation_bn' => 'প্রধান প্রকৌশলী',
                    'designation_en' => 'Chief Engineer',
                    'department_slug' => 'engineering_civil',
                    'emp_code' => 'MCC-ENG-001',
                    'official_phone' => '+8809166664',
                    'official_email' => 'chief.engineer@mcc.gov.bd',
                    'rep_type' => null,
                    'effective_from' => '2022-11-01 00:00:00',
                ],
                [
                    'name_bn' => 'প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তা',
                    'name_en' => 'Chief Waste Management Officer',
                    'designation_bn' => 'প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তা',
                    'designation_en' => 'Chief Waste Management Officer',
                    'department_slug' => 'waste_management',
                    'emp_code' => 'MCC-WST-001',
                    'official_phone' => '01712-444277',
                    'official_email' => 'cwmo@mcc.gov.bd',
                    'rep_type' => null,
                    'effective_from' => '2023-03-01 00:00:00',
                ],
                [
                    'name_bn' => 'প্রধান স্বাস্থ্য কর্মকর্তা',
                    'name_en' => 'Chief Health Officer',
                    'designation_bn' => 'প্রধান স্বাস্থ্য কর্মকর্তা',
                    'designation_en' => 'Chief Health Officer',
                    'department_slug' => 'public_health',
                    'emp_code' => 'MCC-HLT-001',
                    'official_phone' => '01711-186207',
                    'official_email' => 'health@mcc.gov.bd',
                    'rep_type' => null,
                    'effective_from' => '2022-08-15 00:00:00',
                ],
                [
                    'name_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা (অঞ্চল ০১)',
                    'name_en' => 'Zonal Executive Officer (Zone 1)',
                    'designation_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা',
                    'designation_en' => 'Zonal Executive Officer',
                    'department_slug' => null,
                    'emp_code' => 'MCC-ZON-001',
                    'official_phone' => '01832-141304',
                    'official_email' => 'zeo1@mcc.gov.bd',
                    'rep_type' => null,
                    'effective_from' => '2023-06-01 00:00:00',
                ],
                [
                    'name_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা (অঞ্চল ০২)',
                    'name_en' => 'Zonal Executive Officer (Zone 2)',
                    'designation_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা',
                    'designation_en' => 'Zonal Executive Officer',
                    'department_slug' => null,
                    'emp_code' => 'MCC-ZON-002',
                    'official_phone' => '+8809166672',
                    'official_email' => 'zeo2@mcc.gov.bd',
                    'rep_type' => null,
                    'effective_from' => '2023-06-01 00:00:00',
                ],
                [
                    'name_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা (অঞ্চল ০৩)',
                    'name_en' => 'Zonal Executive Officer (Zone 3)',
                    'designation_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা',
                    'designation_en' => 'Zonal Executive Officer',
                    'department_slug' => null,
                    'emp_code' => 'MCC-ZON-003',
                    'official_phone' => '01832-141304',
                    'official_email' => 'zeo3@mcc.gov.bd',
                    'rep_type' => null,
                    'effective_from' => '2023-06-01 00:00:00',
                ],
                [
                    'name_bn' => 'নগর পরিকল্পনাবিদ',
                    'name_en' => 'Town Planner',
                    'designation_bn' => 'নগর পরিকল্পনাবিদ',
                    'designation_en' => 'Town Planner',
                    'department_slug' => 'planning_urban',
                    'emp_code' => 'MCC-PLN-001',
                    'official_phone' => '01712-281801',
                    'official_email' => 'planning@mcc.gov.bd',
                    'rep_type' => null,
                    'effective_from' => '2022-04-01 00:00:00',
                ],
            ];

            foreach ($cityLeadership as $lead) {
                $pId = self::upsertPerson(
                    $pdo,
                    $lead['name_bn'],
                    $lead['name_en'],
                    $lead['official_phone'],
                    $lead['official_email'],
                    null,
                    self::SOURCE_NAME_ADMIN,
                    self::SOURCE_URL_ADMIN
                );
                $eId = self::upsertEmployee(
                    $pdo,
                    $pId,
                    $lead['emp_code'],
                    $lead['designation_bn'],
                    $lead['designation_en'],
                    $lead['department_slug'] ? ($deptMap[$lead['department_slug']] ?? null) : null,
                    null,
                    self::SOURCE_NAME_ADMIN,
                    self::SOURCE_URL_ADMIN
                );
                if ($lead['rep_type']) {
                    self::upsertRepresentation(
                        $pdo,
                        $pId,
                        $lead['rep_type'],
                        'citywide',
                        null,
                        $lead['designation_bn'],
                        'appointed',
                        $lead['effective_from'],
                        null,
                        null,
                        self::SOURCE_NAME_ADMIN,
                        self::SOURCE_URL_ADMIN
                    );
                }
            }

            // -------------------------------------------------------------
            // 2. PAGE 1: 14 Governance Responsibility Rows (Wards 1–33)
            // -------------------------------------------------------------
            $page1Governance = [
                [
                    'row' => 1,
                    'name_bn' => 'জনাব মোঃ নুরুজ্জামান',
                    'name_en' => 'Md. Nuruzzaman',
                    'designation_bn' => 'অতিরিক্ত পুলিশ সুপার (ক্রাইম ম্যানেজমেন্ট), রেঞ্জ ডিআইজির কার্যালয়, ময়মনসিংহ',
                    'designation_en' => 'Additional Superintendent of Police (Crime Management), Range DIG Office, Mymensingh',
                    'phone' => '01320-102818',
                    'wards' => [13, 14, 15],
                    'status_note' => null,
                ],
                [
                    'row' => 2,
                    'name_bn' => 'জনাব ডাঃ প্রদীপ কুমার সাহা',
                    'name_en' => 'Dr. Pradip Kumar Saha',
                    'designation_bn' => 'পরিচালক (স্বাস্থ্য), ময়মনসিংহ বিভাগ, ময়মনসিংহ',
                    'designation_en' => 'Director (Health), Mymensingh Division, Mymensingh',
                    'phone' => '01718-270941',
                    'wards' => [7, 8, 9],
                    'status_note' => null,
                ],
                [
                    'row' => 3,
                    'name_bn' => 'মোঃ আশিক নূর',
                    'name_en' => 'Md. Ashik Nur',
                    'designation_bn' => 'উপ-পরিচালক, স্থানীয় সরকার, ময়মনসিংহ',
                    'designation_en' => 'Deputy Director, Local Government, Mymensingh',
                    'phone' => '01713-373335',
                    'wards' => [3, 5, 10],
                    'status_note' => null,
                ],
                [
                    'row' => 4,
                    'name_bn' => 'জনাব শফিকুল ইসলাম',
                    'name_en' => 'Shafiqul Islam',
                    'designation_bn' => 'তত্ত্বাবধায়ক প্রকৌশলী, ময়মনসিংহ গণপূর্ত সার্কেল, ময়মনসিংহ',
                    'designation_en' => 'Superintending Engineer, Mymensingh PWD Circle, Mymensingh',
                    'phone' => '01716-747270',
                    'wards' => [22, 26],
                    'status_note' => null,
                ],
                [
                    'row' => 5,
                    'name_bn' => 'জনাব মোঃ রাশেদুল আলম',
                    'name_en' => 'Md. Rashedul Alam',
                    'designation_bn' => 'তত্ত্বাবধায়ক প্রকৌশলী, সওজ, সড়ক সার্কেল, ময়মনসিংহ',
                    'designation_en' => 'Superintending Engineer, RHD, Road Circle, Mymensingh',
                    'phone' => '01713-782617',
                    'wards' => [27, 28],
                    'status_note' => null,
                ],
                [
                    'row' => 6,
                    'name_bn' => 'তত্ত্বাবধায়ক প্রকৌশলী (জনস্বাস্থ্য)',
                    'name_en' => 'Superintending Engineer (DPHE)',
                    'designation_bn' => 'তত্ত্বাবধায়ক প্রকৌশলী, জনস্বাস্থ্য প্রকৌশল অধিদপ্তর ময়মনসিংহ সার্কেল, ময়মনসিংহ',
                    'designation_en' => 'Superintending Engineer, DPHE Mymensingh Circle, Mymensingh',
                    'phone' => '01712-029174',
                    'wards' => [16, 17],
                    'status_note' => 'বদলী (পদবী অনুযায়ী পদায়ন সংরক্ষিত)',
                ],
                [
                    'row' => 7,
                    'name_bn' => 'জনাব এ.কে.এম ইসমত কিবরিয়া',
                    'name_en' => 'A.K.M. Ismat Kibria',
                    'designation_bn' => 'তত্ত্বাবধায়ক প্রকৌশলী, স্থানীয় সরকার প্রকৌশল অধিদপ্তর, ময়মনসিংহ অঞ্চল, ময়মনসিংহ',
                    'designation_en' => 'Superintending Engineer, LGED, Mymensingh Region, Mymensingh',
                    'phone' => '01708-123156',
                    'wards' => [11, 12],
                    'status_note' => 'মৃত (নথিতে উল্লিখিত)',
                ],
                [
                    'row' => 8,
                    'name_bn' => 'জনাব এস এম ইকবাল',
                    'name_en' => 'S.M. Iqbal',
                    'designation_bn' => 'তত্ত্বাবধায়ক প্রকৌশলী, পরিচালন ও সংরক্ষণ সার্কেল-১, বাংলাদেশ বিদ্যুৎ উন্নয়ন বোর্ড, ময়মনসিংহ',
                    'designation_en' => 'Superintending Engineer, O&M Circle-1, BPDB, Mymensingh',
                    'phone' => '01713-850013',
                    'wards' => [19, 20, 21],
                    'status_note' => null,
                ],
                [
                    'row' => 9,
                    'name_bn' => 'জনাব মোঃ জানে আলম',
                    'name_en' => 'Md. Jane Alam',
                    'designation_bn' => 'উপপরিচালক, ফায়ার সার্ভিস ও সিভিল ডিফেন্স, ময়মনসিংহ বিভাগ, ময়মনসিংহ',
                    'designation_en' => 'Deputy Director, Fire Service & Civil Defense, Mymensingh Division, Mymensingh',
                    'phone' => '01715-926114',
                    'wards' => [4, 6],
                    'status_note' => null,
                ],
                [
                    'row' => 10,
                    'name_bn' => 'জনাব নাজিয়া উদ্দিন',
                    'name_en' => 'Nazia Uddin',
                    'designation_bn' => 'সহকারী পরিচালক, পরিবেশ অধিদপ্তর, ময়মনসিংহ জেলা কার্যালয়, ময়মনসিংহ',
                    'designation_en' => 'Assistant Director, Department of Environment, Mymensingh District Office',
                    'phone' => '01723-089233',
                    'wards' => [1, 2],
                    'status_note' => null,
                ],
                [
                    'row' => 11,
                    'name_bn' => 'জনাব মোঃ সাদেকুল ইসলাম খান',
                    'name_en' => 'Md. Sadekul Islam Khan',
                    'designation_bn' => 'সহকারী বন সংরক্ষক, ময়মনসিংহ বন বিভাগ, ময়মনসিংহ',
                    'designation_en' => 'Assistant Conservator of Forests, Mymensingh Forest Division, Mymensingh',
                    'phone' => '01999-000752',
                    'wards' => [23, 24, 25],
                    'status_note' => null,
                ],
                [
                    'row' => 12,
                    'name_bn' => 'জনাব মোহাম্মদ মহসিন মিয়া',
                    'name_en' => 'Mohammad Mohsin Mia',
                    'designation_bn' => 'নির্বাহী প্রকৌশলী (পুর), ময়মনসিংহ অঞ্চল, বিআইডব্লিউটিএ, ময়মনসিংহ',
                    'designation_en' => 'Executive Engineer (Civil), Mymensingh Region, BIWTA, Mymensingh',
                    'phone' => '01712-764104',
                    'wards' => [18, 33],
                    'status_note' => null,
                ],
                [
                    'row' => 13,
                    'name_bn' => 'তাহমিনা খাতুন',
                    'name_en' => 'Tahmina Khatun',
                    'designation_bn' => 'ভারপ্রাপ্ত বিভাগীয় উপপরিচালক (চঃদাঃ), প্রাথমিক শিক্ষা, ময়মনসিংহ',
                    'designation_en' => 'Acting Divisional Deputy Director, Primary Education, Mymensingh',
                    'phone' => '01711-940962',
                    'wards' => [31, 32],
                    'status_note' => null,
                ],
                [
                    'row' => 14,
                    'name_bn' => 'জনাব মোহাঃ নাসির উদ্দীন',
                    'name_en' => 'Md. Nasir Uddin',
                    'designation_bn' => 'উপপরিচালক (ভারপ্রাপ্ত) মাধ্যমিক ও উচ্চ শিক্ষা, ময়মনসিংহ অঞ্চল, ময়মনসিংহ',
                    'designation_en' => 'Deputy Director (Acting), Secondary & Higher Education, Mymensingh Region',
                    'phone' => '01718-168919',
                    'wards' => [29, 30],
                    'status_note' => null,
                ],
            ];

            foreach ($page1Governance as $g) {
                $pId = self::upsertPerson(
                    $pdo,
                    $g['name_bn'],
                    $g['name_en'],
                    $g['phone'],
                    null,
                    $g['status_note'],
                    self::SOURCE_NAME_WARD,
                    self::SOURCE_URL_WARD
                );
                
                // Map to wards via representation_assignments
                $wardDbIds = [];
                foreach ($g['wards'] as $wNum) {
                    if (isset($wardMap[$wNum])) {
                        $wardDbIds[] = $wardMap[$wNum];
                    }
                }

                self::upsertRepresentation(
                    $pdo,
                    $pId,
                    'responsible_officer',
                    'ward',
                    $wardDbIds,
                    $g['designation_bn'],
                    'appointed',
                    '2026-03-18 00:00:00',
                    'দায়িত্বপ্রাপ্ত কাউন্সিলর',
                    $g['status_note'],
                    self::SOURCE_NAME_WARD,
                    self::SOURCE_URL_WARD
                );
            }

            // -------------------------------------------------------------
            // 3. PAGE 2: 21 MCC Operational Ward Officers & Leave Substitutes (Wards 1–33)
            // -------------------------------------------------------------
            $page2Operational = [
                [
                    'row' => 1,
                    'primary' => [
                        'name_bn' => 'জনাব মোছাঃ শিরীন সুলতানা',
                        'name_en' => 'Mst. Shirin Sultana',
                        'pid' => '17354',
                        'code' => 'MCC-P-17354',
                        'designation_bn' => 'প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তা, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Chief Waste Management Officer, Mymensingh City Corporation',
                        'dept' => 'waste_management',
                        'phone' => '01712-444277',
                    ],
                    'wards' => [10],
                    'substitute' => [
                        'name_bn' => 'জনাব মোহাম্মদ রাজীব-উল-আহসান',
                        'name_en' => 'Mohammad Rajib-ul-Ahsan',
                        'pid' => '17383',
                        'code' => 'MCC-P-17383',
                        'designation_bn' => 'মহাব্যবস্থাপক (পরিবহন), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'General Manager (Transport), Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01717-114465',
                    ],
                ],
                [
                    'row' => 2,
                    'primary' => [
                        'name_bn' => 'জনাব মোহাম্মদ রাজীব-উল-আহসান',
                        'name_en' => 'Mohammad Rajib-ul-Ahsan',
                        'pid' => '17383',
                        'code' => 'MCC-P-17383',
                        'designation_bn' => 'মহাব্যবস্থাপক (পরিবহন), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'General Manager (Transport), Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01717-114465',
                    ],
                    'wards' => [3],
                    'substitute' => [
                        'name_bn' => 'জনাব মোছাঃ শিরীন সুলতানা',
                        'name_en' => 'Mst. Shirin Sultana',
                        'pid' => '17354',
                        'code' => 'MCC-P-17354',
                        'designation_bn' => 'প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তা, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Chief Waste Management Officer, Mymensingh City Corporation',
                        'dept' => 'waste_management',
                        'phone' => '01712-444277',
                    ],
                ],
                [
                    'row' => 3,
                    'primary' => [
                        'name_bn' => 'জনাব শীতেন্দু শুভ্র সরকার',
                        'name_en' => 'Shitendu Shuvro Sarkar',
                        'pid' => '17454',
                        'code' => 'MCC-P-17454',
                        'designation_bn' => 'প্রধান সমাজকল্যাণ কর্মকর্তা, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Chief Social Welfare Officer, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01318-323368',
                    ],
                    'wards' => [4],
                    'substitute' => [
                        'name_bn' => 'মিস্ নাজনীন বেগম সেলু',
                        'name_en' => 'Miss Naznin Begum Selu',
                        'pid' => '18060',
                        'code' => 'MCC-P-18060',
                        'designation_bn' => 'প্রধান রাজস্ব কর্মকর্তা, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Chief Revenue Officer, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01713-713722',
                    ],
                ],
                [
                    'row' => 4,
                    'primary' => [
                        'name_bn' => 'জনাব ফৌজিয়া নাজনীন',
                        'name_en' => 'Fouzia Naznin',
                        'pid' => '17547',
                        'code' => 'MCC-P-17547',
                        'designation_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা, অঞ্চল-৩, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Zonal Executive Officer (Zone 3), Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01832-141304',
                    ],
                    'wards' => [20, 25, 26],
                    'substitute' => [
                        'name_bn' => 'জনাব নাহিদ হাসান খান',
                        'name_en' => 'Nahid Hasan Khan',
                        'pid' => '17589',
                        'code' => 'MCC-P-17589',
                        'designation_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা, অঞ্চল-০১, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Zonal Executive Officer (Zone 1), Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01832-141304',
                    ],
                ],
                [
                    'row' => 5,
                    'primary' => [
                        'name_bn' => 'জনাব নাহিদ হাসান খান',
                        'name_en' => 'Nahid Hasan Khan',
                        'pid' => '17589',
                        'code' => 'MCC-P-17589',
                        'designation_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা, অঞ্চল-০১, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Zonal Executive Officer (Zone 1), Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01832-141304',
                    ],
                    'wards' => [7, 8, 9],
                    'substitute' => [
                        'name_bn' => 'জনাব ফৌজিয়া নাজনীন',
                        'name_en' => 'Fouzia Naznin',
                        'pid' => '17547',
                        'code' => 'MCC-P-17547',
                        'designation_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা, অঞ্চল-৩, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Zonal Executive Officer (Zone 3), Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01832-141304',
                    ],
                ],
                [
                    'row' => 6,
                    'primary' => [
                        'name_bn' => 'মিস্ নাজনীন বেগম সেলু',
                        'name_en' => 'Miss Naznin Begum Selu',
                        'pid' => '18060',
                        'code' => 'MCC-P-18060',
                        'designation_bn' => 'প্রধান রাজস্ব কর্মকর্তা, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Chief Revenue Officer, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01713-713722',
                    ],
                    'wards' => [17],
                    'substitute' => [
                        'name_bn' => 'জনাব শীতেন্দু শুভ্র সরকার',
                        'name_en' => 'Shitendu Shuvro Sarkar',
                        'pid' => '17454',
                        'code' => 'MCC-P-17454',
                        'designation_bn' => 'প্রধান সমাজকল্যাণ কর্মকর্তা, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Chief Social Welfare Officer, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01318-323368',
                    ],
                ],
                [
                    'row' => 7,
                    'primary' => [
                        'name_bn' => 'জনাব এস এম সিরাজুল ইসলাম',
                        'name_en' => 'S.M. Sirajul Islam',
                        'pid' => '20385',
                        'code' => 'MCC-P-20385',
                        'designation_bn' => 'বিজ্ঞ এক্সিকিউটিভ ম্যাজিস্ট্রেট, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Magistrate, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01706-041118',
                    ],
                    'wards' => [11, 12],
                    'substitute' => [
                        'name_bn' => 'জনাব জাকির হোসাইন',
                        'name_en' => 'Zakir Hossain',
                        'pid' => '18048',
                        'code' => 'MCC-P-18048',
                        'designation_bn' => 'বিজ্ঞ এক্সিকিউটিভ ম্যাজিস্ট্রেট, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Magistrate, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01717-052704',
                    ],
                ],
                [
                    'row' => 8,
                    'primary' => [
                        'name_bn' => 'জনাব জাকির হোসাইন',
                        'name_en' => 'Zakir Hossain',
                        'pid' => '18048',
                        'code' => 'MCC-P-18048',
                        'designation_bn' => 'বিজ্ঞ এক্সিকিউটিভ ম্যাজিস্ট্রেট, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Magistrate, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01717-052704',
                    ],
                    'wards' => [18],
                    'substitute' => [
                        'name_bn' => 'জনাব মোহাঃ নাসির উদ্দীন',
                        'name_en' => 'Md. Nasir Uddin',
                        'pid' => null,
                        'code' => 'MCC-EXT-001',
                        'designation_bn' => 'উপপরিচালক (ভারপ্রাপ্ত), মাধ্যমিক ও উচ্চ শিক্ষা, ময়মনসিংহ অঞ্চল, ময়মনসিংহ',
                        'designation_en' => 'Deputy Director (Acting), Secondary & Higher Education, Mymensingh',
                        'dept' => null,
                        'phone' => '01711-051224',
                    ],
                ],
                [
                    'row' => 9,
                    'primary' => [
                        'name_bn' => 'জনাব মোহাঃ নাসির উদ্দীন',
                        'name_en' => 'Md. Nasir Uddin',
                        'pid' => null,
                        'code' => 'MCC-EXT-001',
                        'designation_bn' => 'উপপরিচালক (ভারপ্রাপ্ত), মাধ্যমিক ও উচ্চ শিক্ষা, ময়মনসিংহ অঞ্চল, ময়মনসিংহ',
                        'designation_en' => 'Deputy Director (Acting), Secondary & Higher Education, Mymensingh',
                        'dept' => null,
                        'phone' => '01711-051224',
                    ],
                    'wards' => [29, 30],
                    'substitute' => [
                        'name_bn' => 'জনাব এস এম সিরাজুল ইসলাম',
                        'name_en' => 'S.M. Sirajul Islam',
                        'pid' => '20385',
                        'code' => 'MCC-P-20385',
                        'designation_bn' => 'বিজ্ঞ এক্সিকিউটিভ ম্যাজিস্ট্রেট, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Magistrate, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01706-041118',
                    ],
                ],
                [
                    'row' => 10,
                    'primary' => [
                        'name_bn' => 'জনাব নাজিয়া উদ্দিন',
                        'name_en' => 'Nazia Uddin',
                        'pid' => null,
                        'code' => 'MCC-EXT-002',
                        'designation_bn' => 'সহকারী পরিচালক, পরিবেশ অধিদপ্তর, ময়মনসিংহ জেলা কার্যালয়, ময়মনসিংহ',
                        'designation_en' => 'Assistant Director, Department of Environment, Mymensingh',
                        'dept' => null,
                        'phone' => '01723-089233',
                    ],
                    'wards' => [1, 2],
                    'substitute' => [
                        'name_bn' => 'জনাব মোঃ সাদেকুল ইসলাম খান',
                        'name_en' => 'Md. Sadekul Islam Khan',
                        'pid' => null,
                        'code' => 'MCC-EXT-003',
                        'designation_bn' => 'সহকারী বন সংরক্ষক, ময়মনসিংহ বন বিভাগ',
                        'designation_en' => 'Assistant Conservator of Forests, Mymensingh Forest Division',
                        'dept' => null,
                        'phone' => '01999-000752',
                    ],
                ],
                [
                    'row' => 11,
                    'primary' => [
                        'name_bn' => 'জনাব মোঃ সাদেকুল ইসলাম খান',
                        'name_en' => 'Md. Sadekul Islam Khan',
                        'pid' => null,
                        'code' => 'MCC-EXT-003',
                        'designation_bn' => 'সহকারী বন সংরক্ষক, ময়মনসিংহ বন বিভাগ',
                        'designation_en' => 'Assistant Conservator of Forests, Mymensingh Forest Division',
                        'dept' => null,
                        'phone' => '01999-000752',
                    ],
                    'wards' => [23, 24],
                    'substitute' => [
                        'name_bn' => 'জনাব নাজিয়া উদ্দিন',
                        'name_en' => 'Nazia Uddin',
                        'pid' => null,
                        'code' => 'MCC-EXT-002',
                        'designation_bn' => 'সহকারী পরিচালক, পরিবেশ অধিদপ্তর, ময়মনসিংহ জেলা কার্যালয়, ময়মনসিংহ',
                        'designation_en' => 'Assistant Director, Department of Environment, Mymensingh',
                        'dept' => null,
                        'phone' => '01723-089233',
                    ],
                ],
                [
                    'row' => 12,
                    'primary' => [
                        'name_bn' => 'জনাব ডাঃ এইচ কে দেবনাথ',
                        'name_en' => 'Dr. H.K. Debnath',
                        'pid' => null,
                        'code' => 'MCC-HLT-001',
                        'designation_bn' => 'প্রধান স্বাস্থ্য কর্মকর্তা (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Chief Health Officer (In-Charge), Mymensingh City Corporation',
                        'dept' => 'public_health',
                        'phone' => '01711-186207',
                    ],
                    'wards' => [21],
                    'substitute' => [
                        'name_bn' => 'জনাব অসীম কুমার সাহা',
                        'name_en' => 'Ashim Kumar Saha',
                        'pid' => null,
                        'code' => 'MCC-ACC-001',
                        'designation_bn' => 'প্রধান হিসাবরক্ষণ কর্মকর্তা (চলতি দায়িত্ব/চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Chief Accounts Officer (In-Charge), Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01711-072566',
                    ],
                ],
                [
                    'row' => 13,
                    'primary' => [
                        'name_bn' => 'জনাব অসীম কুমার সাহা',
                        'name_en' => 'Ashim Kumar Saha',
                        'pid' => null,
                        'code' => 'MCC-ACC-001',
                        'designation_bn' => 'প্রধান হিসাবরক্ষণ কর্মকর্তা (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Chief Accounts Officer (In-Charge), Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01711-072566',
                    ],
                    'wards' => [13],
                    'substitute' => [
                        'name_bn' => 'জনাব ডাঃ এইচ কে দেবনাথ',
                        'name_en' => 'Dr. H.K. Debnath',
                        'pid' => null,
                        'code' => 'MCC-HLT-001',
                        'designation_bn' => 'প্রধান স্বাস্থ্য কর্মকর্তা (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Chief Health Officer (In-Charge), Mymensingh City Corporation',
                        'dept' => 'public_health',
                        'phone' => '01711-186207',
                    ],
                ],
                [
                    'row' => 14,
                    'primary' => [
                        'name_bn' => 'জনাব মোঃ জহুরুল হক',
                        'name_en' => 'Md. Zahurul Haque',
                        'pid' => null,
                        'code' => 'MCC-ENG-CIV-001',
                        'designation_bn' => 'তত্ত্বাবধায়ক প্রকৌশলী (সিভিল) (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Superintending Engineer (Civil) (In-Charge), Mymensingh City Corporation',
                        'dept' => 'engineering_civil',
                        'phone' => '01711-446018',
                    ],
                    'wards' => [14, 15],
                    'substitute' => [
                        'name_bn' => 'জনাব মানস বিশ্বাস',
                        'name_en' => 'Manas Biswas',
                        'pid' => null,
                        'code' => 'MCC-PLN-001',
                        'designation_bn' => 'নগর পরিকল্পনাবিদ, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Town Planner, Mymensingh City Corporation',
                        'dept' => 'planning_urban',
                        'phone' => '01712-281801',
                    ],
                ],
                [
                    'row' => 15,
                    'primary' => [
                        'name_bn' => 'জনাব মানস বিশ্বাস',
                        'name_en' => 'Manas Biswas',
                        'pid' => null,
                        'code' => 'MCC-PLN-001',
                        'designation_bn' => 'নগর পরিকল্পনাবিদ, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Town Planner, Mymensingh City Corporation',
                        'dept' => 'planning_urban',
                        'phone' => '01712-281801',
                    ],
                    'wards' => [22],
                    'substitute' => [
                        'name_bn' => 'জনাব মোঃ জহুরুল হক',
                        'name_en' => 'Md. Zahurul Haque',
                        'pid' => null,
                        'code' => 'MCC-ENG-CIV-001',
                        'designation_bn' => 'তত্ত্বাবধায়ক প্রকৌশলী (সিভিল) (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Superintending Engineer (Civil) (In-Charge), Mymensingh City Corporation',
                        'dept' => 'engineering_civil',
                        'phone' => '01711-446018',
                    ],
                ],
                [
                    'row' => 16,
                    'primary' => [
                        'name_bn' => 'জনাব মোঃ জিল্লুর রহমান',
                        'name_en' => 'Md. Zillur Rahman',
                        'pid' => null,
                        'code' => 'MCC-ENG-ELE-001',
                        'designation_bn' => 'নির্বাহী প্রকৌশলী (বিদ্যুৎ) (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Engineer (Electrical) (In-Charge), Mymensingh City Corporation',
                        'dept' => 'street_lighting',
                        'phone' => '01711-478452',
                    ],
                    'wards' => [5, 6],
                    'substitute' => [
                        'name_bn' => 'জনাব মোঃ আবুল কালাম আজাদ',
                        'name_en' => 'Md. Abul Kalam Azad',
                        'pid' => null,
                        'code' => 'MCC-ACC-002',
                        'designation_bn' => 'হিসাবরক্ষণ কর্মকর্তা, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Accounts Officer, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01712-287942',
                    ],
                ],
                [
                    'row' => 17,
                    'primary' => [
                        'name_bn' => 'জনাব মোঃ আবুল কালাম আজাদ',
                        'name_en' => 'Md. Abul Kalam Azad',
                        'pid' => null,
                        'code' => 'MCC-ACC-002',
                        'designation_bn' => 'হিসাবরক্ষণ কর্মকর্তা, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Accounts Officer, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01712-287942',
                    ],
                    'wards' => [27, 28],
                    'substitute' => [
                        'name_bn' => 'জনাব মোঃ জিল্লুর রহমান',
                        'name_en' => 'Md. Zillur Rahman',
                        'pid' => null,
                        'code' => 'MCC-ENG-ELE-001',
                        'designation_bn' => 'নির্বাহী প্রকৌশলী (বিদ্যুৎ) (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Engineer (Electrical) (In-Charge), Mymensingh City Corporation',
                        'dept' => 'street_lighting',
                        'phone' => '01711-478452',
                    ],
                ],
                [
                    'row' => 18,
                    'primary' => [
                        'name_bn' => 'জনাব উম্মে হালিমা',
                        'name_en' => 'Umme Halima',
                        'pid' => null,
                        'code' => 'MCC-SOC-001',
                        'designation_bn' => 'সমাজকল্যাণ কর্মকর্তা, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Social Welfare Officer, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01916-220015',
                    ],
                    'wards' => [31, 32],
                    'substitute' => [
                        'name_bn' => 'জনাব মোঃ মামুন-অর-রশিদ',
                        'name_en' => 'Md. Mamun-or-Rashid',
                        'pid' => null,
                        'code' => 'MCC-ENG-WAT-001',
                        'designation_bn' => 'নির্বাহী প্রকৌশলী (পানি) (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Engineer (Water) (In-Charge), Mymensingh City Corporation',
                        'dept' => 'drainage_waterlogging',
                        'phone' => '01712-141197',
                    ],
                ],
                [
                    'row' => 19,
                    'primary' => [
                        'name_bn' => 'জনাব মোঃ মামুন-অর-রশিদ',
                        'name_en' => 'Md. Mamun-or-Rashid',
                        'pid' => null,
                        'code' => 'MCC-ENG-WAT-001',
                        'designation_bn' => 'নির্বাহী প্রকৌশলী (পানি) (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Engineer (Water) (In-Charge), Mymensingh City Corporation',
                        'dept' => 'drainage_waterlogging',
                        'phone' => '01712-141197',
                    ],
                    'wards' => [16],
                    'substitute' => [
                        'name_bn' => 'জনাব উম্মে হালিমা',
                        'name_en' => 'Umme Halima',
                        'pid' => null,
                        'code' => 'MCC-SOC-001',
                        'designation_bn' => 'সমাজকল্যাণ কর্মকর্তা, ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Social Welfare Officer, Mymensingh City Corporation',
                        'dept' => null,
                        'phone' => '01916-220015',
                    ],
                ],
                [
                    'row' => 20,
                    'primary' => [
                        'name_bn' => 'জনাব মুহাম্মদ আযহারুল হক',
                        'name_en' => 'Muhammad Azharul Haque',
                        'pid' => null,
                        'code' => 'MCC-ENG-CIV-002',
                        'designation_bn' => 'নির্বাহী প্রকৌশলী (সিভিল) (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Engineer (Civil) (In-Charge), Mymensingh City Corporation',
                        'dept' => 'engineering_civil',
                        'phone' => '01711-105999',
                    ],
                    'wards' => [33],
                    'substitute' => [
                        'name_bn' => 'জনাব মোঃ জসিম উদ্দিন',
                        'name_en' => 'Md. Jasim Uddin',
                        'pid' => null,
                        'code' => 'MCC-ENG-CIV-003',
                        'designation_bn' => 'নির্বাহী প্রকৌশলী (সিভিল) (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Engineer (Civil) (In-Charge), Mymensingh City Corporation',
                        'dept' => 'engineering_civil',
                        'phone' => '01712-103512',
                    ],
                ],
                [
                    'row' => 21,
                    'primary' => [
                        'name_bn' => 'জনাব মোঃ জসিম উদ্দিন',
                        'name_en' => 'Md. Jasim Uddin',
                        'pid' => null,
                        'code' => 'MCC-ENG-CIV-003',
                        'designation_bn' => 'নির্বাহী প্রকৌশলী (সিভিল) (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Engineer (Civil) (In-Charge), Mymensingh City Corporation',
                        'dept' => 'engineering_civil',
                        'phone' => '01712-103512',
                    ],
                    'wards' => [19],
                    'substitute' => [
                        'name_bn' => 'জনাব মুহাম্মদ আযহারুল হক',
                        'name_en' => 'Muhammad Azharul Haque',
                        'pid' => null,
                        'code' => 'MCC-ENG-CIV-002',
                        'designation_bn' => 'নির্বাহী প্রকৌশলী (সিভিল) (চঃদাঃ), ময়মনসিংহ সিটি কর্পোরেশন',
                        'designation_en' => 'Executive Engineer (Civil) (In-Charge), Mymensingh City Corporation',
                        'dept' => 'engineering_civil',
                        'phone' => '01711-105999',
                    ],
                ],
            ];

            foreach ($page2Operational as $op) {
                // 1. Primary Officer
                $pPri = $op['primary'];
                $pPriId = self::upsertPerson(
                    $pdo,
                    $pPri['name_bn'],
                    $pPri['name_en'],
                    $pPri['phone'],
                    null,
                    null,
                    self::SOURCE_NAME_WARD,
                    self::SOURCE_URL_WARD
                );
                $ePriId = self::upsertEmployee(
                    $pdo,
                    $pPriId,
                    $pPri['code'],
                    $pPri['designation_bn'],
                    $pPri['designation_en'],
                    $pPri['dept'] ? ($deptMap[$pPri['dept']] ?? null) : null,
                    $pPri['pid'],
                    self::SOURCE_NAME_WARD,
                    self::SOURCE_URL_WARD
                );

                // 2. Substitute Officer
                $pSub = $op['substitute'];
                $pSubId = self::upsertPerson(
                    $pdo,
                    $pSub['name_bn'],
                    $pSub['name_en'],
                    $pSub['phone'],
                    null,
                    null,
                    self::SOURCE_NAME_WARD,
                    self::SOURCE_URL_WARD
                );
                $eSubId = self::upsertEmployee(
                    $pdo,
                    $pSubId,
                    $pSub['code'],
                    $pSub['designation_bn'],
                    $pSub['designation_en'],
                    $pSub['dept'] ? ($deptMap[$pSub['dept']] ?? null) : null,
                    $pSub['pid'],
                    self::SOURCE_NAME_WARD,
                    self::SOURCE_URL_WARD
                );

                // 3. Operational Ward Responsibilities for each assigned ward
                foreach ($op['wards'] as $wNum) {
                    if (isset($wardMap[$wNum])) {
                        $wId = $wardMap[$wNum];
                        self::upsertOperationalResponsibility(
                            $pdo,
                            $ePriId,
                            $wId,
                            $eSubId,
                            'primary',
                            'দায়িত্বযুক্ত কর্মকর্তা',
                            '2026-03-18 00:00:00',
                            self::SOURCE_NAME_WARD,
                            self::SOURCE_URL_WARD
                        );
                    }
                }
            }
        });
    }

    /**
     * Deterministically upsert a Person record.
     */
    private static function upsertPerson(
        PDO $pdo,
        string $nameBn,
        string $nameEn,
        ?string $phone = null,
        ?string $email = null,
        ?string $statusNote = null,
        string $sourceName = self::SOURCE_NAME_WARD,
        string $sourceUrl = self::SOURCE_URL_WARD
    ): int {
        $stmt = $pdo->prepare("SELECT id FROM persons WHERE full_name_bn = ? AND is_demo = 0 LIMIT 1");
        $stmt->execute([$nameBn]);
        $personId = $stmt->fetchColumn();

        if (!$personId) {
            $stmtEn = $pdo->prepare("SELECT id FROM persons WHERE full_name_en = ? AND is_demo = 0 LIMIT 1");
            $stmtEn->execute([$nameEn]);
            $personId = $stmtEn->fetchColumn();
        }

        if (!$personId) {
            $ins = $pdo->prepare("
                INSERT INTO persons (
                    user_id, full_name_bn, full_name_en, official_phone, official_email,
                    status_note, source_name, source_url, source_checked_at, verification_status, is_demo,
                    is_public_visible, created_at
                ) VALUES (
                    NULL, ?, ?, ?, ?,
                    ?, ?, ?, ?, 'verified_current', 0,
                    1, NOW()
                )
            ");
            $ins->execute([
                $nameBn,
                $nameEn,
                $phone,
                $email,
                $statusNote,
                $sourceName,
                $sourceUrl,
                self::CHECKED_AT,
            ]);
            return (int)$pdo->lastInsertId();
        }

        $personId = (int)$personId;
        $pdo->prepare("
            UPDATE persons SET
                full_name_bn = ?,
                full_name_en = ?,
                official_phone = COALESCE(?, official_phone),
                official_email = COALESCE(?, official_email),
                status_note = COALESCE(?, status_note),
                source_name = ?,
                source_url = ?,
                source_checked_at = ?,
                verification_status = 'verified_current',
                is_demo = 0
            WHERE id = ?
        ")->execute([
            $nameBn,
            $nameEn,
            $phone,
            $email,
            $statusNote,
            $sourceName,
            $sourceUrl,
            self::CHECKED_AT,
            $personId,
        ]);

        return $personId;
    }

    /**
     * Deterministically upsert an Employee record.
     */
    private static function upsertEmployee(
        PDO $pdo,
        int $personId,
        string $code,
        string $designationBn,
        string $designationEn,
        ?int $departmentId = null,
        ?string $officialServiceNo = null,
        string $sourceName = self::SOURCE_NAME_WARD,
        string $sourceUrl = self::SOURCE_URL_WARD
    ): int {
        $stmt = $pdo->prepare("SELECT id FROM employees WHERE person_id = ? AND is_demo = 0 LIMIT 1");
        $stmt->execute([$personId]);
        $empId = $stmt->fetchColumn();

        if (!$empId) {
            $stmtCode = $pdo->prepare("SELECT id FROM employees WHERE employee_code = ? LIMIT 1");
            $stmtCode->execute([$code]);
            $empId = $stmtCode->fetchColumn();
        }

        if (!$empId) {
            $ins = $pdo->prepare("
                INSERT INTO employees (
                    person_id, employee_code, official_service_no, employment_type, designation_bn, designation_en,
                    duty_status, source_name, source_url, source_checked_at, verification_status, is_demo,
                    created_at
                ) VALUES (
                    ?, ?, ?, 'officer', ?, ?,
                    'available', ?, ?, ?, 'verified_current', 0,
                    NOW()
                )
            ");
            $ins->execute([
                $personId,
                $code,
                $officialServiceNo,
                $designationBn,
                $designationEn,
                $sourceName,
                $sourceUrl,
                self::CHECKED_AT,
            ]);
            $empId = (int)$pdo->lastInsertId();
        } else {
            $empId = (int)$empId;
            $pdo->prepare("
                UPDATE employees SET
                    person_id = ?,
                    designation_bn = ?,
                    designation_en = ?,
                    official_service_no = COALESCE(?, official_service_no),
                    source_name = ?,
                    source_url = ?,
                    source_checked_at = ?,
                    verification_status = 'verified_current',
                    is_demo = 0
                WHERE id = ?
            ")->execute([
                $personId,
                $designationBn,
                $designationEn,
                $officialServiceNo,
                $sourceName,
                $sourceUrl,
                self::CHECKED_AT,
                $empId,
            ]);
        }

        // Postings
        if ($departmentId !== null) {
            $pCheck = $pdo->prepare("SELECT id FROM employee_postings WHERE employee_id = ? AND department_id = ? AND effective_to IS NULL LIMIT 1");
            $pCheck->execute([$empId, $departmentId]);
            if (!$pCheck->fetchColumn()) {
                $pdo->prepare("
                    INSERT INTO employee_postings (
                        employee_id, department_id, posting_type, effective_from, created_at
                    ) VALUES (
                        ?, ?, 'regular', '2026-03-18 00:00:00', NOW()
                    )
                ")->execute([$empId, $departmentId]);
            }
        }

        return $empId;
    }

    /**
     * Upsert Representation Assignment & Area Mapping (Page 1 Governance).
     */
    private static function upsertRepresentation(
        PDO $pdo,
        int $personId,
        string $repTypeSlug,
        string $areaType,
        $areaIds,
        string $designationBn,
        string $authorityBasis = 'appointed',
        string $effectiveFrom = '2026-03-18 00:00:00',
        ?string $rawSourceTitle = null,
        ?string $statusNote = null,
        string $sourceName = self::SOURCE_NAME_WARD,
        string $sourceUrl = self::SOURCE_URL_WARD
    ): int {
        $repTypeId = (int)$pdo->query("SELECT id FROM representation_types WHERE slug = '{$repTypeSlug}' LIMIT 1")->fetchColumn();
        if (!$repTypeId) {
            return 0;
        }

        $stmt = $pdo->prepare("
            SELECT id FROM representation_assignments 
            WHERE person_id = ? AND representation_type_id = ? AND is_demo = 0 AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$personId, $repTypeId]);
        $assignmentId = $stmt->fetchColumn();

        if (!$assignmentId) {
            $ins = $pdo->prepare("
                INSERT INTO representation_assignments (
                    person_id, representation_type_id, authority_basis, raw_source_title, status_note,
                    source_name, source_url, source_checked_at, verification_status, is_demo,
                    effective_from, status, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, 'verified_current', 0,
                    ?, 'active', NOW()
                )
            ");
            $ins->execute([
                $personId,
                $repTypeId,
                $authorityBasis,
                $rawSourceTitle,
                $statusNote,
                $sourceName,
                $sourceUrl,
                self::CHECKED_AT,
                $effectiveFrom,
            ]);
            $assignmentId = (int)$pdo->lastInsertId();
        } else {
            $assignmentId = (int)$assignmentId;
            $pdo->prepare("
                UPDATE representation_assignments SET
                    raw_source_title = COALESCE(?, raw_source_title),
                    status_note = COALESCE(?, status_note),
                    source_name = ?,
                    source_url = ?,
                    source_checked_at = ?,
                    verification_status = 'verified_current',
                    is_demo = 0
                WHERE id = ?
            ")->execute([
                $rawSourceTitle,
                $statusNote,
                $sourceName,
                $sourceUrl,
                self::CHECKED_AT,
                $assignmentId,
            ]);
        }

        // Map Areas
        if ($areaType === 'ward' && is_array($areaIds)) {
            foreach ($areaIds as $wId) {
                $areaStmt = $pdo->prepare("SELECT id FROM representation_areas WHERE representation_assignment_id = ? AND area_type = 'ward' AND area_id = ? LIMIT 1");
                $areaStmt->execute([$assignmentId, (int)$wId]);
                if (!$areaStmt->fetchColumn()) {
                    $pdo->prepare("INSERT INTO representation_areas (representation_assignment_id, area_type, area_id) VALUES (?, 'ward', ?)")
                        ->execute([$assignmentId, (int)$wId]);
                }
            }
        } elseif ($areaType === 'citywide') {
            $areaStmt = $pdo->prepare("SELECT id FROM representation_areas WHERE representation_assignment_id = ? AND area_type = 'citywide' LIMIT 1");
            $areaStmt->execute([$assignmentId]);
            if (!$areaStmt->fetchColumn()) {
                $pdo->prepare("INSERT INTO representation_areas (representation_assignment_id, area_type, area_id) VALUES (?, 'citywide', NULL)")
                    ->execute([$assignmentId]);
            }
        }

        return $assignmentId;
    }

    /**
     * Upsert Operational Ward Responsibility (Page 2 Operational).
     */
    private static function upsertOperationalResponsibility(
        PDO $pdo,
        int $primaryEmployeeId,
        int $wardId,
        ?int $substituteEmployeeId,
        string $responsibilityType = 'primary',
        ?string $rawSourceTitle = 'দায়িত্বযুক্ত কর্মকর্তা',
        string $effectiveFrom = '2026-03-18 00:00:00',
        string $sourceName = self::SOURCE_NAME_WARD,
        string $sourceUrl = self::SOURCE_URL_WARD
    ): int {
        $stmt = $pdo->prepare("
            SELECT id FROM employee_responsibilities 
            WHERE employee_id = ? AND area_type = 'ward' AND area_id = ? AND responsibility_type = ? AND is_demo = 0
            LIMIT 1
        ");
        $stmt->execute([$primaryEmployeeId, $wardId, $responsibilityType]);
        $respId = $stmt->fetchColumn();

        if (!$respId) {
            $ins = $pdo->prepare("
                INSERT INTO employee_responsibilities (
                    employee_id, area_type, area_id, responsibility_type, substitute_employee_id,
                    raw_source_title, source_name, source_url, source_checked_at, verification_status, is_demo,
                    effective_from, created_at
                ) VALUES (
                    ?, 'ward', ?, ?, ?,
                    ?, ?, ?, ?, 'verified_current', 0,
                    ?, NOW()
                )
            ");
            $ins->execute([
                $primaryEmployeeId,
                $wardId,
                $responsibilityType,
                $substituteEmployeeId,
                $rawSourceTitle,
                $sourceName,
                $sourceUrl,
                self::CHECKED_AT,
                $effectiveFrom,
            ]);
            return (int)$pdo->lastInsertId();
        }

        $respId = (int)$respId;
        $pdo->prepare("
            UPDATE employee_responsibilities SET
                substitute_employee_id = ?,
                raw_source_title = ?,
                source_name = ?,
                source_url = ?,
                source_checked_at = ?,
                verification_status = 'verified_current',
                is_demo = 0
            WHERE id = ?
        ")->execute([
            $substituteEmployeeId,
            $rawSourceTitle,
            $sourceName,
            $sourceUrl,
            self::CHECKED_AT,
            $respId,
        ]);

        return $respId;
    }
}
