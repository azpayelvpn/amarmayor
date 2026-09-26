<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <!-- Search Card -->
            <div class="card border shadow-sm rounded-4 p-4 bg-white mb-4">
                <h4 class="fw-bold text-dark mb-2">
                    <i class="bi bi-search text-primary me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগের অবস্থা ট্র্যাক করুন' : 'Track Complaint Status' ?>
                </h4>
                <p class="text-muted small mb-3">
                    <?= ($locale ?? 'bn') === 'bn' ? 'আপনার প্রাপ্ত ট্র্যাকিং নম্বরটি প্রবেশ করিয়ে বর্তমান অবস্থা ও কাজের অগ্রগতি দেখুন।' : 'Enter your complaint tracking number to view real-time progress.' ?>
                </p>

                <form action="/track" method="GET" class="d-flex gap-2">
                    <input type="text" name="tracking_number" value="<?= e($trackingNumber ?? '') ?>" required
                           placeholder="MCC-2608-00001"
                           class="form-control form-control-lg text-uppercase font-monospace">
                    <button type="submit" class="btn btn-civic-primary btn-lg px-4">
                        <?= ($locale ?? 'bn') === 'bn' ? 'খুঁজুন' : 'Search' ?>
                    </button>
                </form>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-warning py-3 px-4 rounded-4 shadow-sm border-0 d-flex align-items-center gap-3 mb-4">
                    <i class="bi bi-exclamation-circle-fill fs-3 text-warning"></i>
                    <div>
                        <h6 class="fw-bold mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'কোনো তথ্য পাওয়া যায়নি' : 'No Record Found' ?></h6>
                        <span class="small"><?= e($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success py-3 px-4 rounded-4 shadow-sm border-0 d-flex align-items-center gap-3 mb-4">
                    <i class="bi bi-check-circle-fill fs-3 text-success"></i>
                    <div><?= e($success) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($complaint)): ?>
                <?php
                    $citizenStatus = $complaint['citizen_status'] ?? 'received';
                    $statusMapBn = [
                        'received' => 'অভিযোগ পেয়েছি',
                        'assigned' => 'দায়িত্ব দেওয়া হয়েছে',
                        'in_progress' => 'কাজ চলছে',
                        'work_completed' => 'কাজ সম্পন্ন হয়েছে',
                        'confirmation_needed' => 'আপনার নিশ্চিতকরণ প্রয়োজন',
                        'resolved' => 'সমাধান হয়েছে',
                        'needs_more_work' => 'আবার কাজ প্রয়োজন',
                    ];
                    $statusMapEn = [
                        'received' => 'Received',
                        'assigned' => 'Assigned',
                        'in_progress' => 'In Progress',
                        'work_completed' => 'Work Completed',
                        'confirmation_needed' => 'Confirmation Needed',
                        'resolved' => 'Resolved',
                        'needs_more_work' => 'Needs More Work',
                    ];
                    $statusText = ($locale ?? 'bn') === 'bn' ? ($statusMapBn[$citizenStatus] ?? 'চলমান') : ($statusMapEn[$citizenStatus] ?? 'In Progress');
                ?>

                <!-- Complaint Details Card -->
                <div class="card border shadow-sm rounded-4 p-4 bg-white mb-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 border-bottom pb-3">
                        <div>
                            <span class="text-muted small fw-semibold d-block"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নম্বর' : 'Tracking Number' ?></span>
                            <h4 class="fw-bold text-dark mb-0"><?= e($complaint['public_complaint_number'] ?? '') ?></h4>
                        </div>
                        <div>
                            <span class="badge-status status-<?= e($citizenStatus) ?> fs-6">
                                <i class="bi bi-circle-fill small"></i>
                                <?= e($statusText) ?>
                            </span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4">
                            <span class="text-muted small d-block"><?= ($locale ?? 'bn') === 'bn' ? 'খাত / ক্যাটাগরি' : 'Category' ?></span>
                            <strong class="text-dark"><?= ($locale ?? 'bn') === 'bn' ? e($complaint['category_name_bn'] ?? '') : e($complaint['category_name_en'] ?? '') ?></strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted small d-block"><?= ($locale ?? 'bn') === 'bn' ? 'নির্দিষ্ট সমস্যা' : 'Subcategory' ?></span>
                            <strong class="text-dark"><?= ($locale ?? 'bn') === 'bn' ? e($complaint['subcategory_name_bn'] ?? '') : e($complaint['subcategory_name_en'] ?? '') ?></strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted small d-block"><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ও অঞ্চল' : 'Ward & Zone' ?></span>
                            <strong class="text-dark">
                                <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)($complaint['ward_number'] ?? '')) . ', অঞ্চল ' . to_bn_number((string)($complaint['zone_number'] ?? '')) : 'Ward ' . ($complaint['ward_number'] ?? '') . ', Zone ' . ($complaint['zone_number'] ?? '') ?>
                            </strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted small d-block"><?= ($locale ?? 'bn') === 'bn' ? 'দাখিলের তারিখ' : 'Submitted Date' ?></span>
                            <span class="text-dark"><?= format_bn_date((string)($complaint['submitted_at'] ?? 'now')) ?></span>
                        </div>
                        <div class="col-12 col-md-8">
                            <span class="text-muted small d-block"><?= ($locale ?? 'bn') === 'bn' ? 'স্থান / ঠিকানা' : 'Location / Address' ?></span>
                            <span class="text-dark"><?= e($complaint['public_safe_address'] ?? 'নির্দিষ্ট স্থান') ?></span>
                        </div>
                    </div>

                    <!-- Community Upvote / Anti-Duplicate -->
                    <div class="d-flex align-items-center justify-content-between p-3 mb-3 bg-light rounded-3 border">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                            <div>
                                <strong class="text-dark">
                                    <?= \AmarMayor\Support\Translator::toBanglaNumeral($complaint['supporters_count'] ?? 0) ?> জন
                                </strong>
                                <small class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক এই এলাকার একই সমস্যার ভুক্তভোগী' : 'citizens affected by this same issue' ?></small>
                            </div>
                        </div>
                        <form action="/complaints/<?= (int)$complaint['id'] ?>/support" method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm <?= !empty($complaint['user_supported']) ? 'btn-primary' : 'btn-outline-primary' ?> fw-semibold shadow-sm">
                                <i class="bi bi-hand-thumbs-up-fill me-1"></i>
                                <?= !empty($complaint['user_supported']) ? (($locale ?? 'bn') === 'bn' ? 'আমিও ভুক্তভোগী (সমর্থিত)' : 'Supported') : (($locale ?? 'bn') === 'bn' ? 'আমিও ভুক্তভোগী (+১)' : 'I am also affected (+1)') ?>
                            </button>
                        </form>
                    </div>

                    <?php if (!empty($complaint['description'])): ?>
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <span class="text-muted small fw-semibold d-block mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'বিবরণ' : 'Description' ?></span>
                            <p class="mb-0 text-dark small"><?= nl2br(e($complaint['description'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Before & After Resolution Visual Comparison -->
                    <?php if (!empty($complaint['before_media']) || !empty($complaint['after_media'])): ?>
                        <div class="card border-0 shadow-sm bg-white p-3 rounded-3 mb-3 border">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-camera-fill text-primary"></i>
                                <span><?= ($locale ?? 'bn') === 'bn' ? 'কাজের প্রমাণ ও চিত্র (Before & After প্রমাণ)' : 'Before & After Work Evidence' ?></span>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card h-100 border shadow-sm overflow-hidden bg-light">
                                        <div class="card-header bg-danger text-white py-1 px-3 small fw-bold d-flex align-items-center justify-content-between">
                                            <span><i class="bi bi-exclamation-triangle-fill me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'কাজের পূর্বের ছবি (সমস্যা)' : 'Before Work' ?></span>
                                            <span class="badge bg-white text-danger">BEFORE</span>
                                        </div>
                                        <div class="card-body p-2 text-center d-flex align-items-center justify-content-center" style="min-height: 180px;">
                                            <?php if (!empty($complaint['before_media']['original_file_path'])): ?>
                                                <img src="<?= e($complaint['before_media']['original_file_path']) ?>" alt="Before Work" class="img-fluid rounded" style="max-height: 220px; object-fit: cover; width: 100%;">
                                            <?php else: ?>
                                                <div class="text-muted small py-4">
                                                    <i class="bi bi-image fs-1 d-block mb-1 text-secondary opacity-50"></i>
                                                    <?= ($locale ?? 'bn') === 'bn' ? 'কোন ছবি দেওয়া হয়নি' : 'No photo attached' ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card h-100 border shadow-sm overflow-hidden bg-light">
                                        <div class="card-header bg-success text-white py-1 px-3 small fw-bold d-flex align-items-center justify-content-between">
                                            <span><i class="bi bi-check-circle-fill me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'কাজের পরের ছবি (সমাধান)' : 'After Resolution' ?></span>
                                            <span class="badge bg-white text-success">AFTER</span>
                                        </div>
                                        <div class="card-body p-2 text-center d-flex align-items-center justify-content-center" style="min-height: 180px;">
                                            <?php if (!empty($complaint['after_media']['original_file_path'])): ?>
                                                <img src="<?= e($complaint['after_media']['original_file_path']) ?>" alt="After Resolution" class="img-fluid rounded" style="max-height: 220px; object-fit: cover; width: 100%;">
                                            <?php else: ?>
                                                <div class="text-muted small py-4">
                                                    <i class="bi bi-hourglass-split fs-1 d-block mb-1 text-warning"></i>
                                                    <?= ($locale ?? 'bn') === 'bn' ? 'মাঠপর্যায়ের কাজ শেষে সমাধান ছবি যুক্ত হবে' : 'Resolution photo will appear upon completion' ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- CITIZEN CONFIRMATION & REOPEN CONTROLS (OWNER ONLY) -->
                    <?php if (!empty($complaint['is_owner']) && in_array($citizenStatus, ['confirmation_needed', 'work_completed'], true)): ?>
                        <div class="alert alert-warning border-0 rounded-3 p-3 mt-3">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="bi bi-patch-question-fill text-warning me-1"></i>
                                <?= ($locale ?? 'bn') === 'bn' ? 'মাঠপর্যায়ের কাজ শেষ হয়েছে — আপনার মতামত দিন' : 'Field Work Finished — Please confirm resolution' ?>
                            </h6>
                            <p class="small text-muted mb-3">
                                <?= ($locale ?? 'bn') === 'bn' ? 'কাজের ফলাফল সন্তোষজনক হলে সমাধান নিশ্চিত করুন, অথবা অসন্তোষজনক হলে পুনরায় কাজের নির্দেশ দিন।' : 'If you are satisfied, confirm resolution. If not, request rework.' ?>
                            </p>

                            <div class="d-flex gap-2 flex-wrap">
                                <!-- Confirm Form Button -->
                                <button type="button" class="btn btn-success fw-semibold px-3 py-2" data-bs-toggle="collapse" data-bs-target="#confirmBox">
                                    <i class="bi bi-hand-thumbs-up me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'সমস্যা সমাধান হয়েছে' : 'Resolved Satisfactorily' ?>
                                </button>
                                <!-- Reopen Form Button -->
                                <button type="button" class="btn btn-outline-danger fw-semibold px-3 py-2" data-bs-toggle="collapse" data-bs-target="#reopenBox">
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'সমাধান হয়নি — আবার কাজ প্রয়োজন' : 'Not Resolved — Needs More Work' ?>
                                </button>
                            </div>
                    <?php elseif (empty($complaint['is_owner']) && in_array($citizenStatus, ['confirmation_needed', 'work_completed'], true)): ?>
                        <div class="alert alert-info border-0 rounded-3 p-3 mt-3 d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill text-info fs-5"></i>
                            <div class="small">
                                <?= ($locale ?? 'bn') === 'bn' ? 'মাঠপর্যায়ের কাজ সম্পন্ন হয়েছে। অভিযোগকারী নাগরিক তার একাউন্টে লগইন করে সমাধান নিশ্চিত বা পুনরায় কাজের আবেদন করতে পারেন।' : 'Field work has been completed. The citizen owner can log in to confirm or request rework.' ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($complaint['is_owner']) && in_array($citizenStatus, ['confirmation_needed', 'work_completed'], true)): ?>

                            <!-- Collapse Confirm Form -->
                            <div class="collapse mt-3" id="confirmBox">
                                <div class="card card-body border-0 shadow-sm rounded-3">
                                    <form action="/complaints/<?= (int)$complaint['id'] ?>/confirm-resolution" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="confirm">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted">
                                                <?= ($locale ?? 'bn') === 'bn' ? 'কাজের সন্তুষ্টির রেটিং (১ থেকে ৫ স্টার)' : 'Satisfaction Rating (1 to 5 Stars)' ?>
                                            </label>
                                            <select name="rating" class="form-select form-select-sm" style="max-width: 200px;">
                                                <option value="5">⭐⭐⭐⭐⭐ (অসাধারণ)</option>
                                                <option value="4">⭐⭐⭐⭐ (ভালো)</option>
                                                <option value="3">⭐⭐⭐ (মোটামুটি)</option>
                                                <option value="2">⭐⭐ (চলনসই)</option>
                                                <option value="1">⭐ (অসন্তুষ্ট)</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <textarea name="feedback_notes" rows="2" class="form-control form-control-sm"
                                                      placeholder="<?= ($locale ?? 'bn') === 'bn' ? 'মতামত লিখুন (ঐচ্ছিক)...' : 'Optional notes...' ?>"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-sm px-4">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'চূড়ান্ত নিশ্চিত করুন' : 'Confirm Resolution' ?>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Collapse Reopen Form -->
                            <div class="collapse mt-3" id="reopenBox">
                                <div class="card card-body border-0 shadow-sm rounded-3 bg-danger-subtle">
                                    <form action="/complaints/<?= (int)$complaint['id'] ?>/confirm-resolution" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="reject">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-dark">
                                                <?= ($locale ?? 'bn') === 'bn' ? 'কী কারণে কাজ অসম্পূর্ণ রয়েছে?' : 'Why is the work incomplete?' ?>
                                            </label>
                                            <select name="reopen_reason" class="form-select form-select-sm" required>
                                                <option value="কাজ অসম্পূর্ণ বা আংশিক করা হয়েছে"><?= ($locale ?? 'bn') === 'bn' ? 'কাজ অসম্পূর্ণ বা আংশিক করা হয়েছে' : 'Work is incomplete' ?></option>
                                                <option value="সমস্যার সমাধান সঠিকভাবে হয়নি"><?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার সমাধান সঠিকভাবে হয়নি' : 'Problem not resolved properly' ?></option>
                                                <option value="আবর্জনা বা সরঞ্জাম রেখে দেওয়া হয়েছে"><?= ($locale ?? 'bn') === 'bn' ? 'আবর্জনা বা সরঞ্জাম রেখে দেওয়া হয়েছে' : 'Debris left behind' ?></option>
                                                <option value="অন্যান্য"><?= ($locale ?? 'bn') === 'bn' ? 'অন্যান্য' : 'Other' ?></option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <textarea name="feedback_notes" rows="2" class="form-control form-control-sm" required
                                                      placeholder="<?= ($locale ?? 'bn') === 'bn' ? 'বিস্তারিত কারণ লিখুন...' : 'Describe why rework is needed...' ?>"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-danger btn-sm px-4">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'পুনরায় কাজের জন্য পাঠান' : 'Submit Rework Request' ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Timeline Card -->
                <?php if (!empty($complaint['timeline'])): ?>
                    <div class="card border shadow-sm rounded-4 p-4 bg-white">
                        <h5 class="fw-bold text-dark mb-4">
                            <i class="bi bi-clock-history text-primary me-2"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'কাজের অগ্রগতি ও ইতিহাস' : 'Progress Timeline' ?>
                        </h5>
                        <div class="timeline">
                            <?php foreach ($complaint['timeline'] as $idx => $t): ?>
                                <?php
                                    $st = $t['status'] ?? 'received';
                                    $label = ($locale ?? 'bn') === 'bn' ? ($statusMapBn[$st] ?? $st) : ($statusMapEn[$st] ?? $st);
                                    $isLast = ($idx === count($complaint['timeline']) - 1);
                                ?>
                                <div class="timeline-item <?= $isLast ? 'active' : '' ?>">
                                    <div class="fw-bold text-dark"><?= e($label) ?></div>
                                    <small class="text-muted d-block"><?= format_bn_date((string)$t['created_at']) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
