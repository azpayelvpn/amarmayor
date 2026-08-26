<!-- Reserved Women Councillor Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-person-badge-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'সংরক্ষিত নারী কাউন্সিলর — নাগরিক অভিযোগ ও তদারকি পোর্টাল' : 'Reserved Women Councillor — Civic Oversight Portal' ?>
                    </h5>
                    <div class="alert alert-warning py-1 px-2 small rounded-2 d-inline-block mb-0 mt-1">
                        <i class="bi bi-info-circle me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'সরকারি গেজেট অনুযায়ী সংরক্ষিত নারী আসনের নির্দিষ্ট ওয়ার্ড বিভাজন চূড়ান্তকরণাধীন।' : 'Official gazette ward grouping for reserved women seats is pending official assignment.' ?>
                    </div>
                </div>
                <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill"><?= ($locale ?? 'bn') === 'bn' ? 'সংরক্ষিত কাউন্সিলর' : 'Reserved Councillor' ?></span>
            </div>

            <!-- Complaints Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                            <tr><td colspan="5" class="text-center py-4 text-muted">No complaints found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($complaints as $c): ?>
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-dark"><?= e($c['public_complaint_number'] ?? '') ?></td>
                                    <td><strong><?= ($locale ?? 'bn') === 'bn' ? e($c['subcategory_name_bn'] ?? '') : e($c['subcategory_name_en'] ?? '') ?></strong></td>
                                    <td class="small text-muted"><?= e($c['landmark'] ? $c['landmark'] . ', ' : '') ?><?= e($c['public_safe_address'] ?? '') ?></td>
                                    <td><span class="badge bg-info-subtle text-info-emphasis px-2 py-1"><?= e($c['internal_status']) ?></span></td>
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
