<!-- Control Room Officer Dashboard -->
<div class="row g-4 mb-4">
    <!-- Routing Gap Alerts -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-broadcast text-danger me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'কন্ট্রোল রুম — ট্রায়াজ, গ্যাপ ও কেন্দ্রীয় সমন্বয়' : 'Control Room — Triage, Routing Gaps & Central Dispatch' ?>
            </h5>

            <?php if (!empty($gapAlerts)): ?>
                <div class="alert alert-warning rounded-3 mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong><?= ($locale ?? 'bn') === 'bn' ? 'সতর্কতা:' : 'Alert:' ?></strong>
                    <?= count($gapAlerts) ?> <?= ($locale ?? 'bn') === 'bn' ? 'টি সাবক্যাটেগরির জন্য স্বয়ংক্রিয় রাউটিং রুল পাওয়া যায়নি।' : 'subcategories have unconfigured routing rules.' ?>
                </div>
            <?php endif; ?>

            <!-- Active Triage Queue -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং' : 'Ward #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার ধরন' : 'Category / Problem' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'বর্তমান অবস্থা' : 'Status' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'সমন্বয়' : 'Dispatch' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($activeTriage)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No pending triage cases.</td></tr>
                        <?php else: ?>
                            <?php foreach ($activeTriage as $c): ?>
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-dark"><?= e($c['public_complaint_number'] ?? '') ?></td>
                                    <td><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)($c['ward_number'] ?? '')) : 'Ward ' . ($c['ward_number'] ?? '') ?></td>
                                    <td><strong><?= ($locale ?? 'bn') === 'bn' ? e($c['subcategory_name_bn'] ?? '') : e($c['subcategory_name_en'] ?? '') ?></strong></td>
                                    <td><span class="badge bg-primary-subtle text-primary px-2 py-1"><?= e(human_status($c['internal_status'], $locale)) ?></span></td>
                                    <td class="text-end pe-3">
                                        <a href="/track/<?= urlencode($c['public_complaint_number'] ?? '') ?>" class="btn btn-sm btn-outline-primary">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'কেস দেখুন' : 'View Case' ?>
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
