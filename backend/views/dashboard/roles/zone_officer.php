<!-- Zone Officer Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-pin-map-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'অঞ্চল ১ — আঞ্চলিক নির্বাহী কর্মকর্তার ড্যাশবোর্ড' : 'Zone 1 — Zonal Executive Officer Dashboard' ?>
                    </h5>
                    <small class="text-muted">
                        <?= ($locale ?? 'bn') === 'bn' ? 'অঞ্চল ১ এর অনুমোদিত ওয়ার্ডসমূহ: ১, ২, ৪, ৬, ১১, ১২, ২৭, ২৮, ২৯, ৩০' : 'Authorized Zone 1 Wards: 1, 2, 4, 6, 11, 12, 27, 28, 29, 30' ?>
                    </small>
                </div>
                <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill"><?= ($locale ?? 'bn') === 'bn' ? 'অঞ্চল ১' : 'Zone 1' ?></span>
            </div>

            <!-- Filter Toolbar -->
            <div class="card border-0 bg-light rounded-3 p-3 mb-3 table-filter-toolbar" data-filter-target="#zone-complaints-table">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 filter-search" placeholder="ট্র্যাকিং নং বা সমস্যা খুঁজুন...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select filter-status">
                            <option value="">সকল অবস্থা (All Status)</option>
                            <option value="submitted">নতুন জমা (Submitted)</option>
                            <option value="assigned">দায়িত্বপ্রাপ্ত (Assigned)</option>
                            <option value="in_progress">চলমান (In Progress)</option>
                            <option value="resolved">সমাধানকৃত (Resolved)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select filter-ward">
                            <option value="">সকল ওয়ার্ড</option>
                            <?php foreach ([1, 2, 4, 6, 11, 12, 27, 28, 29, 30] as $zw): ?>
                                <option value="<?= $zw ?>">ওয়ার্ড <?= to_bn_number((string)$zw) ?></option>
                            <?php endforeach; ?>
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

            <!-- Complaints in Zone 1 -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="zone-complaints-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং' : 'Ward #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার বিবরণ' : 'Problem' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'বর্তমান অবস্থা' : 'Status' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'অ্যাকশন' : 'Action' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($complaints)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No complaints found in Zone 1.</td></tr>
                        <?php else: ?>
                            <?php foreach ($complaints as $c): ?>
                                <?php
                                    $searchStr = ($c['public_complaint_number'] ?? '') . ' ' . ($c['subcategory_name_bn'] ?? '') . ' ' . ($c['landmark'] ?? '');
                                ?>
                                <tr data-search="<?= e($searchStr) ?>" data-status="<?= e($c['internal_status'] ?? '') ?>" data-ward="<?= e((string)($c['ward_number'] ?? '')) ?>">
                                    <td class="ps-3 font-monospace fw-bold text-dark"><?= e($c['public_complaint_number'] ?? '') ?></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)($c['ward_number'] ?? '')) : 'Ward ' . ($c['ward_number'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= ($locale ?? 'bn') === 'bn' ? e($c['subcategory_name_bn'] ?? '') : e($c['subcategory_name_en'] ?? '') ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis px-2 py-1"><?= e(human_status($c['internal_status'], $locale)) ?></span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="/track/<?= urlencode($c['public_complaint_number'] ?? '') ?>" class="btn btn-sm btn-outline-primary">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাক করুন &rarr;' : 'Track &rarr;' ?>
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
