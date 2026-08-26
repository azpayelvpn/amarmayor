<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-shield-check text-success"></i>
            <?= ($locale ?? 'bn') === 'bn' ? 'সিস্টেম আর্কিটেকচার ও সংযোগ স্থিতি' : 'System Architecture & Connectivity Status' ?>
        </h5>
        <span class="badge bg-secondary">
            <?= ($locale ?? 'bn') === 'bn' ? 'আপডেট:' : 'Updated:' ?> <?= e($timestamp ?? date('H:i:s')) ?>
        </span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 border rounded bg-light">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-database <?= ($dbHealth['connected'] ?? false) ? 'text-success' : 'text-danger' ?> fs-4"></i>
                        <h6 class="mb-0 fw-bold"><?= ($locale ?? 'bn') === 'bn' ? 'মূল ডেটাবেজ (MySQL 8+)' : 'Core Database (MySQL 8+)' ?></h6>
                    </div>
                    <span class="badge <?= ($dbHealth['connected'] ?? false) ? 'bg-success' : 'bg-danger' ?>">
                        <?= ($dbHealth['connected'] ?? false) ? 'সচল (Connected)' : 'বিচ্ছিন্ন (Disconnected)' ?>
                    </span>
                    <small class="text-muted d-block mt-1">Version: <?= e($dbHealth['version'] ?? 'N/A') ?></small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 border rounded bg-light">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-lightning-charge <?= ($redisHealth['connected'] ?? false) ? 'text-success' : 'text-warning' ?> fs-4"></i>
                        <h6 class="mb-0 fw-bold"><?= ($locale ?? 'bn') === 'bn' ? 'ফাস্ট ক্যাশ ও সেশন (Redis)' : 'Fast Cache & Sessions (Redis)' ?></h6>
                    </div>
                    <span class="badge <?= ($redisHealth['connected'] ?? false) ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= ($redisHealth['connected'] ?? false) ? 'সচল (Connected)' : 'অফলাইন ফলব্যাক (Memory Fallback)' ?>
                    </span>
                    <small class="text-muted d-block mt-1">Driver: <?= e($redisHealth['driver'] ?? 'memory_fallback') ?></small>
                </div>
            </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">Request ID: <code><?= e($requestId ?? 'sys-anon') ?></code></small>
            <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                    hx-get="/htmx/status-check"
                    hx-target="#status-card-container"
                    hx-swap="innerHTML">
                <i class="bi bi-arrow-clockwise"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'HTMX দিয়ে রিফ্রেশ করুন' : 'Refresh via HTMX' ?>
            </button>
        </div>
    </div>
</div>
