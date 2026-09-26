<!-- Auditor Read-Only Dashboard -->
<div class="row g-4 mb-4">
    <!-- Audit Overview & Anomaly Section -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-shield-check text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'অভ্যন্তরীণ নিরীক্ষক — অডিট ট্রেইল ও অস্বাভাবিকতা পর্যবেক্ষণ (Audit & Anomalies)' : 'Internal Auditor — Audit Trail & Anomaly Surveillance' ?>
                    </h5>
                    <p class="text-muted small mb-0">
                        <?= ($locale ?? 'bn') === 'bn' ? 'নিরীক্ষক পোর্টালটি সম্পূর্ণ রিড-অনলি। সকল কেস পরিবর্তন ও স্টেট মেশিন ট্রানজিশন ক্রমানুসারে সংরক্ষিত।' : 'Audit portal is strictly read-only. All system events and state machine transitions are immutably logged.' ?>
                    </p>
                </div>
                <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-lock-fill me-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'অপরিবর্তনযোগ্য রিড-অনলি আর্কাইভ' : 'Immutable Read-Only Archive' ?>
                </span>
            </div>

            <!-- Suspicious Fast-Closure Anomaly Detection Queue -->
            <div class="card border-warning border-opacity-50 shadow-none rounded-3 mb-4 bg-warning bg-opacity-10">
                <div class="card-header bg-transparent py-3 border-warning border-opacity-25 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-warning-emphasis mb-0">
                        <i class="bi bi-exclamation-octagon-fill text-warning me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'সতর্কতা: সন্দেহজনক দ্রুত নিষ্পত্তি পর্যবেক্ষণ (Anomaly Queue — Resolved in < 60 mins)' : 'Suspicious Fast-Closure Anomaly Queue (< 60 mins)' ?>
                    </h6>
                    <span class="badge bg-warning text-dark"><?= count($anomalyCases ?? []) ?> <?= ($locale ?? 'bn') === 'bn' ? 'টি কেস চিহ্নিত' : 'cases flagged' ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 bg-white">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড' : 'Ward' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'ক্যাটাগরি' : 'Category' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'সমাধানের সময়কাল' : 'Duration to Resolve' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'সম্পন্নের তারিখ' : 'Closed At' ?></th>
                                <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'নিরীক্ষা' : 'Audit Case' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($anomalyCases)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        <?= ($locale ?? 'bn') === 'bn' ? 'কোন সন্দেহজনক দ্রুত নিষ্পত্তির ঘটনা পাওয়া যায়নি।' : 'No fast-closure anomalies detected.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($anomalyCases as $ac): ?>
                                    <tr>
                                        <td class="ps-3 font-monospace fw-bold text-dark"><?= e($ac['public_complaint_number']) ?></td>
                                        <td><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)$ac['ward_number']) : 'Ward ' . $ac['ward_number'] ?></td>
                                        <td><?= e($ac['subcategory_name_bn']) ?></td>
                                        <td>
                                            <span class="badge bg-danger-subtle text-danger fw-bold">
                                                <?= to_bn_number((string)$ac['duration_minutes']) ?> <?= ($locale ?? 'bn') === 'bn' ? 'মিনিট' : 'mins' ?>
                                            </span>
                                        </td>
                                        <td class="small text-muted"><?= e(date('d M Y, h:i A', strtotime($ac['closed_at']))) ?></td>
                                        <td class="text-end pe-3">
                                            <a href="/track/<?= urlencode($ac['public_complaint_number']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <?= ($locale ?? 'bn') === 'bn' ? 'প্রমাণক দেখুন' : 'Verify Evidence' ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Full Immutable Audit Logs -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-journal-text text-primary me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'সার্বিক সিস্টেম ইভেন্ট ও স্টেট পরিবর্তন লগ (Audit Logs)' : 'Immutable Audit Logs' ?>
                </h6>
                <div style="max-width: 260px;">
                    <input type="text" id="auditSearchInput" class="form-control form-control-sm" placeholder="<?= ($locale ?? 'bn') === 'bn' ? 'খুঁজুন (অ্যাকশন / ইমেইল)...' : 'Filter logs...' ?>" onkeyup="filterAuditLogs()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="auditTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'লগ আইডি' : 'Log ID' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'অ্যাকশন (Action)' : 'Action' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'সম্পাদনকারী' : 'Actor' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'টার্গেট এন্টিটি' : 'Target Entity' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'তারিখ ও সময়' : 'Timestamp' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($auditLogs)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'কোন অডিট রেকর্ড পাওয়া যায়নি।' : 'No audit records found.' ?></td></tr>
                        <?php else: ?>
                            <?php foreach ($auditLogs as $l): ?>
                                <tr>
                                    <td class="ps-3 font-monospace small text-muted">#<?= (int)$l['id'] ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary font-monospace"><?= e($l['action']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small"><?= e($l['actor_name'] ?? 'সিস্টেম বা নামহীন') ?></div>
                                        <small class="text-muted font-monospace"><?= e($l['actor_email'] ?? 'system@internal') ?></small>
                                    </td>
                                    <td class="font-monospace small">
                                        <span class="badge bg-light text-dark border"><?= e($l['entity_type']) ?> #<?= e((string)$l['entity_id']) ?></span>
                                    </td>
                                    <td class="text-end pe-3 small text-muted font-monospace"><?= e(date('d M Y, h:i A', strtotime($l['created_at']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterAuditLogs() {
    const input = document.getElementById("auditSearchInput").value.toLowerCase();
    const rows = document.querySelectorAll("#auditTable tbody tr");
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}
</script>
