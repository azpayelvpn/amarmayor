<!-- Field Worker / Team Leader Simple Task Portal -->
<div class="card border shadow-sm rounded-4 bg-white p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-clipboard2-check-fill text-success me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'আমার আজকের কাজ' : 'My Assigned Field Tasks' ?>
            </h4>
            <p class="text-muted small mb-0">
                <?= ($locale ?? 'bn') === 'bn' ? 'মাঠ পর্যায়ে নির্ধারিত কাজের তালিকা ও বর্তমান অবস্থা' : 'Daily operational task list for field team execution' ?>
            </p>
        </div>
        <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 rounded-pill">
            <?= ($locale ?? 'bn') === 'bn' ? 'মোট কাজ: ' . to_bn_number((string)count($tasks ?? [])) : 'Total Tasks: ' . count($tasks ?? []) ?>
        </span>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="card bg-light border-0 rounded-4 p-5 text-center my-3">
            <i class="bi bi-emoji-smile fs-1 text-muted d-block mb-2"></i>
            <h5 class="fw-bold text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'আজকের জন্য কোনো কাজ নির্ধারিত নেই।' : 'No field tasks assigned currently.' ?></h5>
            <p class="text-muted small mb-0"><?= ($locale ?? 'bn') === 'bn' ? 'সুপারভাইজার নতুন কাজ বরাদ্দ করলে এখানে প্রদর্শিত হবে।' : 'New tasks assigned by supervisor will appear here.' ?></p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($tasks as $t): ?>
                <?php
                    $isPending = ($t['task_status'] === 'pending');
                    $isInProgress = ($t['task_status'] === 'in_progress');
                    $isCompleted = ($t['task_status'] === 'completed');
                ?>
                <div class="col-12 col-lg-6">
                    <div class="card border rounded-4 h-100 p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge <?= $isCompleted ? 'bg-secondary' : ($isInProgress ? 'bg-warning text-dark' : 'bg-primary') ?> px-2 py-1">
                                <?php
                                    if ($isCompleted) echo ($locale ?? 'bn') === 'bn' ? 'কাজ সম্পন্ন (যাচাই বাকি)' : 'Work Completed (Pending Check)';
                                    elseif ($isInProgress) echo ($locale ?? 'bn') === 'bn' ? 'কাজ চলছে' : 'In Progress';
                                    else echo ($locale ?? 'bn') === 'bn' ? 'নতুন কাজ' : 'New Task';
                                ?>
                            </span>
                            <span class="font-monospace text-muted small"><?= e($t['task_code'] ?? '') ?></span>
                        </div>

                        <!-- 1. What is the problem -->
                        <h5 class="fw-bold text-dark mb-1">
                            <?= ($locale ?? 'bn') === 'bn' ? e($t['subcategory_name_bn'] ?? 'নাগরিক সমস্যা') : e($t['subcategory_name_en'] ?? 'Civic Problem') ?>
                        </h5>
                        <p class="small text-muted mb-2">
                            <?= e($t['description'] ?? '') ?>
                        </p>

                        <!-- 2. Where to go -->
                        <div class="p-2 bg-white rounded-3 border mb-3">
                            <div class="small fw-semibold text-dark">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                <?= ($locale ?? 'bn') === 'bn' ? 'কোথায় যেতে হবে:' : 'Location / Destination:' ?>
                            </div>
                            <div class="text-primary fw-bold">
                                <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number((string)($t['ward_number'] ?? '১')) : 'Ward ' . ($t['ward_number'] ?? '1') ?>
                            </div>
                            <div class="small text-muted">
                                <?= e($t['landmark'] ? $t['landmark'] . ', ' : '') ?><?= e($t['approximate_address'] ?? '') ?>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <?php if (!empty($t['instructions'])): ?>
                            <div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
                                <strong><?= ($locale ?? 'bn') === 'bn' ? 'সুপারভাইজারের নির্দেশনা:' : 'Supervisor Instruction:' ?></strong>
                                <?= e($t['instructions']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Worker Actions -->
                        <div class="mt-auto pt-2 border-top d-flex gap-2 flex-wrap">
                            <?php if ($isPending): ?>
                                <form action="/dashboard/tasks/<?= (int)$t['id'] ?>/start" method="POST" class="w-100">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                        <i class="bi bi-play-circle-fill me-1"></i>
                                        <?= ($locale ?? 'bn') === 'bn' ? 'কাজ শুরু করুন' : 'Start Work' ?>
                                    </button>
                                </form>
                            <?php elseif ($isInProgress): ?>
                                <form action="/dashboard/tasks/<?= (int)$t['id'] ?>/complete" method="POST" class="w-100">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="notes" value="মাঠ পর্যায়ের পরিচ্ছন্নতা সম্পন্ন।">
                                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold mb-2">
                                        <i class="bi bi-check2-circle me-1"></i>
                                        <?= ($locale ?? 'bn') === 'bn' ? 'কাজ সম্পন্ন ঘোষণা করুন' : 'Declare Work Completed' ?>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="alert('ক্যামেরা বা গ্যালারি থেকে ছবি আপলোড করার ইন্টারফেস সংযুক্ত।')">
                                    <i class="bi bi-camera-fill me-1"></i> <?= ($locale ?? 'bn') === 'bn' ? 'ছবি / প্রমাণ যোগ করুন' : 'Attach Photo Evidence' ?>
                                </button>
                            <?php else: ?>
                                <div class="alert alert-secondary py-2 px-3 small w-100 mb-0 text-center">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'সুপারভাইজার যাচাইয়ের পর নাগরিকের কাছে পাঠানো হবে।' : 'Pending supervisor verification before citizen confirmation.' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
