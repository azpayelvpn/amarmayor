<?php

declare(strict_types=1);

namespace AmarMayor\Database\Seeders;

use AmarMayor\Database\DatabaseManager;
use PDO;

/**
 * Structural Seeder: Seeds MCC Official Structural Configuration & Taxonomies.
 * Critical Rules:
 * 1. 1 City, 3 Zones, 33 Wards (with exact approved Zone-Ward mapping from Spec).
 * 2. 11 Reserved Seats (coverage unassigned - NO arithmetic grouping).
 * 3. Idempotent execution (safe to run multiple times).
 */
class StructuralSeeder
{
    public static function run(): void
    {
        DatabaseManager::transaction(function (PDO $pdo) {
            self::seedCity($pdo);
            self::seedZonesAndWards($pdo);
            self::seedReservedSeats($pdo);
            self::seedRepresentationTypes($pdo);
            self::seedRolesAndPermissions($pdo);
            self::seedComplaintTaxonomy($pdo);
            self::seedClassificationsAndPriorities($pdo);
            self::seedSkills($pdo);
            self::seedCoreSettings($pdo);
        });
    }

    private static function seedCity(PDO $pdo): void
    {
        $stmt = $pdo->prepare("SELECT id FROM cities WHERE slug = 'mcc'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $insert = $pdo->prepare("INSERT INTO cities (
                slug, name_bn, name_en, short_name_bn, short_name_en,
                official_address_bn, official_address_en, official_phone, official_email,
                website, timezone, created_at
            ) VALUES (
                'mcc', 'ময়মনসিংহ সিটি কর্পোরেশন', 'Mymensingh City Corporation', 'মসিক', 'MCC',
                'পৌর ভবন, শহীদ গোলন্দাজ সড়ক, ময়মনসিংহ-২২০০', 'City Corporation Bhaban, Shaheed Golandaz Road, Mymensingh-2200',
                '+8809166666', 'info@mcc.gov.bd', 'https://mcc.gov.bd', 'Asia/Dhaka', NOW()
            )");
            $insert->execute();
        }
    }

    private static function seedZonesAndWards(PDO $pdo): void
    {
        $cityId = (int)$pdo->query("SELECT id FROM cities WHERE slug = 'mcc'")->fetchColumn();

        // 1. Seed 3 Zones
        $zonesData = [
            1 => ['name_bn' => 'অঞ্চল ০১', 'name_en' => 'Zone 01'],
            2 => ['name_bn' => 'অঞ্চল ০২', 'name_en' => 'Zone 02'],
            3 => ['name_bn' => 'অঞ্চল ০৩', 'name_en' => 'Zone 03'],
        ];

        $zoneIds = [];
        foreach ($zonesData as $zoneNum => $z) {
            $stmt = $pdo->prepare("SELECT id FROM zones WHERE city_id = ? AND zone_number = ?");
            $stmt->execute([$cityId, $zoneNum]);
            $existingId = $stmt->fetchColumn();

            if (!$existingId) {
                $ins = $pdo->prepare("INSERT INTO zones (city_id, zone_number, name_bn, name_en, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
                $ins->execute([$cityId, $zoneNum, $z['name_bn'], $z['name_en']]);
                $zoneIds[$zoneNum] = (int)$pdo->lastInsertId();
            } else {
                $zoneIds[$zoneNum] = (int)$existingId;
            }
        }

        // 2. Exact Zone-Ward Mapping from MASTER_SPEC.md (Section 64 & Pre-Implementation Gate)
        $zoneWards = [
            1 => [1, 2, 4, 6, 11, 12, 27, 28, 29, 30], // 10 Wards
            2 => [3, 5, 7, 8, 9, 10, 16, 17, 18, 31, 32, 33], // 12 Wards
            3 => [13, 14, 15, 19, 20, 21, 22, 23, 24, 25, 26], // 11 Wards
        ];

        $bnNumerals = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];

        foreach ($zoneWards as $zoneNum => $wards) {
            $zId = $zoneIds[$zoneNum];
            foreach ($wards as $wNum) {
                $stmt = $pdo->prepare("SELECT id FROM wards WHERE city_id = ? AND ward_number = ?");
                $stmt->execute([$cityId, $wNum]);
                $wId = $stmt->fetchColumn();

                $padEn = sprintf("%02d", $wNum);
                $padBn = strtr($padEn, $bnNumerals);
                $nameBn = "ওয়ার্ড " . $padBn;
                $nameEn = "Ward " . $padEn;

                if (!$wId) {
                    $ins = $pdo->prepare("INSERT INTO wards (city_id, zone_id, ward_number, name_bn, name_en, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
                    $ins->execute([$cityId, $zId, $wNum, $nameBn, $nameEn]);
                    $wId = (int)$pdo->lastInsertId();

                    // Record initial ward_zone_history
                    $histIns = $pdo->prepare("INSERT INTO ward_zone_history (ward_id, zone_id, effective_from, authority_order, created_at) VALUES (?, ?, NOW(), 'Initial MCC Gazette', NOW())");
                    $histIns->execute([$wId, $zId]);
                }
            }
        }
    }

    private static function seedReservedSeats(PDO $pdo): void
    {
        $cityId = (int)$pdo->query("SELECT id FROM cities WHERE slug = 'mcc'")->fetchColumn();
        $bnNumerals = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];

        // Seed 11 Reserved Seats (coverage mapping left UNASSIGNED until verified data)
        for ($s = 1; $s <= 11; $s++) {
            $stmt = $pdo->prepare("SELECT id FROM reserved_seats WHERE city_id = ? AND seat_number = ?");
            $stmt->execute([$cityId, $s]);
            if (!$stmt->fetch()) {
                $padEn = sprintf("%02d", $s);
                $padBn = strtr($padEn, $bnNumerals);
                $nameBn = "সংরক্ষিত আসন " . $padBn;
                $nameEn = "Reserved Seat " . $padEn;

                $ins = $pdo->prepare("INSERT INTO reserved_seats (city_id, seat_number, name_bn, name_en, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
                $ins->execute([$cityId, $s, $nameBn, $nameEn]);
            }
        }
    }

    private static function seedRepresentationTypes(PDO $pdo): void
    {
        $types = [
            ['slug' => 'mayor', 'name_bn' => 'মেয়র', 'name_en' => 'Mayor', 'is_electoral' => 1],
            ['slug' => 'administrator', 'name_bn' => 'প্রশাসক', 'name_en' => 'Administrator', 'is_electoral' => 0],
            ['slug' => 'ceo', 'name_bn' => 'প্রধান নির্বাহী কর্মকর্তা (সিইও)', 'name_en' => 'Chief Executive Officer (CEO)', 'is_electoral' => 0],
            ['slug' => 'general_councillor', 'name_bn' => 'সাধারণ কাউন্সিলর', 'name_en' => 'General Councillor', 'is_electoral' => 1],
            ['slug' => 'reserved_women_councillor', 'name_bn' => 'সংরক্ষিত নারী কাউন্সিলর', 'name_en' => 'Reserved Women Councillor', 'is_electoral' => 1],
            ['slug' => 'responsible_officer', 'name_bn' => 'দায়িত্বপ্রাপ্ত কর্মকর্তা (কাউন্সিলর দায়িত্ব)', 'name_en' => 'Responsible Officer (Councillor Duties)', 'is_electoral' => 0],
            ['slug' => 'acting_responsible_officer', 'name_bn' => 'ভারপ্রাপ্ত দায়িত্বপ্রাপ্ত কর্মকর্তা', 'name_en' => 'Acting Responsible Officer', 'is_electoral' => 0],
            ['slug' => 'temporary_responsible_officer', 'name_bn' => 'সাময়িক দায়িত্বপ্রাপ্ত কর্মকর্তা', 'name_en' => 'Temporary Responsible Officer', 'is_electoral' => 0],
        ];

        foreach ($types as $t) {
            $stmt = $pdo->prepare("SELECT id FROM representation_types WHERE slug = ?");
            $stmt->execute([$t['slug']]);
            if (!$stmt->fetch()) {
                $ins = $pdo->prepare("INSERT INTO representation_types (slug, name_bn, name_en, is_electoral) VALUES (?, ?, ?, ?)");
                $ins->execute([$t['slug'], $t['name_bn'], $t['name_en'], $t['is_electoral']]);
            }
        }
    }

    private static function seedRolesAndPermissions(PDO $pdo): void
    {
        $roles = [
            ['slug' => 'citizen', 'name_bn' => 'নাগরিক', 'name_en' => 'Citizen'],
            ['slug' => 'field_worker', 'name_bn' => 'মাঠকর্মী', 'name_en' => 'Field Worker'],
            ['slug' => 'team_leader', 'name_bn' => 'দলনেতা', 'name_en' => 'Team Leader'],
            ['slug' => 'field_supervisor', 'name_bn' => 'মাঠ তদারককারী / সুপারভাইজার', 'name_en' => 'Field Supervisor'],
            ['slug' => 'ward_responsible_officer', 'name_bn' => 'ওয়ার্ড দায়িত্বপ্রাপ্ত কর্মকর্তা', 'name_en' => 'Ward Responsible Officer'],
            ['slug' => 'ward_inspector', 'name_bn' => 'ওয়ার্ড পরিদর্শক', 'name_en' => 'Ward Inspector'],
            ['slug' => 'general_councillor', 'name_bn' => 'সাধারণ কাউন্সিলর', 'name_en' => 'General Councillor'],
            ['slug' => 'reserved_councillor', 'name_bn' => 'সংরক্ষিত নারী কাউন্সিলর', 'name_en' => 'Reserved Councillor'],
            ['slug' => 'zone_executive_officer', 'name_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা', 'name_en' => 'Zone Executive Officer'],
            ['slug' => 'department_head', 'name_bn' => 'বিভাগীয় প্রধান', 'name_en' => 'Department Head'],
            ['slug' => 'service_unit_in_charge', 'name_bn' => 'শাখা ইন-চার্জ', 'name_en' => 'Service Unit In-Charge'],
            ['slug' => 'ceo', 'name_bn' => 'প্রধান নির্বাহী কর্মকর্তা (সিইও)', 'name_en' => 'Chief Executive Officer'],
            ['slug' => 'mayor', 'name_bn' => 'মেয়র', 'name_en' => 'Mayor'],
            ['slug' => 'administrator', 'name_bn' => 'প্রশাসক', 'name_en' => 'Administrator'],
            ['slug' => 'triage_officer', 'name_bn' => 'অভিযোগ বাছাই ও যাচাইকারী', 'name_en' => 'Triage Officer'],
            ['slug' => 'communication_officer', 'name_bn' => 'যোগাযোগ কর্মকর্তা', 'name_en' => 'Communication Officer'],
            ['slug' => 'public_relation_officer', 'name_bn' => 'জনসংযোগ কর্মকর্তা', 'name_en' => 'Public Relations Officer'],
            ['slug' => 'data_analyst', 'name_bn' => 'ডাটা অ্যানালিস্ট', 'name_en' => 'Data Analyst'],
            ['slug' => 'finance_budget_officer', 'name_bn' => 'অর্থ ও বাজেট সমন্বয়কারী', 'name_en' => 'Finance & Budget Officer'],
            ['slug' => 'external_agency_coordinator', 'name_bn' => 'বাহ্যিক সংস্থা সমন্বয়কারী', 'name_en' => 'External Agency Coordinator'],
            ['slug' => 'platform_super_admin', 'name_bn' => 'প্ল্যাটফর্ম সুপার অ্যাডমিন', 'name_en' => 'Platform Super Admin'],
            ['slug' => 'technical_super_admin', 'name_bn' => 'কারিগরি সুপার অ্যাডমিন', 'name_en' => 'Technical Super Admin'],
        ];

        foreach ($roles as $r) {
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE slug = ?");
            $stmt->execute([$r['slug']]);
            if (!$stmt->fetch()) {
                $ins = $pdo->prepare("INSERT INTO roles (slug, name_bn, name_en, is_system) VALUES (?, ?, ?, 1)");
                $ins->execute([$r['slug'], $r['name_bn'], $r['name_en']]);
            }
        }
    }

    private static function seedComplaintTaxonomy(PDO $pdo): void
    {
        $categories = [
            [
                'slug' => 'cleanliness', 'name_bn' => 'পরিচ্ছন্নতা ও বর্জ্য', 'name_en' => 'Cleanliness & Waste', 'icon' => 'trash',
                'subcategories' => [
                    ['slug' => 'garbage_pile', 'name_bn' => 'খোলা জায়গায় ময়লার স্তূপ', 'name_en' => 'Unattended Garbage Pile', 'priority' => 'p3_normal', 'classification' => 'quick_action', 'camera' => 1],
                    ['slug' => 'dustbin_overflow', 'name_bn' => 'ডাস্টবিন উপচে পড়া বর্জ্য', 'name_en' => 'Overflowing Waste Bin', 'priority' => 'p3_normal', 'classification' => 'quick_action', 'camera' => 1],
                    ['slug' => 'dead_animal', 'name_bn' => 'মৃত পশু অপসারণ', 'name_en' => 'Dead Animal Removal', 'priority' => 'p1_critical', 'classification' => 'quick_action', 'camera' => 1],
                    ['slug' => 'medical_waste', 'name_bn' => 'বিপজ্জনক বা মেডিকেল বর্জ্য', 'name_en' => 'Hazardous / Medical Waste', 'priority' => 'p1_critical', 'classification' => 'quick_action', 'camera' => 1],
                ]
            ],
            [
                'slug' => 'mosquito', 'name_bn' => 'মশক নিয়ন্ত্রণ ও জনস্বাস্থ্য', 'name_en' => 'Mosquito & Public Health', 'icon' => 'bug',
                'subcategories' => [
                    ['slug' => 'mosquito_breeding_spot', 'name_bn' => 'মশার প্রজননস্থল / লার্ভা স্পট', 'name_en' => 'Mosquito Breeding Hotspot', 'priority' => 'p2_high', 'classification' => 'quick_action', 'camera' => 0],
                    ['slug' => 'fogging_request', 'name_bn' => 'ফগিং / স্প্রে করার প্রয়োজন', 'name_en' => 'Fogging Spray Request', 'priority' => 'p3_normal', 'classification' => 'quick_action', 'camera' => 0],
                ]
            ],
            [
                'slug' => 'drainage', 'name_bn' => 'ড্রেনেজ ও জলাবদ্ধতা', 'name_en' => 'Drainage & Waterlogging', 'icon' => 'water',
                'subcategories' => [
                    ['slug' => 'waterlogging_road', 'name_bn' => 'রাস্তায় জলাবদ্ধতা', 'name_en' => 'Road Waterlogging', 'priority' => 'p2_high', 'classification' => 'quick_action', 'camera' => 1],
                    ['slug' => 'blocked_drain', 'name_bn' => 'বন্ধ / ভরাট হয়ে যাওয়া ড্রেন', 'name_en' => 'Clogged Drain', 'priority' => 'p2_high', 'classification' => 'maintenance', 'camera' => 1],
                    ['slug' => 'broken_drain_slab', 'name_bn' => 'ভাঙা ড্রেনের স্ল্যাব / বিপজ্জনক গর্ত', 'name_en' => 'Broken Drain Slab Hazard', 'priority' => 'p1_critical', 'classification' => 'quick_action', 'camera' => 1],
                ]
            ],
            [
                'slug' => 'roads', 'name_bn' => 'সড়ক ও ফুটপাত', 'name_en' => 'Roads & Footpaths', 'icon' => 'signpost-2',
                'subcategories' => [
                    ['slug' => 'pothole', 'name_bn' => 'রাস্তায় বিপজ্জনক গর্ত / খানাখন্দ', 'name_en' => 'Dangerous Road Pothole', 'priority' => 'p2_high', 'classification' => 'maintenance', 'camera' => 1],
                    ['slug' => 'broken_footpath', 'name_bn' => 'ভাঙা বা অনুপযোগী ফুটপাত', 'name_en' => 'Damaged Footpath', 'priority' => 'p3_normal', 'classification' => 'maintenance', 'camera' => 1],
                ]
            ],
            [
                'slug' => 'street_lighting', 'name_bn' => 'সড়কবাতি ও বৈদ্যুতিক', 'name_en' => 'Street Light & Municipal Electrical', 'icon' => 'lightbulb',
                'subcategories' => [
                    ['slug' => 'light_not_working', 'name_bn' => 'অচল / নিভে থাকা সড়কবাতি', 'name_en' => 'Non-functioning Street Light', 'priority' => 'p3_normal', 'classification' => 'quick_action', 'camera' => 0],
                    ['slug' => 'exposed_wire', 'name_bn' => 'ঝুলন্ত বা উন্মুক্ত ঝুঁকিপূর্ণ বিদ্যুতের তার', 'name_en' => 'Exposed Electric Wire Hazard', 'priority' => 'p1_critical', 'classification' => 'quick_action', 'camera' => 1],
                ]
            ],
            [
                'slug' => 'water_supply', 'name_bn' => 'পানি সরবরাহ', 'name_en' => 'Water Supply', 'icon' => 'droplet',
                'subcategories' => [
                    ['slug' => 'pipe_leakage', 'name_bn' => 'পাইপলাইন লিকেজ / পানি অপচয়', 'name_en' => 'Water Pipe Leakage', 'priority' => 'p2_high', 'classification' => 'quick_action', 'camera' => 1],
                    ['slug' => 'contaminated_water', 'name_bn' => 'দূষিত / দুর্গন্ধযুক্ত পানি', 'name_en' => 'Contaminated Water Supply', 'priority' => 'p1_critical', 'classification' => 'technical_assessment', 'camera' => 0],
                ]
            ],
            [
                'slug' => 'encroachment', 'name_bn' => 'অবৈধ দখল ও শৃঙ্খলা', 'name_en' => 'Encroachment & Urban Order', 'icon' => 'slash-circle',
                'subcategories' => [
                    ['slug' => 'footpath_encroachment', 'name_bn' => 'ফুটপাত বা সড়ক অবৈধ দখল', 'name_en' => 'Footpath / Road Encroachment', 'priority' => 'p3_normal', 'classification' => 'administrative_service', 'camera' => 1],
                    ['slug' => 'illegal_billboard', 'name_bn' => 'অবৈধ ব্যানার / বিপজ্জনক বিলবোর্ড', 'name_en' => 'Illegal Billboard / Banner', 'priority' => 'p3_normal', 'classification' => 'maintenance', 'camera' => 1],
                ]
            ],
            [
                'slug' => 'public_assets', 'name_bn' => 'পার্ক, বাজার ও পৌর সম্পদ', 'name_en' => 'Parks / Markets / Terminals / Public Assets', 'icon' => 'shop',
                'subcategories' => [
                    ['slug' => 'park_facility_damaged', 'name_bn' => 'পার্ক বা খেলার মাঠের সুবিধা ক্ষতিগ্রস্ত', 'name_en' => 'Damaged Park / Playground Facility', 'priority' => 'p3_normal', 'classification' => 'maintenance', 'camera' => 1],
                    ['slug' => 'public_toilet_unhygienic', 'name_bn' => 'পৌর গণশৌচাগার অপরিচ্ছন্ন / অচল', 'name_en' => 'Unhygienic Public Toilet', 'priority' => 'p2_high', 'classification' => 'quick_action', 'camera' => 1],
                ]
            ],
            [
                'slug' => 'environment', 'name_bn' => 'পরিবেশ ও বৃক্ষরোপণ', 'name_en' => 'Environment & Trees', 'icon' => 'tree',
                'subcategories' => [
                    ['slug' => 'dangerous_tree_branch', 'name_bn' => 'রাস্তায় বিপজ্জনক হেলে পড়া গাছের ডাল', 'name_en' => 'Dangerous Hanging Tree Branch', 'priority' => 'p2_high', 'classification' => 'quick_action', 'camera' => 1],
                    ['slug' => 'illegal_tree_cutting', 'name_bn' => 'অনুমতিহীন গাছ কাটা / পরিবেশ ক্ষতি', 'name_en' => 'Illegal Tree Cutting / Damage', 'priority' => 'p2_high', 'classification' => 'administrative_service', 'camera' => 1],
                ]
            ],
            [
                'slug' => 'civic_services', 'name_bn' => 'নাগরিক সেবা ও সনদ', 'name_en' => 'Civic / Administrative Services', 'icon' => 'file-earmark-text',
                'subcategories' => [
                    ['slug' => 'birth_death_cert_issue', 'name_bn' => 'জন্ম/মৃত্যু সনদ সংক্রান্ত জিজ্ঞাসা', 'name_en' => 'Birth / Death Certificate Inquiry', 'priority' => 'p3_normal', 'classification' => 'administrative_service', 'camera' => 0],
                    ['slug' => 'trade_license_inquiry', 'name_bn' => 'ট্রেড লাইসেন্স ও ট্যাক্স সংক্রান্ত জিজ্ঞাসা', 'name_en' => 'Trade License / Tax Inquiry', 'priority' => 'p3_normal', 'classification' => 'administrative_service', 'camera' => 0],
                ]
            ],
            [
                'slug' => 'urgent_hazard', 'name_bn' => 'জরুরি পৌর ঝুঁকি', 'name_en' => 'Urgent Municipal Hazard', 'icon' => 'exclamation-octagon',
                'subcategories' => [
                    ['slug' => 'building_collapse_risk', 'name_bn' => 'ভবন বা দেয়াল ধস ঝুঁকি', 'name_en' => 'Imminent Structure Collapse Hazard', 'priority' => 'p1_critical', 'classification' => 'technical_assessment', 'camera' => 1],
                    ['slug' => 'fire_hazard_material', 'name_bn' => 'পৌর এলাকায় বিপজ্জনক অগ্নিকাণ্ড ঝুঁকি', 'name_en' => 'Hazardous Flammable Accumulation', 'priority' => 'p1_critical', 'classification' => 'quick_action', 'camera' => 1],
                ]
            ],
            [
                'slug' => 'other', 'name_bn' => 'অন্যান্য পৌর সমস্যা', 'name_en' => 'Other Civic Issue', 'icon' => 'three-dots',
                'subcategories' => [
                    ['slug' => 'unspecified_problem', 'name_bn' => 'অন্যান্য অনির্দিষ্ট নাগরিক সমস্যা', 'name_en' => 'Unspecified Civic Problem', 'priority' => 'p3_normal', 'classification' => 'quick_action', 'camera' => 0],
                ]
            ],
        ];

        $displayOrder = 1;
        foreach ($categories as $cat) {
            $stmt = $pdo->prepare("SELECT id FROM complaint_categories WHERE slug = ?");
            $stmt->execute([$cat['slug']]);
            $catId = $stmt->fetchColumn();

            if (!$catId) {
                $ins = $pdo->prepare("INSERT INTO complaint_categories (slug, name_bn, name_en, icon_name, display_order, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                $ins->execute([$cat['slug'], $cat['name_bn'], $cat['name_en'], $cat['icon'], $displayOrder++]);
                $catId = (int)$pdo->lastInsertId();
            }

            foreach ($cat['subcategories'] as $subcat) {
                $subStmt = $pdo->prepare("SELECT id FROM complaint_subcategories WHERE category_id = ? AND slug = ?");
                $subStmt->execute([$catId, $subcat['slug']]);
                if (!$subStmt->fetch()) {
                    $subIns = $pdo->prepare("INSERT INTO complaint_subcategories (category_id, slug, name_bn, name_en, default_priority, default_classification, requires_live_camera, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
                    $subIns->execute([$catId, $subcat['slug'], $subcat['name_bn'], $subcat['name_en'], $subcat['priority'], $subcat['classification'], $subcat['camera']]);
                }
            }
        }
    }

    private static function seedClassificationsAndPriorities(PDO $pdo): void
    {
        // 1. Classifications
        $classifications = [
            ['slug' => 'quick_action', 'name_bn' => 'তাৎক্ষণিক ব্যবস্থা (Quick Action)', 'name_en' => 'Quick Action'],
            ['slug' => 'maintenance', 'name_bn' => 'রক্ষণাবেক্ষণ প্রয়োজন (Maintenance)', 'name_en' => 'Maintenance Required'],
            ['slug' => 'technical_assessment', 'name_bn' => 'কারিগরি মূল্যায়ন প্রয়োজন (Technical Assessment)', 'name_en' => 'Technical Assessment Required'],
            ['slug' => 'project_required', 'name_bn' => 'উন্নয়ন প্রকল্প প্রয়োজন (Project Required)', 'name_en' => 'Project Required'],
            ['slug' => 'external_agency', 'name_bn' => 'বাহ্যিক সংস্থায় প্রেরণ (External Agency)', 'name_en' => 'External Agency Referral'],
            ['slug' => 'administrative_service', 'name_bn' => 'প্রশাসনিক / দাপ্তরিক সেবা (Administrative)', 'name_en' => 'Administrative Service'],
        ];

        foreach ($classifications as $c) {
            $stmt = $pdo->prepare("SELECT id FROM operational_classifications WHERE slug = ?");
            $stmt->execute([$c['slug']]);
            if (!$stmt->fetch()) {
                $ins = $pdo->prepare("INSERT INTO operational_classifications (slug, name_bn, name_en, is_active) VALUES (?, ?, ?, 1)");
                $ins->execute([$c['slug'], $c['name_bn'], $c['name_en']]);
            }
        }

        // 2. Priorities
        $priorities = [
            ['slug' => 'p1_critical', 'name_bn' => 'জরুরি (P1 Critical)', 'name_en' => 'Critical (P1)', 'order' => 1],
            ['slug' => 'p2_high', 'name_bn' => 'উচ্চ (P2 High)', 'name_en' => 'High (P2)', 'order' => 2],
            ['slug' => 'p3_normal', 'name_bn' => 'সাধারণ (P3 Normal)', 'name_en' => 'Normal (P3)', 'order' => 3],
            ['slug' => 'p4_low', 'name_bn' => 'নিম্ন (P4 Low)', 'name_en' => 'Low (P4)', 'order' => 4],
        ];

        foreach ($priorities as $p) {
            $stmt = $pdo->prepare("SELECT id FROM priorities WHERE slug = ?");
            $stmt->execute([$p['slug']]);
            if (!$stmt->fetch()) {
                $ins = $pdo->prepare("INSERT INTO priorities (slug, name_bn, name_en, display_order, is_active) VALUES (?, ?, ?, ?, 1)");
                $ins->execute([$p['slug'], $p['name_bn'], $p['name_en'], $p['order']]);
            }
        }
    }

    private static function seedSkills(PDO $pdo): void
    {
        $skills = [
            ['slug' => 'waste_collection', 'name_bn' => 'বর্জ্য সংগ্রহ ও পরিবহন', 'name_en' => 'Waste Collection & Transport'],
            ['slug' => 'drain_cleaning', 'name_bn' => 'ড্রেন পরিষ্কার ও পলি অপসারণ', 'name_en' => 'Drain Cleaning & Desilting'],
            ['slug' => 'mosquito_fogging', 'name_bn' => 'মশক নিধন স্প্রে ও ফগিং', 'name_en' => 'Mosquito Fogging & Larviciding'],
            ['slug' => 'electrical_repair', 'name_bn' => 'সড়কবাতি ও বৈদ্যুতিক মেরামত', 'name_en' => 'Streetlight & Electrical Repair'],
            ['slug' => 'plumbing', 'name_bn' => 'পাইপলাইন ও প্লাম্বিং কাজ', 'name_en' => 'Pipeline & Plumbing Works'],
            ['slug' => 'road_patching', 'name_bn' => 'সড়ক প্যাচওয়ার্ক ও মেরামত', 'name_en' => 'Road Patching & Repair'],
            ['slug' => 'masonry', 'name_bn' => 'রাজমিস্ত্রির কাজ (স্ল্যাব ও ফুটপাত)', 'name_en' => 'Masonry & Slab Repair'],
            ['slug' => 'driving', 'name_bn' => 'পৌর গাড়ি ও ট্রাক চালনা', 'name_en' => 'Municipal Vehicle Driving'],
            ['slug' => 'heavy_equipment', 'name_bn' => 'ভারী যন্ত্রপাতি চালনা (জেসিবি/এক্সকাভেটর)', 'name_en' => 'Heavy Equipment Operation'],
        ];

        foreach ($skills as $s) {
            $stmt = $pdo->prepare("SELECT id FROM skills WHERE slug = ?");
            $stmt->execute([$s['slug']]);
            if (!$stmt->fetch()) {
                $ins = $pdo->prepare("INSERT INTO skills (slug, name_bn, name_en, is_active) VALUES (?, ?, ?, 1)");
                $ins->execute([$s['slug'], $s['name_bn'], $s['name_en']]);
            }
        }
    }

    private static function seedCoreSettings(PDO $pdo): void
    {
        $settings = [
            ['key_name' => 'app_name_bn', 'value_text' => 'আমার ময়মনসিংহ', 'value_type' => 'string', 'is_public' => 1],
            ['key_name' => 'app_name_en', 'value_text' => 'My Mymensingh', 'value_type' => 'string', 'is_public' => 1],
            ['key_name' => 'mcc_official_phone', 'value_text' => '+8809166666', 'value_type' => 'string', 'is_public' => 1],
            ['key_name' => 'mcc_official_email', 'value_text' => 'info@mcc.gov.bd', 'value_type' => 'string', 'is_public' => 1],
            ['key_name' => 'default_sla_hours_p1', 'value_text' => '8', 'value_type' => 'integer', 'is_public' => 0],
            ['key_name' => 'default_sla_hours_p2', 'value_text' => '24', 'value_type' => 'integer', 'is_public' => 0],
            ['key_name' => 'default_sla_hours_p3', 'value_text' => '48', 'value_type' => 'integer', 'is_public' => 0],
            ['key_name' => 'default_sla_hours_p4', 'value_text' => '120', 'value_type' => 'integer', 'is_public' => 0],
        ];

        foreach ($settings as $setting) {
            $stmt = $pdo->prepare("SELECT key_name FROM settings WHERE key_name = ?");
            $stmt->execute([$setting['key_name']]);
            if (!$stmt->fetch()) {
                $ins = $pdo->prepare("INSERT INTO settings (key_name, value_text, value_type, is_public) VALUES (?, ?, ?, ?)");
                $ins->execute([$setting['key_name'], $setting['value_text'], $setting['value_type'], $setting['is_public']]);
            }
        }
    }
}
