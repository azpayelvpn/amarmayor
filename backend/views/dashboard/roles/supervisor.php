<!-- Supervisor Operations & Verification Portal -->
<div class="row g-4 mb-4">
    <!-- Supervisor Quick Metrics -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-people-fill text-primary me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'সুপারভাইজার অপারেশন হাব (Ward 1 Sanitation)' : 'Supervisor Operations Hub' ?>
            </h5>
            <div class="row g-3">
                <?php
                    $pendingCount = count(array_filter($tasks ?? [], fn($t) => $t['task_status'] === 'pending'));
                    $inProgressCount = count(array_filter($tasks ?? [], fn($t) => $t['task_status'] === 'in_progress'));
                    $verificationCount = count(array_filter($tasks ?? [], fn($t) => $t['task_status'] === 'completed' || ($t['complaint_status'] ?? '') === 'work_completed'));
                ?>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-primary"><?= to_bn_number((string)$pendingCount) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'নতুন কাজ (বরাদ্দ বাকি)' : 'New Tasks' ?></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-warning-emphasis"><?= to_bn_number((string)$inProgressCount) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মাঠে চলমান কাজ' : 'In Progress' ?></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-success-subtle rounded-3 text-center border border-success-subtle">
                        <div class="fs-2 fw-bold text-success"><?= to_bn_number((string)$verificationCount) ?></div>
                        <small class="text-success fw-semibold"><?= ($locale ?? 'bn') === 'bn' ? 'কাজ সম্পন্ন — যাচাই প্রয়োজন' : 'Verification Pending' ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Pending Tasks (Priority Queue) -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-shield-check text-success me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'মাঠ পর্যায়ে সম্পন্ন কাজ — সুপারভাইজার যাচাই' : 'Field Work Completed — Verification Pending' ?>
            </h5>

            <?php
                $completedTasks = array_filter($tasks ?? [], fn($t) => $t['task_status'] === 'completed' || ($t['complaint_status'] ?? '') === 'work_completed');
            ?>

            <?php if (empty($completedTasks)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check2-circle fs-2 text-success d-block mb-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'বর্তমানে যাচাইয়ের জন্য কোনো কাজ অপেক্ষমাণ নেই।' : 'No tasks currently awaiting verification.' ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার ধরন ও স্থান' : 'Problem & Location' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'সম্পন্নকারী কর্মী' : 'Field Worker' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'বর্তমান অবস্থা' : 'Status' ?></th>
                                <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'সুপারভাইজার যাচাই অ্যাকশন' : 'Action' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completedTasks as $t): ?>
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-dark">
                                        <?= e($t['public_complaint_number'] ?? $t['tracking_number'] ?? '') ?>
                                    </td>
                                    <td>
                                        <strong><?= ($locale ?? 'bn') === 'bn' ? e($t['subcategory_name_bn'] ?? '') : e($t['subcategory_name_en'] ?? '') ?></strong>
                                        <small class="text-muted d-block">
                                            <?= e($t['landmark'] ? $t['landmark'] . ', ' : '') ?><?= e($t['approximate_address'] ?? '') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= ($locale ?? 'bn') === 'bn' ? e($t['worker_name_bn'] ?? 'মাঠকর্মী') : e($t['worker_name_en'] ?? 'Field Worker') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'যাচাই অপেক্ষমাণ' : 'Verification Needed' ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <form action="/dashboard/tasks/<?= (int)$t['id'] ?>/verify" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-success fw-bold">
                                                <i class="bi bi-check-lg me-1"></i>
                                                <?= ($locale ?? 'bn') === 'bn' ? 'যাচাই ও অনুমোদন করুন' : 'Verify & Send to Citizen' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
