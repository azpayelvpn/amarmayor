<div class="container py-4">
    <!-- Role Header Banner -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:56px;height:56px;font-size:1.6rem;">
                    <?php if (in_array($primaryRole, ['mayor', 'administrator', 'ceo'])): ?>
                        <i class="bi bi-bank2"></i>
                    <?php elseif (in_array($primaryRole, ['field_worker', 'team_leader'])): ?>
                        <i class="bi bi-tools"></i>
                    <?php elseif ($primaryRole === 'supervisor'): ?>
                        <i class="bi bi-person-workspace"></i>
                    <?php elseif (in_array($primaryRole, ['platform_super_admin', 'technical_super_admin'])): ?>
                        <i class="bi bi-sliders"></i>
                    <?php elseif (in_array($primaryRole, ['general_councillor', 'reserved_women_councillor', 'responsible_officer'])): ?>
                        <i class="bi bi-person-badge"></i>
                    <?php else: ?>
                        <i class="bi bi-grid-fill"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h4 class="fw-bold mb-0 text-dark">
                            <?= ($locale ?? 'bn') === 'bn' ? e($person['full_name_bn'] ?? $user->email) : e($person['full_name_en'] ?? $user->email) ?>
                        </h4>
                        <span class="badge bg-primary px-3 py-1 rounded-pill">
                            <?= e(strtoupper(str_replace('_', ' ', $primaryRole))) ?>
                        </span>
                        <?php if (!empty($employee['designation_bn'])): ?>
                            <span class="badge bg-light text-dark border">
                                <?= ($locale ?? 'bn') === 'bn' ? e($employee['designation_bn']) : e($employee['designation_en'] ?? '') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">
                        <?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশন অভ্যন্তরীণ পোর্টাল' : 'Mymensingh City Corporation Internal Portal' ?>
                        &bull; <?= e($user->email) ?>
                    </small>
                </div>
            </div>

            <!-- Quick Action Links -->
            <div class="d-flex align-items-center gap-2">
                <a href="/" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-globe me-1"></i> <?= ($locale ?? 'bn') === 'bn' ? 'পাবলিক সাইট দেখুন' : 'View Public Site' ?>
                </a>
                <a href="/dev/testing-access" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-shuffle me-1"></i> <?= ($locale ?? 'bn') === 'bn' ? 'অন্য রোল পরীক্ষা করুন' : 'Switch Demo Role' ?>
                </a>
                <form action="/logout" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-box-arrow-right me-1"></i> <?= ($locale ?? 'bn') === 'bn' ? 'লগআউট' : 'Logout' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Alert Notices -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php
                $msg = $_GET['msg'];
                if ($msg === 'task_started') echo ($locale ?? 'bn') === 'bn' ? 'কাজ শুরু করা হয়েছে।' : 'Task marked as in progress.';
                elseif ($msg === 'task_completed') echo ($locale ?? 'bn') === 'bn' ? 'মাঠ পর্যায়ের কাজ সম্পন্ন হয়েছে। সুপারভাইজারের যাচাইয়ের অপেক্ষায় রয়েছে।' : 'Work completed by field crew. Awaiting supervisor verification.';
                elseif ($msg === 'task_verified') echo ($locale ?? 'bn') === 'bn' ? 'সুপারভাইজার কর্তৃক কাজ যাচাই সম্পন্ন হয়েছে। নাগরিক সন্তুষ্টির জন্য পাঠানো হয়েছে।' : 'Task verified. Sent for citizen confirmation.';
                elseif ($msg === 'directive_issued') echo ($locale ?? 'bn') === 'bn' ? 'নির্বাহী নির্দেশনা সফলভাবে জারি করা হয়েছে।' : 'Executive directive issued successfully.';
                else echo e($msg);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Role-Specific Portal Content -->
    <?php
        $roleView = __DIR__ . '/roles/' . $primaryRole . '.php';
        if (file_exists($roleView)) {
            include $roleView;
        } else {
            include __DIR__ . '/roles/generic.php';
        }
    ?>
</div>
