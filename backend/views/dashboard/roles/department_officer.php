<!-- Department Officer Operational & Field Inspection Dashboard -->
<div class="row g-4 mb-4">
    <!-- Header & Action Bar -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-patch-check-fill text-info me-2"></i>
                        <?= e($departmentNameBn ?? 'বিভাগীয় কর্মকর্তা') ?> — অপারেশনাল পরিদর্শন ও কোয়ালিটি অডিট
                    </h5>
                    <small class="text-muted">মাঠপর্যায়ে চলমান কাজের স্পট চেকিং, সুপারভাইজারদের কাজের মান মূল্যায়ন ও সমাধানকৃত কাজের সত্যতা যাচাই</small>
                </div>
                <button type="button" class="btn btn-info text-white fw-bold" data-bs-toggle="modal" data-bs-target="#officerAuditModal">
                    <i class="bi bi-check2-circle me-1"></i>স্পট চেক অডিট নোট লিখুন
                </button>
            </div>

            <!-- KPIs -->
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-primary"><?= to_bn_number((string)($kpis['total_complaints'] ?? 0)) ?></div>
                        <small class="text-muted">বিভাগের মোট সমস্যা</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-warning-emphasis"><?= to_bn_number((string)($kpis['in_progress'] ?? 0)) ?></div>
                        <small class="text-muted">মাঠে চলমান টিম</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-success"><?= to_bn_number((string)($kpis['resolved_count'] ?? 0)) ?></div>
                        <small class="text-muted">সমাধান সম্পন্ন</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-danger-subtle rounded-3 text-center border border-danger-subtle">
                        <div class="fs-2 fw-bold text-danger"><?= to_bn_number((string)($kpis['overdue_count'] ?? 0)) ?></div>
                        <small class="text-danger fw-semibold">জরুরি তদারকি প্রয়োজন</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Random Spot-Check Audit Queue for Completed Work -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-search text-info me-2"></i>সমাধানকৃত কাজের র্যান্ডম স্পট-চেকিং অডিট কিউ
                </h6>
                <small class="text-muted">সুপারভাইজার সমাধান দেখালেও কর্মকর্তার সরেজমিন গুণমান যাচাই</small>
            </div>
            <!-- Filter Toolbar -->
            <div class="card border-0 bg-light rounded-3 p-3 mb-3 table-filter-toolbar" data-filter-target="#officer-audit-table">
                <div class="row g-2 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 filter-search" placeholder="ট্র্যাকিং নং, বিবরণ বা এলাকা খুঁজুন...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select filter-ward">
                            <option value="">সকল ওয়ার্ড</option>
                            <?php for ($w = 1; $w <= 33; $w++): ?>
                                <option value="<?= $w ?>">ওয়ার্ড <?= to_bn_number((string)$w) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100 filter-reset">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>রিসেট
                        </button>
                    </div>
                </div>
                <div class="mt-2 text-muted small filter-count"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="officer-audit-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ট্র্যাকিং নং</th>
                            <th>ওয়ার্ড</th>
                            <th>কাজের ধরন</th>
                            <th>স্থান</th>
                            <th>অবস্থা</th>
                            <th class="text-end pe-3">অডিট অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentCompletedForAudit)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">বর্তমানে অডিটের জন্য কোনো কেস নেই।</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentCompletedForAudit as $ca): ?>
                                <?php
                                    $searchStr = ($ca['public_complaint_number'] ?? '') . ' ' . ($ca['subcategory_name_bn'] ?? '') . ' ' . ($ca['landmark'] ?? '');
                                ?>
                                <tr data-search="<?= e($searchStr) ?>" data-ward="<?= e((string)($ca['ward_number'] ?? '')) ?>">
                                    <td class="ps-3 font-monospace fw-bold text-dark">#<?= e($ca['public_complaint_number']) ?></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">ওয়ার্ড <?= to_bn_number((string)$ca['ward_number']) ?></span></td>
                                    <td><strong><?= e($ca['subcategory_name_bn']) ?></strong></td>
                                    <td class="small text-muted"><?= e($ca['landmark'] ?: 'ওয়ার্ড এলাকা') ?></td>
                                    <td><span class="badge bg-success-subtle text-success"><?= e(human_status($ca['internal_status'], $locale)) ?></span></td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="/track/<?= urlencode($ca['public_complaint_number']) ?>" class="btn btn-outline-secondary">ছবি প্রমাণ</a>
                                            <button type="button" class="btn btn-outline-info text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#officerAuditModal" data-complaint-id="<?= (int)$ca['id'] ?>">
                                                অডিট নোট
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Past Inspection Logs Recorded by This Officer -->
    <?php if (!empty($myInspectionLogs)): ?>
        <div class="col-12">
            <div class="card border shadow-sm rounded-4 bg-white p-4">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-clock-history text-secondary me-2"></i>আমার দাখিলকৃত ফিল্ড পরিদর্শন ও অডিট রেকর্ড
                </h6>
                <div class="list-group list-group-flush">
                    <?php foreach ($myInspectionLogs as $il): ?>
                        <div class="list-group-item px-0 py-2 border-0 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <span class="font-monospace fw-bold text-dark me-2">#<?= e($il['public_complaint_number']) ?></span>
                                <span class="small text-secondary"><?= e($il['note_text']) ?></span>
                            </div>
                            <small class="text-muted font-monospace"><?= e($il['created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Field Officer Audit -->
<div class="modal fade" id="officerAuditModal" tabindex="-1" aria-labelledby="auditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/dashboard/inspection-notes" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold" id="auditModalLabel">
                    <i class="bi bi-patch-check me-2"></i>মাঠ স্পট চেক ও কোয়ালিটি অডিট নোট
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">অভিযোগ নির্বাচন করুন</label>
                    <select name="complaint_id" class="form-select" required>
                        <?php foreach (($recentCompletedForAudit ?? []) as $ca): ?>
                            <option value="<?= (int)$ca['id'] ?>">#<?= e($ca['public_complaint_number']) ?> — <?= e($ca['subcategory_name_bn']) ?> (ওয়ার্ড <?= to_bn_number((string)$ca['ward_number']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">কাজের মান অডিট (Quality Rating)</label>
                    <select name="quality_rating" class="form-select">
                        <option value="verified_excellent">শতভাগ সঠিক সমাধান (Excellent)</option>
                        <option value="needs_touchup">সামান্য ত্রুটি আছে / ফিনিশিং প্রয়োজন (Needs Touchup)</option>
                        <option value="fake_resolution">ভুয়া সমাধান চিহ্নিত / পুনরায় কাজ দরকার (Rejected)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">অডিট পর্যবেক্ষণ ও বিভাগীয় প্রধানের জন্য নোট</label>
                    <textarea name="note_text" rows="3" class="form-control" placeholder="সরেজমিনে পরিদর্শনের সময় কি দেখা গেছে এবং টিম সঠিকভাবে কাজ করেছে কিনা লিখুন..." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-info text-white fw-bold">অডিট নোট সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>
</div>
