<!-- Ward Officer Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ১ — প্রশাসনিক ও নাগরিক অভিযোগ পোর্টাল' : 'Ward 1 — Administrative & Civic Grievance Portal' ?>
                    </h5>
                    <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ১ সংশ্লিষ্ট সকল সক্রিয় অভিযোগ ও কর্মকা্ল' : 'Complaints and operations within Ward 1 jurisdiction' ?></small>
                </div>
                <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill"><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ১' : 'Ward #1' ?></span>
            </div>

            <!-- Filter Toolbar -->
            <div class="card border-0 bg-light rounded-3 p-3 mb-3 table-filter-toolbar" data-filter-target="#ward-complaints-table">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 filter-search" placeholder="ট্র্যাকিং নং, বিবরণ বা এলাকা খুঁজুন...">
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

            <!-- Ward Complaints Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="ward-complaints-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার ধরন' : 'Category / Problem' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'স্থান / ঠিকানা' : 'Location' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'বর্তমান অবস্থা' : 'Status' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'বিস্তারিত' : 'Details' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($complaints)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No complaints in Ward 1.</td></tr>
                        <?php else: ?>
                            <?php foreach ($complaints as $c): ?>
                                <?php
                                    $searchStr = ($c['public_complaint_number'] ?? '') . ' ' . ($c['subcategory_name_bn'] ?? '') . ' ' . ($c['landmark'] ?? '') . ' ' . ($c['public_safe_address'] ?? '');
                                ?>
                                <tr data-search="<?= e($searchStr) ?>" data-status="<?= e($c['internal_status'] ?? '') ?>">
                                    <td class="ps-3">
                                        <a href="/dashboard/complaints/<?= urlencode($c['public_complaint_number'] ?? '') ?>"
                                           class="font-monospace fw-bold text-primary text-decoration-none">
                                            <?= e($c['public_complaint_number'] ?? '') ?>
                                            <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <strong><?= ($locale ?? 'bn') === 'bn' ? e($c['subcategory_name_bn'] ?? '') : e($c['subcategory_name_en'] ?? '') ?></strong>
                                    </td>
                                    <td class="small text-muted">
                                        <?= e($c['landmark'] ? $c['landmark'] . ', ' : '') ?><?= e($c['public_safe_address'] ?? '') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis px-2 py-1">
                                            <?= e(human_status($c['internal_status'], $locale)) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="/dashboard/complaints/<?= urlencode($c['public_complaint_number'] ?? '') ?>" class="btn btn-sm btn-primary fw-semibold">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'বিস্তারিত →' : 'Full Detail →' ?>
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
