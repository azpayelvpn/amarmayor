<!-- Auditor Read-Only Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-journal-text text-primary me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'অভ্যন্তরীণ নিরীক্ষক — অপরিবর্তনযোগ্য অডিট ও ট্রানজিশন লগ' : 'Internal Auditor — Immutable Audit & Transition Logs' ?>
            </h5>
            <div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
                <i class="bi bi-info-circle me-1"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'নিরীক্ষক পোর্টালটি সম্পূর্ণ রিড-অনলি। সকল কেস পরিবর্তন ও স্টেট মেশিন ট্রানজিশন ক্রমানুসারে সংরক্ষিত।' : 'Audit portal is strictly read-only. All system events and state machine transitions are immutably logged.' ?>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'লগ আইডি' : 'Log ID' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'অ্যাকশন' : 'Action' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'ব্যবহারকারী' : 'Actor' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'টার্গেট এন্টিটি' : 'Target Entity' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'সময়' : 'Timestamp' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($auditLogs)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No audit records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($auditLogs as $l): ?>
                                <tr>
                                    <td class="ps-3 font-monospace small">#<?= (int)$l['id'] ?></td>
                                    <td><span class="badge bg-secondary"><?= e($l['action']) ?></span></td>
                                    <td class="small"><?= e($l['actor_email'] ?? 'System / Anonymous') ?></td>
                                    <td class="font-monospace small"><?= e($l['entity_type']) ?>:<?= e((string)$l['entity_id']) ?></td>
                                    <td class="text-end pe-3 small text-muted"><?= e($l['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
