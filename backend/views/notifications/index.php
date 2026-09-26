<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border-bottom pb-3">
        <div>
            <h2 class="h4 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-bell-fill text-primary"></i>
                <span>অভ্যন্তরীণ নোটিফিকেশন ও অ্যালার্ট কেন্দ্র</span>
            </h2>
            <p class="text-muted small mb-0">মেয়র মহোদয়ের নির্দেশনা, জরুরি অ্যালার্ট, আন্তঃবিভাগীয় সমন্বয় ও মাঠ পর্যায়ের আপডেট</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
                <span class="badge bg-danger rounded-pill px-3 py-2">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    <?= \AmarMayor\Support\Translator::toBanglaNumeral($unreadCount) ?> টি অপঠিত
                </span>
                <form action="/notifications/read-all" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-check2-all me-1"></i>সব পঠিত হিসেবে চিহ্নিত করুন
                    </button>
                </form>
            <?php else: ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                    <i class="bi bi-check-circle me-1"></i>সব নোটিফিকেশন পঠিত
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="card shadow-sm border-0 py-5 text-center">
            <div class="card-body">
                <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                <h5 class="fw-semibold mt-3 text-secondary">কোন নোটিফিকেশন নেই</h5>
                <p class="text-muted small">আপনার দপ্তরে এই মুহূর্তে কোন নতুন বার্তা বা নোটিফিকেশন অপেক্ষমান নেই।</p>
                <a href="/dashboard" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="bi bi-arrow-left me-1"></i>ড্যাশবোর্ডে ফিরে যান
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                    <?php 
                        $isUnread = empty($n['is_read']) || $n['is_read'] == 0;
                        $bgClass = $isUnread ? 'bg-primary-subtle bg-opacity-25' : '';
                        $type = $n['notification_type'] ?? 'system';
                        
                        $icon = 'bi-bell';
                        $iconColor = 'text-primary';
                        if (str_contains($type, 'directive') || str_contains($type, 'executive')) {
                            $icon = 'bi-shield-fill-exclamation';
                            $iconColor = 'text-danger';
                        } elseif (str_contains($type, 'support') || str_contains($type, 'cross')) {
                            $icon = 'bi-diagram-3-fill';
                            $iconColor = 'text-purple';
                        } elseif (str_contains($type, 'task')) {
                            $icon = 'bi-tools';
                            $iconColor = 'text-info';
                        } elseif (str_contains($type, 'resolved') || str_contains($type, 'verified')) {
                            $icon = 'bi-check-circle-fill';
                            $iconColor = 'text-success';
                        }
                    ?>
                    <div class="list-group-item p-3 <?= $bgClass ?> d-flex align-items-start justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3 flex-grow-1">
                            <div class="fs-4 <?= $iconColor ?> mt-1">
                                <i class="bi <?= $icon ?>"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <h6 class="fw-bold mb-0 <?= $isUnread ? 'text-dark' : 'text-secondary' ?>">
                                        <?= e($n['title_bn'] ?? $n['title_en'] ?? 'বিজ্ঞপ্তি') ?>
                                    </h6>
                                    <?php if ($isUnread): ?>
                                        <span class="badge bg-primary rounded-pill small" style="font-size: 0.65rem;">নতুন</span>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i><?= e($n['created_at']) ?>
                                    </small>
                                </div>
                                <p class="mb-1 text-secondary small" style="line-height: 1.5;">
                                    <?= nl2br(e($n['body_bn'] ?? $n['body_en'] ?? '')) ?>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <?php if ($isUnread): ?>
                                <form action="/notifications/<?= (int)$n['id'] ?>/read" method="POST">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-light border text-muted" title="পঠিত চিহ্নিত করুন">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
