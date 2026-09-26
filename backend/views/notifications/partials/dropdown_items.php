<?php if (empty($notifications)): ?>
    <li class="p-3 text-center text-muted small">
        <i class="bi bi-bell-slash d-block fs-4 mb-1"></i>
        কোন নতুন বিজ্ঞপ্তি নেই
    </li>
<?php else: ?>
    <?php foreach ($notifications as $n): ?>
        <?php 
            $isUnread = empty($n['is_read']) || $n['is_read'] == 0;
            $bg = $isUnread ? 'bg-light' : '';
        ?>
        <li>
            <a class="dropdown-item py-2 border-bottom <?= $bg ?> text-wrap" href="/notifications">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="small text-dark"><?= e($n['title_bn'] ?? 'বিজ্ঞপ্তি') ?></strong>
                    <?php if ($isUnread): ?>
                        <span class="badge bg-primary" style="font-size: 0.6rem;">নতুন</span>
                    <?php endif; ?>
                </div>
                <div class="small text-muted text-truncate" style="max-width: 260px;">
                    <?= e($n['body_bn'] ?? '') ?>
                </div>
                <small class="text-muted" style="font-size: 0.7rem;">
                    <?= e($n['created_at']) ?>
                </small>
            </a>
        </li>
    <?php endforeach; ?>
<?php endif; ?>
<li class="p-2 text-center bg-light">
    <a href="/notifications" class="small fw-semibold text-primary text-decoration-none">
        সব নোটিফিকেশন দেখুন &rarr;
    </a>
</li>
