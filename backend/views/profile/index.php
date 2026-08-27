<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <!-- Page Title -->
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <div>
                    <h3 class="fw-bold text-dark mb-1">
                        <i class="bi bi-person-bounding-box text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'আমার প্রোফাইল ও অ্যাকাউন্ট' : 'My Account & Profile' ?>
                    </h3>
                    <p class="text-muted small mb-0">
                        <?= ($locale ?? 'bn') === 'bn' ? 'ব্যক্তিগত তথ্য, এলাকা ও নোটিফিকেশন পছন্দসমূহ পরিচালনা করুন।' : 'Manage your personal details, home locality, and notification preferences.' ?>
                    </p>
                </div>
                <a href="/my-complaints" class="btn btn-outline-secondary btn-sm">
                    &larr; <?= ($locale ?? 'bn') === 'bn' ? 'আমার অভিযোগসমূহ' : 'My Complaints' ?>
                </a>
            </div>

            <!-- Feedback Alerts -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <div><?= ($locale ?? 'bn') === 'bn' ? 'প্রোফাইল সফলভাবে আপডেট করা হয়েছে।' : 'Profile updated successfully.' ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?= ($locale ?? 'bn') === 'bn' ? 'ত্রুটি: ' . e($error) : 'Error: ' . e($error) ?></div>
                </div>
            <?php endif; ?>

            <!-- Verified Identity Header Card -->
            <div class="card border shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-3" style="width:64px;height:64px;">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h5 class="fw-bold mb-0 text-dark">
                                <?= e(!empty($person['full_name_bn']) ? $person['full_name_bn'] : (!empty($person['full_name_en']) ? $person['full_name_en'] : (($locale ?? 'bn') === 'bn' ? 'সম্মানিত নাগরিক' : 'Citizen'))) ?>
                            </h5>
                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill small">
                                <i class="bi bi-patch-check-fill me-1"></i>
                                <?= ($locale ?? 'bn') === 'bn' ? 'যাচাইকৃত অ্যাকাউন্ট' : 'Verified Account' ?>
                            </span>
                        </div>
                        <p class="text-muted small mb-0 font-monospace">
                            <i class="bi bi-phone me-1"></i> <?= e($user->phone ?? 'N/A') ?> 
                            <span class="text-success small fw-semibold ms-2">(OTP Verified)</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Profile Edit Form -->
            <div class="card border shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="bi bi-pencil-square text-primary me-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'ব্যক্তিগত ও এলাকা সংক্রান্ত তথ্য' : 'Personal & Locality Information' ?>
                </h5>

                <form action="/profile" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="name_bn" class="form-label fw-semibold small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'পুরো নাম (বাংলা)' : 'Full Name (Bangla)' ?>
                            </label>
                            <input type="text" id="name_bn" name="name_bn" class="form-control"
                                   placeholder="যেমন: রফিকুল ইসলাম"
                                   value="<?= e($person['full_name_bn'] ?? '') ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="name_en" class="form-label fw-semibold small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'পুরো নাম (ইংরেজি)' : 'Full Name (English)' ?>
                            </label>
                            <input type="text" id="name_en" name="name_en" class="form-control"
                                   placeholder="e.g. Rafiqul Islam"
                                   value="<?= e($person['full_name_en'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label fw-semibold small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'ইমেইল ঠিকানা (ঐচ্ছিক)' : 'Email Address (Optional)' ?>
                            </label>
                            <input type="email" id="email" name="email" class="form-control"
                                   placeholder="name@example.com"
                                   value="<?= e($user->email ?? '') ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="preferred_language" class="form-label fw-semibold small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'পছন্দের ভাষা' : 'Preferred Language' ?>
                            </label>
                            <select id="preferred_language" name="preferred_language" class="form-select">
                                <option value="bn" <?= ($user->preferredLanguage ?? 'bn') === 'bn' ? 'selected' : '' ?>>বাংলা (Bangla)</option>
                                <option value="en" <?= ($user->preferredLanguage ?? 'bn') === 'en' ? 'selected' : '' ?>>English</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="home_ward_id" class="form-label fw-semibold small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'আমার ওয়ার্ড (বসবাসের এলাকা)' : 'Home Ward (Residence)' ?>
                            </label>
                            <select id="home_ward_id" name="home_ward_id" class="form-select">
                                <option value=""><?= ($locale ?? 'bn') === 'bn' ? '-- ওয়ার্ড নির্বাচন করুন --' : '-- Select Ward --' ?></option>
                                <?php foreach ($wards as $w): ?>
                                    <option value="<?= $w['id'] ?>" <?= ((int)($person['home_ward_id'] ?? 0) === (int)$w['id']) ? 'selected' : '' ?>>
                                        <?= ($locale ?? 'bn') === 'bn' ? e($w['name_bn']) : e($w['name_en']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="home_area" class="form-label fw-semibold small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'মহল্লা / এলাকা / সড়ক' : 'Area / Neighborhood / Street' ?>
                            </label>
                            <input type="text" id="home_area" name="home_area" class="form-control"
                                   placeholder="<?= ($locale ?? 'bn') === 'bn' ? 'যেমন: গাঙ্গিনার পাড়, বড় বাজার' : 'e.g. Ganginar Par, Bara Bazar' ?>"
                                   value="<?= e($person['home_area'] ?? '') ?>">
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="bi bi-bell text-primary me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'নোটিফিকেশন ও বার্তা গ্রহণ পছন্দ' : 'Notification Preferences' ?>
                    </h6>

                    <div class="mb-4">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="notif_sms" name="notif_sms" value="1"
                                   <?= (!isset($notifPrefs['sms']) || !empty($notifPrefs['sms'])) ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold" for="notif_sms">
                                <?= ($locale ?? 'bn') === 'bn' ? 'মোবাইলে এসএমএস (SMS) বার্তা গ্রহণ করুন' : 'Receive SMS updates on mobile' ?>
                            </label>
                            <div class="text-muted small ms-4">
                                <?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ গ্রহণ, কাজ শুরু এবং সমাধানের এসএমএস বার্তা পাবেন।' : 'Get critical updates when complaints are assigned, resolved, or verified.' ?>
                            </div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="notif_app" name="notif_app" value="1"
                                   <?= (!isset($notifPrefs['app']) || !empty($notifPrefs['app'])) ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold" for="notif_app">
                                <?= ($locale ?? 'bn') === 'bn' ? 'অনলাইন পোর্টাল নোটিফিকেশন' : 'Web portal in-app notifications' ?>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/my-complaints" class="btn btn-outline-secondary">
                            <?= ($locale ?? 'bn') === 'bn' ? 'বাতিল' : 'Cancel' ?>
                        </a>
                        <button type="submit" class="btn btn-civic-primary px-4">
                            <i class="bi bi-save me-1"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'তথ্য সংরক্ষণ করুন' : 'Save Changes' ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Staff Workforce Profile (if applicable) -->
            <?php if ($employee): ?>
                <div class="card border shadow-sm rounded-4 p-4 bg-light">
                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="bi bi-briefcase-fill text-primary me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'দাপ্তরিক ও কর্মসংস্থান তথ্য' : 'Official Workforce Posting' ?>
                    </h5>
                    <div class="row g-3 small">
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'কর্মচারী আইডি:' : 'Employee Code:' ?></span>
                            <span class="fw-bold font-monospace"><?= e($employee['employee_code']) ?></span>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'পদবী:' : 'Designation:' ?></span>
                            <span class="fw-bold"><?= ($locale ?? 'bn') === 'bn' ? e($employee['designation_bn']) : e($employee['designation_en']) ?></span>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'বিভাগ:' : 'Department:' ?></span>
                            <span class="fw-bold"><?= ($locale ?? 'bn') === 'bn' ? e($employee['dept_name_bn'] ?? 'সাধারণ') : e($employee['dept_name_en'] ?? 'General') ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
