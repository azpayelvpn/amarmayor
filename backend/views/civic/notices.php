<div class="container py-4">
    <!-- Header -->
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-bell-fill text-warning me-2"></i>
            <?= ($locale ?? 'bn') === 'bn' ? 'পৌর নোটিশ ও ঘোষণা' : 'City Notices & Announcements' ?>
        </h3>
        <p class="text-muted small mb-0">
            <?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশনের নাগরিক সেবা সংক্রান্ত সর্বশেষ নোটিশ ও জরুরি বিজ্ঞপ্তি।' : 'Latest municipal notices, public service advisories, and official announcements.' ?>
        </p>
    </div>

    <?php if (empty($notices)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <div class="rounded-circle bg-light text-muted mx-auto d-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;font-size:2rem;">
                <i class="bi bi-megaphone"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2"><?= ($locale ?? 'bn') === 'bn' ? 'এই মুহূর্তে কোনো নতুন নোটিশ নেই' : 'No Active Notices' ?></h5>
            <p class="text-muted small mb-0"><?= ($locale ?? 'bn') === 'bn' ? 'নতুন কোনো গণবিজ্ঞপ্তি প্রকাশিত হলে এখানে প্রদর্শিত হবে।' : 'New public notices and announcements will appear here.' ?></p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($notices as $n): ?>
                <div class="col-12 col-md-6">
                    <div class="card border shadow-sm rounded-4 p-4 h-100 bg-white">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-primary-subtle text-primary px-2 py-1 small fw-semibold">
                                <?= ($locale ?? 'bn') === 'bn' ? 'গণবিজ্ঞপ্তি' : 'Notice' ?>
                            </span>
                            <small class="text-muted"><?= format_bn_date((string)($n['created_at'] ?? 'now')) ?></small>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">
                            <?= ($locale ?? 'bn') === 'bn' ? e($n['title_bn'] ?? '') : e($n['title_en'] ?? '') ?>
                        </h5>
                        <p class="text-muted small mb-0">
                            <?= nl2br(e(($locale ?? 'bn') === 'bn' ? ($n['body_bn'] ?? '') : ($n['body_en'] ?? ''))) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
