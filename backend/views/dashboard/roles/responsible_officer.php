<!-- Responsible Officer Dashboard -->
<div class="row g-4 mb-4">
    <!-- Header & Action Bar -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-briefcase-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ২ — দায়িত্বপ্রাপ্ত কর্মকর্তা (Responsible Officer) গভর্ন্যান্স পোর্টাল' : 'Ward 2 — Responsible Officer Governance Portal' ?>
                    </h5>
                    <small class="text-muted">প্রথম শ্রেণির সরকারি কর্মকর্তা হিসেবে ওয়ার্ডের প্রশাসনিক তদারকি, মাঠ পরিদর্শন ও কাজের গুণমান মূল্যায়ন</small>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#inspectionModal">
                        <i class="bi bi-clipboard-check me-1"></i>মাঠ পরিদর্শন নোট দাখিল
                    </button>
                </div>
            </div>

            <!-- Ward KPIs -->
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-primary"><?= to_bn_number((string)($wardKpis['total_complaints'] ?? count($complaints ?? []))) ?></div>
                        <small class="text-muted">ওয়ার্ড ২ এর মোট কেস</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-success"><?= to_bn_number((string)($wardKpis['resolved_count'] ?? 0)) ?></div>
                        <small class="text-muted">সমাধান সম্পন্ন</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-warning-emphasis"><?= to_bn_number((string)($wardKpis['in_progress'] ?? 0)) ?></div>
                        <small class="text-muted">চলমান কাজ</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-danger-subtle rounded-3 text-center border border-danger-subtle">
                        <div class="fs-2 fw-bold text-danger"><?= to_bn_number((string)($wardKpis['overdue_count'] ?? 0)) ?></div>
                        <small class="text-danger fw-semibold">সময় অতিক্রান্ত</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ward Supervisor Contact Details -->
    <?php if (!empty($wardSupervisor)): ?>
        <div class="col-12">
            <div class="card border-primary-subtle bg-primary-subtle rounded-4 p-3 d-flex flex-row justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:44px; height:44px; font-size: 1.2rem;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <strong class="text-dark d-block">ওয়ার্ড ২ এর দায়িত্বরত সুপারভাইজার: <?= e($wardSupervisor['full_name_bn']) ?></strong>
                        <small class="text-muted">সরকারি মোবাইল: <a href="tel:<?= e($wardSupervisor['phone']) ?>" class="fw-bold text-primary font-monospace"><?= e($wardSupervisor['phone']) ?></a></small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#inspectionModal">
                    <i class="bi bi-pencil-square me-1"></i>পরিদর্শন নোট লিখুন
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Complaints & Inspection Queue Table -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">ওয়ার্ড ২ এর অভিযোগ ও সমাধান তদারকি</h6>
                <span class="badge bg-secondary-subtle text-secondary">মোট <?= count($complaints ?? []) ?> টি কেস</span>
            </div>
            <!-- Filter Toolbar -->
            <div class="card border-0 bg-light rounded-3 p-3 mb-3 table-filter-toolbar" data-filter-target="#resp-complaints-table">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 filter-search" placeholder="ট্র্যাকিং নং, সমস্যা বা এলাকা খুঁজুন...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select filter-status">
                            <option value="">সকল অবস্থা (All Status)</option>
                            <option value="submitted">নতুন জমা (Submitted)</option>
                            <option value="assigned">দায়িত্বপ্রাপ্ত (Assigned)</option>
                            <option value="in_progress">চলমান (In Progress)</option>
                            <option value="resolved">সমাধানকৃত (Resolved)</option>
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
                <table class="table table-hover align-middle mb-0" id="resp-complaints-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার ধরন' : 'Category / Problem' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'স্থান / ঠিকানা' : 'Location' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'বর্তমান অবস্থা' : 'Status' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'পরিদর্শন ও অ্যাকশন' : 'Action' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($complaints)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">ওয়ার্ড ২-এ কোনো অভিযোগ পাওয়া যায়নি।</td></tr>
                        <?php else: ?>
                            <?php foreach ($complaints as $c): ?>
                                <?php
                                    $searchStr = ($c['public_complaint_number'] ?? '') . ' ' . ($c['subcategory_name_bn'] ?? '') . ' ' . ($c['landmark'] ?? '') . ' ' . ($c['public_safe_address'] ?? '');
                                ?>
                                <tr data-search="<?= e($searchStr) ?>" data-status="<?= e($c['internal_status'] ?? '') ?>">
                                    <td class="ps-3 font-monospace fw-bold text-dark"><?= e($c['public_complaint_number'] ?? '') ?></td>
                                    <td><strong><?= ($locale ?? 'bn') === 'bn' ? e($c['subcategory_name_bn'] ?? '') : e($c['subcategory_name_en'] ?? '') ?></strong></td>
                                    <td class="small text-muted"><?= e($c['landmark'] ? $c['landmark'] . ', ' : '') ?><?= e($c['public_safe_address'] ?? '') ?></td>
                                    <td><span class="badge bg-info-subtle text-info-emphasis px-2 py-1"><?= e(human_status($c['internal_status'], $locale)) ?></span></td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="/track/<?= urlencode($c['public_complaint_number'] ?? '') ?>" class="btn btn-outline-secondary">ট্র্যাক</a>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#inspectionModal" data-complaint-id="<?= (int)$c['id'] ?>">
                                                পরিদর্শন নোট
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
</div>

<!-- Modal: Official Inspection Note -->
<div class="modal fade" id="inspectionModal" tabindex="-1" aria-labelledby="inspModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/dashboard/inspection-notes" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="inspModalLabel">
                    <i class="bi bi-clipboard-check me-2"></i>মাঠ পরিদর্শন নোট ও গুণমান রেটিং
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">সংশ্লিষ্ট অভিযোগ নির্বাচন করুন</label>
                    <select name="complaint_id" class="form-select" required>
                        <?php foreach (($complaints ?? []) as $c): ?>
                            <option value="<?= (int)$c['id'] ?>">#<?= e($c['public_complaint_number']) ?> — <?= e($c['subcategory_name_bn']) ?> (<?= e($c['landmark'] ?: 'ওয়ার্ড ২') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">কাজের সরেজমিন মান (Inspection Rating)</label>
                    <select name="quality_rating" class="form-select">
                        <option value="good">সন্তোষজনক ও মানসম্মত (Good)</option>
                        <option value="needs_improvement">উন্নতি প্রয়োজন / অসম্পূর্ণ (Needs Work)</option>
                        <option value="poor">অসন্তোষজনক / পুনরাবৃত্তি প্রয়োজন (Poor)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">পরিদর্শন নোট ও প্রশাসনিক মন্তব্য</label>
                    <textarea name="note_text" rows="3" class="form-control" placeholder="সরেজমিনে পরিদর্শনের ফলাফল, ব্যবহৃত জনবল ও সুপারভাইজারের প্রতি নির্দেশনা লিখুন..." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary fw-bold">পরিদর্শন নোট সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>
</div>
