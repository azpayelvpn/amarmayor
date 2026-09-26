<!-- Control Room Officer Dashboard -->
<div class="row g-4 mb-4">
    <!-- Routing Gap Alerts -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-broadcast text-danger me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'কন্ট্রোল রুম — ট্রায়াজ, রাউটিং ও কেন্দ্রীয় সমন্বয়' : 'Control Room — Triage, Routing & Central Dispatch' ?>
                    </h5>
                    <p class="text-muted small mb-0">
                        <?= ($locale ?? 'bn') === 'bn' ? 'অস্পষ্ট, ভুল ক্যাটেগরি বা মিস-রাউটেড নাগরিক অভিযোগ যাচাই ও সঠিক বিভাগে দ্রুত পুনর্বণ্টন করুন।' : 'Review misclassified or unrouted civic complaints and re-route them to the correct department/ward.' ?>
                    </p>
                </div>
                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-activity me-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'সরাসরি ডিসপ্যাচ সেল' : 'Live Dispatch Cell' ?>
                </span>
            </div>

            <?php if (!empty($gapAlerts)): ?>
                <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-4 me-3"></i>
                    <div>
                        <strong><?= ($locale ?? 'bn') === 'bn' ? 'স্বয়ংক্রিয় রাউটিং গ্যাপ সতর্কতা:' : 'Automatic Routing Gap Alert:' ?></strong>
                        <?= count($gapAlerts) ?> <?= ($locale ?? 'bn') === 'bn' ? 'টি উপ-বিভাগে স্বয়ংক্রিয় এসাইনমেন্ট রুল কনফিগার করা নেই। ম্যানুয়াল ডিসপ্যাচ প্রয়োজন।' : 'subcategories lack auto-routing rules and require manual dispatch.' ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Active Triage Queue -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং' : 'Ward #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার বিষয়' : 'Category / Problem' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'বর্তমান দপ্তর' : 'Current Dept' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'অবস্থা' : 'Status' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'পদক্ষেপ' : 'Action' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($activeTriage)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'কোন অনিষ্পন্ন বা মিস-রাউটেড অভিযোগ নেই।' : 'No pending triage cases.' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($activeTriage as $c): ?>
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-dark"><?= e($c['public_complaint_number'] ?? '') ?></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)($c['ward_number'] ?? '')) : 'Ward ' . ($c['ward_number'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="d-block text-dark"><?= ($locale ?? 'bn') === 'bn' ? e($c['subcategory_name_bn'] ?? '') : e($c['subcategory_name_en'] ?? '') ?></strong>
                                        <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= e($c['landmark'] ?? 'স্থান উল্লেখ নেই') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis">
                                            <?= e($c['department_name_bn'] ?? 'অনির্ধারিত') ?>
                                        </span>
                                    </td>
                                    <td><span class="badge bg-primary-subtle text-primary px-2 py-1"><?= e(human_status($c['internal_status'], $locale)) ?></span></td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="/track/<?= urlencode($c['public_complaint_number'] ?? '') ?>" target="_blank" class="btn btn-outline-secondary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#rerouteModal"
                                                onclick="setRerouteTarget('<?= $c['id'] ?>', '<?= e($c['public_complaint_number']) ?>', '<?= $c['department_id'] ?? '' ?>', '<?= $c['ward_id'] ?? '' ?>')">
                                                <i class="bi bi-shuffle me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'রি-রাউট' : 'Re-route' ?>
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

<!-- Re-Route Modal -->
<div class="modal fade" id="rerouteModal" tabindex="-1" aria-labelledby="rerouteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold" id="rerouteModalLabel">
                    <i class="bi bi-shuffle me-2"></i><?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ পুনর্বণ্টন (Re-Route)' : 'Re-route Complaint' ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="/dashboard/control-room/re-route">
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        <?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ নং:' : 'Complaint No:' ?> <strong id="reroute_complaint_num" class="text-primary font-monospace"></strong>
                    </p>
                    <input type="hidden" name="complaint_id" id="reroute_complaint_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'সঠিক বিভাগ নির্বাচন করুন' : 'Assign to Department' ?></label>
                        <select name="department_id" id="reroute_dept_id" class="form-select">
                            <option value=""><?= ($locale ?? 'bn') === 'bn' ? '-- বিভাগ অপরিবর্তিত রাখুন --' : '-- Keep current department --' ?></option>
                            <?php foreach ($allDepartments ?? [] as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= e($dept['name_bn']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'সঠিক ওয়ার্ড নির্বাচন করুন' : 'Assign to Ward' ?></label>
                        <select name="ward_id" id="reroute_ward_id" class="form-select">
                            <option value=""><?= ($locale ?? 'bn') === 'bn' ? '-- ওয়ার্ড অপরিবর্তিত রাখুন --' : '-- Keep current ward --' ?></option>
                            <?php foreach ($allWards ?? [] as $ward): ?>
                                <option value="<?= $ward['id'] ?>"><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)$ward['ward_number']) : 'Ward ' . $ward['ward_number'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'পুনর্বণ্টনের কারণ / পর্যবেক্ষণ' : 'Reason / Note' ?> <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="<?= ($locale ?? 'bn') === 'bn' ? 'যেমন: নাগরিক ভুলবশত বিদ্যুৎ বিভাগে দিয়েছিলেন, এটি সড়ক বাতির অধীন।' : 'Reason for re-routing' ?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= ($locale ?? 'bn') === 'bn' ? 'বাতিল' : 'Cancel' ?></button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4"><?= ($locale ?? 'bn') === 'bn' ? 'পুনর্বণ্টন সম্পন্ন করুন' : 'Confirm Re-route' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setRerouteTarget(id, num, deptId, wardId) {
    document.getElementById('reroute_complaint_id').value = id;
    document.getElementById('reroute_complaint_num').innerText = num;
    if (deptId) document.getElementById('reroute_dept_id').value = deptId;
    if (wardId) document.getElementById('reroute_ward_id').value = wardId;
}
</script>
