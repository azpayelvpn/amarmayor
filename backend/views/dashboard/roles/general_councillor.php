<!-- General Councillor / Representative Dashboard -->
<div class="row g-4 mb-4">
    <!-- Header & Action Bar -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-person-badge-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ১ — সাধারণ কাউন্সিলর নাগরিক অভিযোগ ও তদারকি পোর্টাল' : 'Ward 1 — Councillor Civic Grievance & Oversight Portal' ?>
                    </h5>
                    <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'কাউন্সিলর হিসেবে আপনার ওয়ার্ডের নাগরিক সমস্যা পর্যবেক্ষণ, অগ্রাধিকার প্রদান ও ফলোআপ' : 'Civic oversight, priority referral and complaint follow-up within elected ward' ?></small>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#referralModal">
                        <i class="bi bi-plus-circle me-1"></i>নাগরিক সমস্যা রেফার করুন
                    </button>
                </div>
            </div>

            <!-- Ward KPIs -->
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-primary"><?= to_bn_number((string)($wardKpis['total_complaints'] ?? count($complaints ?? []))) ?></div>
                        <small class="text-muted">ওয়ার্ডের মোট সমস্যা</small>
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
                        <small class="text-muted">মাঠে চলমান</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-danger-subtle rounded-3 text-center border border-danger-subtle">
                        <div class="fs-2 fw-bold text-danger"><?= to_bn_number((string)($wardKpis['overdue_count'] ?? 0)) ?></div>
                        <small class="text-danger fw-semibold">সময় পেরিয়ে গেছে</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ward Supervisor Contact & Quick Coordination -->
    <?php if (!empty($wardSupervisor)): ?>
        <div class="col-12">
            <div class="card border-primary-subtle bg-primary-subtle rounded-4 p-3 d-flex flex-row justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:44px; height:44px; font-size: 1.2rem;">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div>
                        <strong class="text-dark d-block">ওয়ার্ড ১ এর দায়িত্বরত সুপারভাইজার: <?= e($wardSupervisor['full_name_bn']) ?></strong>
                        <small class="text-muted">সরকারি মোবাইল: <a href="tel:<?= e($wardSupervisor['phone']) ?>" class="fw-bold text-primary font-monospace"><?= e($wardSupervisor['phone']) ?></a></small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#referralModal">
                    <i class="bi bi-chat-dots me-1"></i>অগ্রাধিকার বার্তা পাঠান
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Complaints Table -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h6 class="fw-bold text-dark mb-3">ওয়ার্ডের সক্রিয় নাগরিক সমস্যা ও নিষ্পত্তির তালিকা</h6>

            <!-- Filter Toolbar -->
            <div class="card border-0 bg-light rounded-3 p-3 mb-3 table-filter-toolbar" data-filter-target="#councillor-complaints-table">
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
                <table class="table table-hover align-middle mb-0" id="councillor-complaints-table">
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
                            <tr><td colspan="5" class="text-center py-4 text-muted">কোনো অভিযোগ পাওয়া যায়নি।</td></tr>
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

<!-- Modal: Councillor Referral -->
<div class="modal fade" id="referralModal" tabindex="-1" aria-labelledby="referralModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/dashboard/councillor/referral" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="ward_number" value="1">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="referralModalLabel">
                    <i class="bi bi-megaphone me-2"></i>নাগরিক সমস্যা রেফারেল ও সুপারভাইজার নির্দেশনা
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">কাউন্সিলর কার্যালয়ে আগত নাগরিকের অভিযোগ বা এলাকার জরুরি সমস্যাটি সরাসরি সিস্টেম ও ওয়ার্ড সুপারভাইজারের কাছে রেফার করুন:</p>
                
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
                    <input type="text" name="landmark" class="form-control" placeholder="যেমন: ১ নং ওয়ার্ড, বড় বাজার মোড়" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">সমস্যার বিবরণ ও কাউন্সিলর নির্দেশনা</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="সমস্যার সুনির্দিষ্ট বিবরণ ও কেন দ্রুত ব্যবস্থা নেওয়া প্রয়োজন লিখুন..." required></textarea>
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
                <button type="submit" class="btn btn-primary fw-bold">রেফার করুন</button>
            </div>
        </form>
    </div>
</div>
