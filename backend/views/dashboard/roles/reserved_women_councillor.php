<!-- Reserved Women Councillor Dashboard -->
<div class="row g-4 mb-4">
    <!-- Header & Cluster Selector -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-person-badge-fill text-danger me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'সংরক্ষিত নারী কাউন্সিলর — ক্লাস্টার নাগরিক তদারকি ও সুরক্ষা পোর্টাল' : 'Reserved Women Councillor — Cluster Civic Oversight Portal' ?>
                    </h5>
                    <small class="text-muted">আপনার আওতাধীন ক্লাস্টারভুক্ত ৩টি ওয়ার্ডের নাগরিক সেবা ও নারী-বান্ধব সুবিধা তদারকি</small>
                </div>
                <div class="d-flex gap-2 align-items-center mt-2 mt-md-0">
                    <form method="GET" action="/dashboard" class="d-flex align-items-center gap-2">
                        <label class="small fw-bold text-muted text-nowrap">ওয়ার্ড নির্বাচন:</label>
                        <select name="cluster_ward" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach ($clusterWards as $cw): ?>
                                <option value="<?= (int)$cw ?>" <?= ($selectedWard == $cw) ? 'selected' : '' ?>>ওয়ার্ড নং <?= to_bn_number((string)$cw) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <button type="button" class="btn btn-sm btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#referralModal">
                        <i class="bi bi-plus-circle me-1"></i>সমস্যা রেফার করুন
                    </button>
                </div>
            </div>

            <!-- Ward KPIs for selected ward -->
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-primary"><?= to_bn_number((string)($wardKpis['total_complaints'] ?? count($complaints ?? []))) ?></div>
                        <small class="text-muted">ওয়ার্ড <?= to_bn_number((string)$selectedWard) ?> এর মোট সমস্যা</small>
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

    <!-- Supervisor Card for Selected Ward -->
    <?php if (!empty($wardSupervisor)): ?>
        <div class="col-12">
            <div class="card border-danger-subtle bg-danger-subtle rounded-4 p-3 d-flex flex-row justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width:44px; height:44px; font-size: 1.2rem;">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div>
                        <strong class="text-dark d-block">ওয়ার্ড <?= to_bn_number((string)$selectedWard) ?> এর দায়িত্বরত সুপারভাইজার: <?= e($wardSupervisor['full_name_bn']) ?></strong>
                        <small class="text-muted">সরকারি মোবাইল: <a href="tel:<?= e($wardSupervisor['phone']) ?>" class="fw-bold text-danger font-monospace"><?= e($wardSupervisor['phone']) ?></a></small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger fw-bold" data-bs-toggle="modal" data-bs-target="#referralModal">
                    <i class="bi bi-chat-dots me-1"></i>অগ্রাধিকার বার্তা পাঠান
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Complaints Table for Selected Ward -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">ওয়ার্ড <?= to_bn_number((string)$selectedWard) ?> এর সক্রিয় নাগরিক সমস্যা তালিকা</h6>
                <span class="badge bg-secondary-subtle text-secondary">মোট <?= count($complaints ?? []) ?> টি রেকর্ড</span>
            </div>
            <!-- Filter Toolbar -->
            <div class="card border-0 bg-light rounded-3 p-3 mb-3 table-filter-toolbar" data-filter-target="#reserved-complaints-table">
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
                <table class="table table-hover align-middle mb-0" id="reserved-complaints-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার বিবরণ' : 'Problem' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'স্থান' : 'Location' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'বর্তমান অবস্থা' : 'Status' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'ফলোআপ' : 'Follow Up' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($complaints)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">ওয়ার্ড <?= to_bn_number((string)$selectedWard) ?>-এ কোনো সক্রিয় অভিযোগ পাওয়া যায়নি।</td></tr>
                        <?php else: ?>
                            <?php foreach ($complaints as $c): ?>
                                <?php
                                    $searchStr = ($c['public_complaint_number'] ?? '') . ' ' . ($c['subcategory_name_bn'] ?? '') . ' ' . ($c['landmark'] ?? '');
                                ?>
                                <tr data-search="<?= e($searchStr) ?>" data-status="<?= e($c['internal_status'] ?? '') ?>">
                                    <td class="ps-3 font-monospace fw-bold text-dark"><?= e($c['public_complaint_number'] ?? '') ?></td>
                                    <td><strong><?= ($locale ?? 'bn') === 'bn' ? e($c['subcategory_name_bn'] ?? '') : e($c['subcategory_name_en'] ?? '') ?></strong></td>
                                    <td class="small text-muted"><?= e($c['landmark'] ? $c['landmark'] . ', ' : '') ?><?= e($c['public_safe_address'] ?? '') ?></td>
                                    <td><span class="badge bg-info-subtle text-info-emphasis px-2 py-1"><?= e(human_status($c['internal_status'], $locale)) ?></span></td>
                                    <td class="text-end pe-3">
                                        <a href="/track/<?= urlencode($c['public_complaint_number'] ?? '') ?>" class="btn btn-sm btn-outline-primary">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'দেখুন &rarr;' : 'View &rarr;' ?>
                                        </a>
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

<!-- Modal: Reserved Councillor Referral -->
<div class="modal fade" id="referralModal" tabindex="-1" aria-labelledby="refModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/dashboard/councillor/referral" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="ward_number" value="<?= (int)$selectedWard ?>">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="refModalLabel">
                    <i class="bi bi-megaphone me-2"></i>ওয়ার্ড <?= to_bn_number((string)$selectedWard) ?> — নাগরিক সমস্যা রেফারেল
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">সেবা ক্যাটাগরি</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name_bn']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">স্থান / সড়কের নাম / ল্যান্ডমার্ক</label>
                    <input type="text" name="landmark" class="form-control" placeholder="যেমন: ওয়ার্ড <?= to_bn_number((string)$selectedWard) ?>, স্কুল রোড" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">সমস্যার বিবরণ ও কাউন্সিলর নির্দেশনা</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="নারী ও শিশু নিরাপত্তা, ড্রেন সংস্কার, সড়কবাতি বা বর্জ্য ব্যবস্থাপনা সংক্রান্ত বিবরণ লিখুন..." required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">অগ্রাধিকার স্তর</label>
                    <select name="priority" class="form-select">
                        <option value="p2_medium">সাধারণ অগ্রাধিকার</option>
                        <option value="p1_urgent">জরুরি নাগরিক ঝুঁকি (Urgent)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-danger fw-bold">রেফার করুন</button>
            </div>
        </form>
    </div>
</div>
