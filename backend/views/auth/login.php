<?php
// Canonical 21 roles definition for testing environment
$canonicalRoleList = [
    [
        'category' => 'নগর নেতৃত্ব ও শীর্ষ প্রশাসন',
        'category_en' => 'Executive Leadership',
        'roles' => [
            ['slug' => 'mayor', 'name_bn' => 'মেয়র', 'name_en' => 'Mayor', 'desig_bn' => 'মাননীয় সিটি মেয়র', 'desig_en' => 'City Mayor', 'email' => 'demo.mayor@demo.local', 'phone' => '01711000003', 'scope' => 'সমগ্র ময়মনসিংহ সিটি', 'icon' => '👑', 'badge_class' => 'bg-danger text-white'],
            ['slug' => 'administrator', 'name_bn' => 'প্রশাসক', 'name_en' => 'Administrator', 'desig_bn' => 'সিটি প্রশাসক', 'desig_en' => 'City Administrator', 'email' => 'demo.administrator@demo.local', 'phone' => '01711000004', 'scope' => 'সিটি প্রশাসন ও নীতিনির্ধারণ', 'icon' => '🏛️', 'badge_class' => 'bg-primary text-white'],
            ['slug' => 'ceo', 'name_bn' => 'প্রধান নির্বাহী কর্মকর্তা (সিইও)', 'name_en' => 'Chief Executive Officer', 'desig_bn' => 'প্রধান নির্বাহী কর্মকর্তা', 'desig_en' => 'Chief Executive Officer', 'email' => 'demo.ceo@demo.local', 'phone' => '01711000005', 'scope' => 'প্রশাসনিক তদারকি ও কৈফিয়ত', 'icon' => '🏢', 'badge_class' => 'bg-dark text-white'],
            ['slug' => 'platform_super_admin', 'name_bn' => 'প্ল্যাটফর্ম সুপার অ্যাডমিন', 'name_en' => 'Platform Super Admin', 'desig_bn' => 'প্ল্যাটফর্ম প্রশাসক', 'desig_en' => 'Platform Administrator', 'email' => 'demo.platform_super_admin@demo.local', 'phone' => '01711000021', 'scope' => 'সিস্টেম কনফিগ ও ইউজার কন্ট্রোল', 'icon' => '🛡️', 'badge_class' => 'bg-info text-dark'],
            ['slug' => 'technical_super_admin', 'name_bn' => 'টেকনিক্যাল সুপার অ্যাডমিন', 'name_en' => 'Technical Super Admin', 'desig_bn' => 'লিড সিস্টেম ইঞ্জিনিয়ার', 'desig_en' => 'Lead System Engineer', 'email' => 'demo.technical_super_admin@demo.local', 'phone' => '01711000000', 'scope' => 'সার্ভার হেলথ ও ডাটাবেস ব্যাকআপ', 'icon' => '⚙️', 'badge_class' => 'bg-secondary text-white'],
        ]
    ],
    [
        'category' => 'জনপ্রতিনিধি ও ওয়ার্ড প্রশাসন',
        'category_en' => 'Ward & Representatives',
        'roles' => [
            ['slug' => 'general_councillor', 'name_bn' => 'সাধারণ ওয়ার্ড কাউন্সিলর', 'name_en' => 'General Councillor', 'desig_bn' => 'কাউন্সিলর (ওয়ার্ড ১)', 'desig_en' => 'Ward Councillor (Ward 1)', 'email' => 'demo.general_councillor@demo.local', 'phone' => '01711000006', 'scope' => 'ওয়ার্ড ১ (গাঙ্গিনার পাড়)', 'icon' => '🗣️', 'badge_class' => 'bg-success text-white'],
            ['slug' => 'reserved_women_councillor', 'name_bn' => 'সংরক্ষিত নারী কাউন্সিলর', 'name_en' => 'Reserved Councillor', 'desig_bn' => 'কাউন্সিলর (সংরক্ষিত ১-৩)', 'desig_en' => 'Reserved Women Councillor', 'email' => 'demo.reserved_women_councillor@demo.local', 'phone' => '01711000007', 'scope' => 'ওয়ার্ড ১, ২ ও ৩ ক্লাস্টার', 'icon' => '👩‍💼', 'badge_class' => 'bg-warning text-dark'],
            ['slug' => 'responsible_officer', 'name_bn' => 'দায়িত্বপ্রাপ্ত কর্মকর্তা', 'name_en' => 'Responsible Officer', 'desig_bn' => 'ওয়ার্ড ২ তদারকি কর্মকর্তা', 'desig_en' => 'Responsible Officer (Ward 2)', 'email' => 'demo.responsible_officer@demo.local', 'phone' => '01711000008', 'scope' => 'ওয়ার্ড ২ প্রশাসনিক দায়িত্ব', 'icon' => '📋', 'badge_class' => 'bg-primary-subtle text-primary-emphasis'],
            ['slug' => 'ward_officer', 'name_bn' => 'ওয়ার্ড সচিব / কর্মকর্তা', 'name_en' => 'Ward Officer', 'desig_bn' => 'ওয়ার্ড সচিব (ওয়ার্ড ১)', 'desig_en' => 'Ward Secretary (Ward 1)', 'email' => 'demo.ward_officer@demo.local', 'phone' => '01711000012', 'scope' => 'ওয়ার্ড ১ সচিবালয়', 'icon' => '📝', 'badge_class' => 'bg-info-subtle text-info-emphasis'],
        ]
    ],
    [
        'category' => 'বিভাগীয় ও আঞ্চলিক প্রশাসন',
        'category_en' => 'Department & Zonal',
        'roles' => [
            ['slug' => 'department_head', 'name_bn' => 'বিভাগীয় প্রধান (বর্জ্য)', 'name_en' => 'Department Head', 'desig_bn' => 'প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তা', 'desig_en' => 'Chief Waste Management Officer', 'email' => 'demo.department_head@demo.local', 'phone' => '01711000009', 'scope' => 'বর্জ্য ব্যবস্থাপনা বিভাগ', 'icon' => '🚛', 'badge_class' => 'bg-primary text-white'],
            ['slug' => 'department_officer', 'name_bn' => 'বিভাগীয় কর্মকর্তা', 'name_en' => 'Department Officer', 'desig_bn' => 'সহকারী বর্জ্য ব্যবস্থাপনা কর্মকর্তা', 'desig_en' => 'Assistant Waste Officer', 'email' => 'demo.department_officer@demo.local', 'phone' => '01711000010', 'scope' => 'বর্জ্য বিভাগ স্পট-চেক অডিট', 'icon' => '🛠️', 'badge_class' => 'bg-secondary text-white'],
            ['slug' => 'zone_officer', 'name_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা', 'name_en' => 'Zone Officer', 'desig_bn' => 'আঞ্চলিক কর্মকর্তা (অঞ্চল ১)', 'desig_en' => 'Zonal Executive Officer (Zone 1)', 'email' => 'demo.zone_officer@demo.local', 'phone' => '01711000011', 'scope' => 'অঞ্চল ১ (১০টি ওয়ার্ড)', 'icon' => '🌐', 'badge_class' => 'bg-dark text-white'],
        ]
    ],
    [
        'category' => 'মাঠপর্যায় ও তদারকি',
        'category_en' => 'Field Operations',
        'roles' => [
            ['slug' => 'supervisor', 'name_bn' => 'ওয়ার্ড সুপারভাইজার', 'name_en' => 'Ward Supervisor', 'desig_bn' => 'পরিচ্ছন্নতা পরিদর্শক (ওয়ার্ড ১)', 'desig_en' => 'Sanitation Inspector (Ward 1)', 'email' => 'demo.supervisor@demo.local', 'phone' => '01711000013', 'scope' => 'ওয়ার্ড ১ মাঠ তদারকি', 'icon' => '👷', 'badge_class' => 'bg-success text-white'],
            ['slug' => 'team_leader', 'name_bn' => 'মাঠ দলনেতা (টিম লিডার)', 'name_en' => 'Team Leader', 'desig_bn' => 'মাঠ দলনেতা (ওয়ার্ড ১)', 'desig_en' => 'Field Team Leader (Ward 1)', 'email' => 'demo.team_leader@demo.local', 'phone' => '01711000014', 'scope' => 'ওয়ার্ড ১ রাস্তা ও ড্রেন স্কোয়াড', 'icon' => '🦺', 'badge_class' => 'bg-warning text-dark'],
            ['slug' => 'field_worker', 'name_bn' => 'পরিচ্ছন্নতাকর্মী (ম্যানুয়াল কর্মী — সফটওয়্যার লগইন নেই)', 'name_en' => 'Sanitation Cleaner (Manual - No Software Login)', 'desig_bn' => 'সড়ক পরিচ্ছন্নতাকর্মী', 'desig_en' => 'Street Cleaner', 'email' => 'demo.field_worker@demo.local', 'phone' => '০১৭-ম্যানুয়াল', 'scope' => 'মাঠের কায়িক কর্মী (দলনেতা ও সুপারভাইজার দ্বারা পরিচালিত)', 'icon' => '🧹', 'badge_class' => 'bg-secondary text-white', 'is_manual' => true],
        ]
    ],
    [
        'category' => 'সমন্বয়, কল সেন্টার ও নিরীক্ষা',
        'category_en' => 'Intake, Control & Audit',
        'roles' => [
            ['slug' => 'call_center_operator', 'name_bn' => 'কল সেন্টার অপারেটর', 'name_en' => 'Call Center Operator', 'desig_bn' => '৩৩৩ ইনটেক অপারেটর', 'desig_en' => '333 Intake Operator', 'email' => 'demo.call_center_operator@demo.local', 'phone' => '01711000016', 'scope' => '৩৩৩ হেল্পলাইন দ্রুত ইনটেক', 'icon' => '📞', 'badge_class' => 'bg-danger-subtle text-danger-emphasis'],
            ['slug' => 'control_room_officer', 'name_bn' => 'কন্ট্রোল রুম কর্মকর্তা', 'name_en' => 'Control Room Officer', 'desig_bn' => 'কন্ট্রোল রুম সমন্বয়ক', 'desig_en' => 'Control Room Coordinator', 'email' => 'demo.control_room_officer@demo.local', 'phone' => '01711000017', 'scope' => 'জরুরি ট্রায়াজ ও রি-রাউটিং', 'icon' => '🖥️', 'badge_class' => 'bg-secondary-subtle text-secondary-emphasis'],
            ['slug' => 'public_info_officer', 'name_bn' => 'জনসংযোগ কর্মকর্তা (PRO)', 'name_en' => 'Public Info Officer', 'desig_bn' => 'জনসংযোগ কর্মকর্তা', 'desig_en' => 'Public Relations Officer', 'email' => 'demo.public_info_officer@demo.local', 'phone' => '01711000018', 'scope' => 'নাগরিক নোটিশ ও গণযোগাযোগ', 'icon' => '📢', 'badge_class' => 'bg-info text-white'],
            ['slug' => 'data_monitoring_officer', 'name_bn' => 'ডাটা ও মনিটরিং কর্মকর্তা', 'name_en' => 'Data & Monitoring Officer', 'desig_bn' => 'আইটি ও পরিসংখ্যান কর্মকর্তা', 'desig_en' => 'IT & Statistics Officer', 'email' => 'demo.data_monitoring_officer@demo.local', 'phone' => '01711000019', 'scope' => 'KPI ও পারফরম্যান্স এনালাইসিস', 'icon' => '📊', 'badge_class' => 'bg-dark-subtle text-dark-emphasis'],
            ['slug' => 'auditor', 'name_bn' => 'অভ্যন্তরীণ নিরীক্ষক (অডিটর)', 'name_en' => 'Internal Auditor', 'desig_bn' => 'অভ্যন্তরীণ নিরীক্ষক', 'desig_en' => 'Internal Auditor', 'email' => 'demo.auditor@demo.local', 'phone' => '01711000020', 'scope' => 'সমাধান অডিট ও স্বচ্ছতা', 'icon' => '🔍', 'badge_class' => 'bg-warning text-dark'],
            ['slug' => 'public_viewer', 'name_bn' => 'পাবলিক ভিউয়ার (পর্যবেক্ষক)', 'name_en' => 'Public Viewer', 'desig_bn' => 'সাধারণ পর্যবেক্ষক', 'desig_en' => 'Observer', 'email' => 'demo.public_viewer@demo.local', 'phone' => '01711000022', 'scope' => 'উন্মুক্ত নাগরিক পরিসংখ্যান', 'icon' => '👁️', 'badge_class' => 'bg-light text-dark border'],
        ]
    ],
];
?>
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">
            <div class="card border shadow-sm rounded-4 p-3 p-sm-4 bg-white">
                <div class="text-center mb-4">
                    <div class="brand-badge mx-auto mb-2">
                        <i class="bi bi-person-check fs-4"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">
                        <?= ($locale ?? 'bn') === 'bn' ? 'লগইন / প্রবেশ' : 'Sign In' ?>
                    </h3>
                    <p class="text-muted small mb-0">
                        <?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশন সেবা প্ল্যাটফর্ম' : 'Mymensingh City Corporation Service Platform' ?>
                    </p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?= e(__('auth.' . $error, [], null) !== 'auth.' . $error ? __('auth.' . $error) : $error) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <div><?= e($success) ?></div>
                    </div>
                <?php endif; ?>

                <!-- Tab Navigation -->
                <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-pill" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold py-2" id="tab-btn-citizen" type="button" onclick="switchLoginTab('citizen')">
                            <i class="bi bi-person me-1"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক' : 'Citizen' ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2 text-muted" id="tab-btn-staff" type="button" onclick="switchLoginTab('staff')">
                            <i class="bi bi-shield-lock me-1"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'কর্মকর্তা ও কর্মচারী' : 'Staff / Officer' ?>
                        </button>
                    </li>
                </ul>

                <!-- 1. Citizen OTP Login Form Container -->
                <div id="citizen-login-container">
                    <?php if (\AmarMayor\Support\Config::get('app.env') !== 'production'): ?>
                        <!-- Test Citizen Quick Fill for Testing -->
                        <div class="card border border-success-subtle bg-success-subtle bg-opacity-10 rounded-3 p-2 p-sm-3 mb-3 text-dark small shadow-sm">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-2">
                                <span class="fw-bold text-success-emphasis">
                                    <i class="bi bi-person-check-fill me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'টেস্ট নাগরিক দ্রুত নির্বাচন:' : 'Quick Demo Citizen Login:' ?>
                                </span>
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'ওটিপি মোড' : 'OTP Mode' ?>
                                </span>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-white bg-white border border-success-subtle text-success-emphasis fw-semibold shadow-xs" onclick="quickFillCitizen('01711000001')">
                                    👤 <?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক ক (০১৭১১-০০০০০১)' : 'Citizen A (01711000001)' ?>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">ওয়ার্ড ১ (গাঙ্গিনার পাড়)</small>
                                </button>
                                <button type="button" class="btn btn-sm btn-white bg-white border border-success-subtle text-success-emphasis fw-semibold shadow-xs" onclick="quickFillCitizen('01711000002')">
                                    👤 <?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক খ (০১৭১১-০০০০০২)' : 'Citizen B (01711000002)' ?>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">ওয়ার্ড ৫ (সানকিপাড়া)</small>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-3 border-0 bg-info-subtle text-info-emphasis">
                        <i class="bi bi-info-circle me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'আপনার মোবাইল নম্বরে একটি যাচাইকরণ কোড পাঠানো হবে।' : 'A verification code will be sent to your mobile number.' ?>
                    </div>

                    <?php if (($step ?? 'request') === 'verify'): ?>
                        <?php if (!empty($demo_otp)): ?>
                            <div class="card border border-warning bg-warning-subtle rounded-3 p-3 mb-3 text-dark shadow-sm">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <div class="fw-bold text-dark mb-1">
                                            <i class="bi bi-shield-lock-fill text-warning me-1"></i>
                                            <?= ($locale ?? 'bn') === 'bn' ? 'পরীক্ষামূলক ওটিপি কোড:' : 'Demo OTP Code:' ?>
                                            <span class="badge bg-warning text-dark font-monospace fs-5 px-2 py-1"><?= e($demo_otp) ?></span>
                                        </div>
                                        <small class="text-muted d-block">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'বাস্তবে এই কোডটি নাগরিকের মোবাইলে এসএমএস যাবে।' : 'In production, this code is sent via SMS.' ?>
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-dark px-3 py-1 fw-bold rounded-pill" onclick="document.getElementById('otp_code').value='<?= e($demo_otp) ?>'">
                                        <i class="bi bi-arrow-down-circle me-1"></i> <?= ($locale ?? 'bn') === 'bn' ? 'কোড বসান' : 'Auto-fill' ?>
                                    </button>
                                </div>
                            </div>
                        <?php elseif (\AmarMayor\Support\Config::get('app.env') !== 'production'): ?>
                            <div class="alert alert-warning py-2 px-3 small rounded-3 mb-3 border-0 bg-warning-subtle text-warning-emphasis">
                                <i class="bi bi-tools me-1"></i>
                                <strong><?= ($locale ?? 'bn') === 'bn' ? 'পরীক্ষামূলক মোড:' : 'Testing Mode:' ?></strong>
                                <?= ($locale ?? 'bn') === 'bn' ? 'SMS গেটওয়ে সংযুক্ত নয়। কোড দেখতে পারেন' : 'SMS gateway is not connected. Code in' ?>
                                <a href="/dev/otp-inbox" target="_blank" class="fw-bold text-decoration-underline text-warning-emphasis">Developer OTP Inbox</a>.
                            </div>
                        <?php endif; ?>

                        <!-- Step 2: Verify OTP -->
                        <form action="/auth/otp/verify" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="phone" value="<?= e($phone ?? '') ?>">

                            <div class="mb-3">
                                <label for="otp_code" class="form-label fw-semibold small text-muted">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'যাচাইকরণ কোড (৬ সংখ্যা)' : 'Verification Code (6 Digits)' ?>
                                </label>
                                <input type="text" id="otp_code" name="otp_code" value="" autocomplete="off" required autofocus maxlength="6" pattern="[0-9]{6}"
                                       placeholder="123456"
                                       class="form-control form-control-lg text-center fs-4 letter-spacing-2">
                            </div>

                            <button type="submit" class="btn btn-civic-primary btn-lg w-100 py-2 mb-2">
                                <?= ($locale ?? 'bn') === 'bn' ? 'যাচাই করে প্রবেশ করুন' : 'Verify & Enter' ?>
                            </button>

                            <div class="text-center mt-2">
                                <a href="/login" class="text-muted small text-decoration-none">
                                    &larr; <?= ($locale ?? 'bn') === 'bn' ? 'নম্বর পরিবর্তন করুন' : 'Change Phone Number' ?>
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <!-- Step 1: Request OTP -->
                        <form action="/auth/otp/request" method="POST">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-semibold small text-muted">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'মোবাইল নম্বর' : 'Mobile Number' ?>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted fw-bold">+88</span>
                                    <input type="tel" id="phone" name="phone" required autofocus
                                           placeholder="017XXXXXXXX"
                                           class="form-control form-control-lg">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-civic-primary btn-lg w-100 py-2">
                                <?= ($locale ?? 'bn') === 'bn' ? 'ওটিপি পাঠান' : 'Send OTP' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- 2. Staff Password Login Form Container -->
                <div id="staff-login-container" class="d-none">
                    <?php if (\AmarMayor\Support\Config::get('app.env') !== 'production'): ?>
                        <!-- Comprehensive Demo Accounts Hub (All 21 Roles + 33 Supervisors) -->
                        <div class="card border border-primary-subtle bg-light rounded-3 p-3 mb-3 text-dark small shadow-sm">
                            <div class="fw-bold mb-2 text-dark d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span>
                                    <i class="bi bi-person-badge-fill text-primary me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'ডেমো অ্যাকাউন্ট দ্রুত নির্বাচন:' : 'Quick Demo Logins:' ?>
                                </span>
                                <span class="badge bg-success font-monospace">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'পাসওয়ার্ড: Demo@12345' : 'Password: Demo@12345' ?>
                                </span>
                            </div>

                            <!-- Primary Quick-Access Role Pills -->
                            <div class="d-flex gap-1 flex-wrap mb-2">
                                <button type="button" class="btn btn-xs btn-primary fw-semibold px-2 py-1 shadow-xs" onclick="quickFillStaff('demo.mayor@demo.local', 'Demo@12345')">
                                    👑 <?= ($locale ?? 'bn') === 'bn' ? 'মেয়র' : 'Mayor' ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary fw-semibold px-2 py-1 shadow-xs bg-white" onclick="quickFillStaff('demo.administrator@demo.local', 'Demo@12345')">
                                    🏛️ <?= ($locale ?? 'bn') === 'bn' ? 'প্রশাসক' : 'Admin' ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-dark fw-semibold px-2 py-1 shadow-xs" onclick="quickFillStaff('demo.ceo@demo.local', 'Demo@12345')">
                                    🏢 <?= ($locale ?? 'bn') === 'bn' ? 'সিইও' : 'CEO' ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-primary fw-semibold px-2 py-1 shadow-xs bg-white" onclick="quickFillStaff('demo.department_head@demo.local', 'Demo@12345')">
                                    🚛 <?= ($locale ?? 'bn') === 'bn' ? 'বিভাগীয় প্রধান' : 'Dept Head' ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-success fw-semibold px-2 py-1 shadow-xs" onclick="quickFillStaff('demo.supervisor@demo.local', 'Demo@12345')">
                                    👷 <?= ($locale ?? 'bn') === 'bn' ? 'সুপারভাইজার' : 'Supervisor' ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-warning text-dark fw-semibold px-2 py-1 shadow-xs" onclick="quickFillStaff('demo.team_leader@demo.local', 'Demo@12345')">
                                    🦺 <?= ($locale ?? 'bn') === 'bn' ? 'দলনেতা (১)' : 'Team Leader (1)' ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-success fw-semibold px-2 py-1 shadow-xs bg-white" onclick="quickFillStaff('demo.general_councillor@demo.local', 'Demo@12345')">
                                    🗣️ <?= ($locale ?? 'bn') === 'bn' ? 'কাউন্সিলর (১)' : 'Councillor' ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-warning text-dark fw-semibold px-2 py-1 shadow-xs bg-white" onclick="quickFillStaff('demo.reserved_women_councillor@demo.local', 'Demo@12345')">
                                    👩‍💼 <?= ($locale ?? 'bn') === 'bn' ? 'নারী কাউন্সিলর' : 'Res. Councillor' ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-danger fw-semibold px-2 py-1 shadow-xs bg-white" onclick="quickFillStaff('demo.call_center_operator@demo.local', 'Demo@12345')">
                                    📞 <?= ($locale ?? 'bn') === 'bn' ? 'কল সেন্টার' : 'Call Center' ?>
                                </button>
                            </div>

                            <!-- Dropdown 1: All 21 Canonical Roles with Groups -->
                            <div class="mt-2 pt-2 border-top">
                                <label for="roleSelector" class="form-label text-dark fw-bold mb-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-diagram-3-fill text-primary me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'সকল ২১টি কর্মকর্তা ও কর্মচারী পদবী (নির্বাচন করুন):' : 'All 21 System Roles (Select to Fill):' ?>
                                </label>
                                <div class="input-group input-group-sm">
                                    <select id="roleSelector" class="form-select border-primary-subtle" onchange="if(this.value) quickFillStaff(this.value, 'Demo@12345')">
                                        <option value=""><?= ($locale ?? 'bn') === 'bn' ? '-- ২১টি ক্যানোনিকাল রোলের যেকোনোটি বেছে নিন --' : '-- Choose Any of 21 Canonical Roles --' ?></option>
                                        <?php foreach ($canonicalRoleList as $group): ?>
                                            <optgroup label="<?= ($locale ?? 'bn') === 'bn' ? e($group['category']) : e($group['category_en']) ?>">
                                                <?php foreach ($group['roles'] as $r): ?>
                                                    <option value="<?= e($r['email']) ?>">
                                                        <?= $r['icon'] ?> <?= ($locale ?? 'bn') === 'bn' ? e($r['name_bn']) . ' (' . e($r['desig_bn']) . ')' : e($r['name_en']) . ' (' . e($r['desig_en']) . ')' ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-primary" onclick="autoLoginSelected('roleSelector')">
                                        <?= ($locale ?? 'bn') === 'bn' ? 'প্রবেশ &rarr;' : 'Login &rarr;' ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Dropdown 2: 33 Ward Supervisors -->
                            <div class="mt-2">
                                <label for="wardSupervisorSelector" class="form-label text-dark fw-bold mb-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-geo-alt-fill text-success me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? '৩৩টি ওয়ার্ডের সুপারভাইজার (ওয়ার্ড ১-৩৩):' : '33 Ward Supervisors (Wards 1-33):' ?>
                                </label>
                                <div class="input-group input-group-sm">
                                    <select id="wardSupervisorSelector" class="form-select border-success-subtle" onchange="if(this.value) quickFillStaff(this.value, 'Demo@12345')">
                                        <option value=""><?= ($locale ?? 'bn') === 'bn' ? 'যেকোনো ওয়ার্ড বেছে নিন (১-৩৩)...' : 'Select Ward Supervisor (1-33)...' ?></option>
                                        <?php for ($w = 1; $w <= 33; $w++): ?>
                                            <option value="supervisor.ward<?= $w ?>@demo.local">
                                                👷 <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number((string)$w) . ' পরিচ্ছন্নতা পরিদর্শক' : 'Ward ' . $w . ' Sanitation Supervisor' ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <button type="button" class="btn btn-success" onclick="autoLoginSelected('wardSupervisorSelector')">
                                        <?= ($locale ?? 'bn') === 'bn' ? 'প্রবেশ &rarr;' : 'Login &rarr;' ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Dropdown 3: 33 Ward Field Team Leaders -->
                            <div class="mt-2">
                                <label for="wardTeamLeaderSelector" class="form-label text-dark fw-bold mb-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-people-fill text-warning me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? '৩৩টি ওয়ার্ডের মাঠ দলনেতা (ওয়ার্ড ১-৩৩):' : '33 Ward Field Team Leaders (Wards 1-33):' ?>
                                </label>
                                <div class="input-group input-group-sm">
                                    <select id="wardTeamLeaderSelector" class="form-select border-warning-subtle" onchange="if(this.value) quickFillStaff(this.value, 'Demo@12345')">
                                        <option value=""><?= ($locale ?? 'bn') === 'bn' ? 'যেকোনো ওয়ার্ড বেছে নিন (১-৩৩)...' : 'Select Ward Team Leader (1-33)...' ?></option>
                                        <?php for ($w = 1; $w <= 33; $w++): ?>
                                            <option value="team_leader.ward<?= $w ?>@demo.local">
                                                🦺 <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number((string)$w) . ' মাঠ দলনেতা' : 'Ward ' . $w . ' Field Team Leader' ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <button type="button" class="btn btn-warning text-dark fw-bold" onclick="autoLoginSelected('wardTeamLeaderSelector')">
                                        <?= ($locale ?? 'bn') === 'bn' ? 'প্রবেশ &rarr;' : 'Login &rarr;' ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Modal Trigger Button -->
                            <div class="mt-2 pt-2 border-top text-center">
                                <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#allRolesModal">
                                    <i class="bi bi-grid-3x3-gap-fill me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'সকল ২১টি পদের পূর্ণ ডিরেক্টরি ও ১-ক্লিক লগইন তালিকা' : 'View Full 21-Role Matrix & 1-Click Logins' ?> &rarr;
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form id="staff-login-form" action="/login/password" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="identifier" class="form-label fw-semibold small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'ইমেইল বা মোবাইল নম্বর' : 'Email or Phone' ?>
                            </label>
                            <input type="text" id="identifier" name="identifier" required
                                   placeholder="staff@mymensinghcity.gov.bd / 017XXXXXXXX"
                                   class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'পাসওয়ার্ড' : 'Password' ?>
                            </label>
                            <input type="password" id="password" name="password" required
                                   placeholder="••••••••"
                                   class="form-control">
                        </div>

                        <button type="submit" class="btn btn-civic-primary btn-lg w-100 py-2">
                            <?= ($locale ?? 'bn') === 'bn' ? 'লগইন করুন' : 'Sign In' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Comprehensive 21-Role Testing & Demo Logins Directory -->
<div class="modal fade" id="allRolesModal" tabindex="-1" aria-labelledby="allRolesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow border-0">
            <div class="modal-header bg-light border-0 py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="allRolesModalLabel">
                        <i class="bi bi-person-badge-fill text-primary fs-4"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশন — ২১টি কর্মকর্তা ও কর্মচারী ভূমিকা ডিরেক্টরি' : 'Mymensingh City Corporation — All 21 System Roles Directory' ?>
                    </h5>
                    <p class="text-muted small mb-0">
                        <?= ($locale ?? 'bn') === 'bn' ? 'টেস্টিং ও প্রদর্শনী মোড: যেকোনো রোলে প্রবেশের জন্য সরাসরি "লগইন করুন" বোতামে ক্লিক করুন। ডিফল্ট পাসওয়ার্ড:' : 'Testing Mode: Click "Login" on any row for instant 1-click access. Default Password:' ?>
                        <span class="badge bg-success font-monospace">Demo@12345</span>
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-2 px-3"><?= ($locale ?? 'bn') === 'bn' ? 'পদবী / ভূমিকা' : 'Role & Category' ?></th>
                                <th class="py-2"><?= ($locale ?? 'bn') === 'bn' ? 'অফিসিয়াল দায়িত্ব' : 'Designation' ?></th>
                                <th class="py-2"><?= ($locale ?? 'bn') === 'bn' ? 'এলাকা / কর্মপরিধি' : 'Scope / Coverage' ?></th>
                                <th class="py-2"><?= ($locale ?? 'bn') === 'bn' ? 'লগইন আইডি' : 'Login Email' ?></th>
                                <th class="text-end py-2 pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'পদক্ষেপ' : 'Action' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $roleIdx = 1; foreach ($canonicalRoleList as $group): ?>
                                <tr class="table-secondary bg-opacity-50">
                                    <td colspan="5" class="py-2 px-3 fw-bold text-dark">
                                        <i class="bi bi-folder2-open text-primary me-1"></i>
                                        <?= ($locale ?? 'bn') === 'bn' ? e($group['category']) : e($group['category_en']) ?>
                                    </td>
                                </tr>
                                <?php foreach ($group['roles'] as $r): ?>
                                    <tr>
                                        <td class="px-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fs-5"><?= $r['icon'] ?></span>
                                                <div>
                                                    <span class="fw-bold text-dark d-block">
                                                        <?= ($locale ?? 'bn') === 'bn' ? to_bn_number((string)$roleIdx) . '. ' . e($r['name_bn']) : $roleIdx . '. ' . e($r['name_en']) ?>
                                                    </span>
                                                    <span class="badge <?= $r['badge_class'] ?> rounded-pill" style="font-size: 0.7rem;">
                                                        <?= e($r['slug']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-dark d-block"><?= ($locale ?? 'bn') === 'bn' ? e($r['desig_bn']) : e($r['desig_en']) ?></strong>
                                            <small class="text-muted"><?= e($r['phone']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= ($locale ?? 'bn') === 'bn' ? e($r['scope']) : e($r['scope']) ?>
                                            </span>
                                        </td>
                                        <td class="font-monospace text-dark small">
                                            <?= e($r['email']) ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <?php if (!empty($r['is_manual'])): ?>
                                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 small">
                                                    <i class="bi bi-person-x me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'লগইন নেই (মাঠ সম্পদ)' : 'No Login (Field Asset)' ?>
                                                </span>
                                            <?php else: ?>
                                                <form action="/login/password" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="identifier" value="<?= e($r['email']) ?>">
                                                    <input type="hidden" name="password" value="Demo@12345">
                                                    <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3 shadow-xs">
                                                        <?= ($locale ?? 'bn') === 'bn' ? '১-ক্লিকে লগইন &rarr;' : 'Login &rarr;' ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php $roleIdx++; endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2 px-4 d-flex justify-content-between">
                <a href="/dev/testing-access" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'সম্পূর্ণ ডেভেলপার অ্যাক্সেস হাব' : 'Full Developer Testing Hub' ?>
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <?= ($locale ?? 'bn') === 'bn' ? 'বন্ধ করুন' : 'Close' ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function switchLoginTab(tab) {
    const citizenContainer = document.getElementById('citizen-login-container');
    const staffContainer = document.getElementById('staff-login-container');
    const citizenBtn = document.getElementById('tab-btn-citizen');
    const staffBtn = document.getElementById('tab-btn-staff');

    if (tab === 'staff' || tab === 'password') {
        citizenContainer.classList.add('d-none');
        staffContainer.classList.remove('d-none');
        staffBtn.classList.add('active', 'text-white');
        staffBtn.classList.remove('text-muted');
        citizenBtn.classList.remove('active');
        citizenBtn.classList.add('text-muted');
    } else {
        staffContainer.classList.add('d-none');
        citizenContainer.classList.remove('d-none');
        citizenBtn.classList.add('active');
        citizenBtn.classList.remove('text-muted');
        staffBtn.classList.remove('active', 'text-white');
        staffBtn.classList.add('text-muted');
    }
}

function quickFillStaff(email, pass) {
    document.getElementById('identifier').value = email;
    document.getElementById('password').value = pass;
}

function quickFillCitizen(phone) {
    document.getElementById('phone').value = phone;
    document.getElementById('phone').focus();
}

function autoLoginSelected(selectId) {
    const select = document.getElementById(selectId);
    if (!select || !select.value) {
        alert('অনুগ্রহ করে আগে একটি পদবী নির্বাচন করুন');
        return;
    }
    quickFillStaff(select.value, 'Demo@12345');
    const form = document.getElementById('staff-login-form');
    if (form) {
        form.submit();
    }
}

// Auto-switch to staff tab if specified in URL query
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('tab') === 'staff' || urlParams.get('tab') === 'password') {
    switchLoginTab('staff');
}
</script>
