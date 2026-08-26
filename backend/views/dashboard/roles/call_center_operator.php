<!-- Call Center Operator Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-md-5">
        <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-headset text-primary me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'সহায়তাপ্রাপ্ত অভিযোগ গ্রহণ (Call Intake)' : 'Assisted Phone Intake' ?>
            </h5>
            <p class="text-muted small mb-3">
                <?= ($locale ?? 'bn') === 'bn' ? 'ফোনে কলকারী নাগরিকের পক্ষে নতুন অভিযোগ দাখিল করুন।' : 'Submit a complaint on behalf of a citizen calling the helpline.' ?>
            </p>
            <a href="/complaints/create" class="btn btn-civic-primary w-100 py-3 fw-bold">
                <i class="bi bi-plus-circle-fill me-1"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'নতুন অভিযোগ নিবন্ধন করুন' : 'Register New Phone Complaint' ?>
            </a>

            <div class="mt-4 pt-3 border-top">
                <label class="form-label fw-semibold small text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'দ্রুত নাগরিক ট্র্যাকিং অনুসন্ধান:' : 'Quick Status Lookup:' ?></label>
                <form action="/track" method="GET" class="d-flex gap-2">
                    <input type="text" name="tracking_number" placeholder="MCC-XXXX-XXXXX" required class="form-control form-control-sm font-monospace">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><?= ($locale ?? 'bn') === 'bn' ? 'খুঁজুন' : 'Search' ?></button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-telephone-inbound-fill text-success me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'সম্প্রতি নিবন্ধিত অভিযোগসমূহ' : 'Recently Registered Intakes' ?>
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'ধরন' : 'Category' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড' : 'Ward' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'অ্যাকশন' : 'Action' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentIntakes)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No recent phone intakes.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentIntakes as $item): ?>
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-dark"><?= e($item['public_complaint_number'] ?? '') ?></td>
                                    <td><?= ($locale ?? 'bn') === 'bn' ? e($item['subcategory_name_bn'] ?? '') : e($item['subcategory_name_en'] ?? '') ?></td>
                                    <td><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)($item['ward_number'] ?? '')) : 'Ward ' . ($item['ward_number'] ?? '') ?></td>
                                    <td class="text-end pe-3">
                                        <a href="/track/<?= urlencode($item['public_complaint_number'] ?? '') ?>" class="btn btn-sm btn-outline-primary">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'অবস্থা দেখুন' : 'Check Status' ?>
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
