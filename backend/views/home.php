<div class="container">
    <!-- Hero Banner -->
    <div class="p-5 mb-4 bg-light rounded-4 border shadow-sm text-center">
        <div class="mb-3">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold">
                Phase 1 — Core Backend Foundation Ready
            </span>
        </div>
        <h1 class="display-5 fw-bold text-dark mb-3"><?= e(__('common.welcome')) ?></h1>
        <p class="lead text-muted col-lg-8 mx-auto mb-4">
            <?= e(__('common.app_slogan')) ?>
        </p>

        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="/api/v1/health" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-activity"></i>
                <span>API Health Endpoint (/api/v1/health)</span>
            </a>
            <a href="?lang=<?= ($locale ?? 'bn') === 'bn' ? 'en' : 'bn' ?>" class="btn btn-outline-secondary px-4 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-translate"></i>
                <span><?= ($locale ?? 'bn') === 'bn' ? 'Switch to English' : 'বাংলা ভাষায় পরিবর্তন করুন' ?></span>
            </a>
        </div>
    </div>

    <!-- HTMX Real-time Status Card Container -->
    <div id="status-card-container">
        <?= \AmarMayor\View\View::partial('partials/status_card', [
            'dbHealth' => $dbHealth,
            'redisHealth' => $redisHealth,
            'locale' => $locale,
            'timestamp' => date('H:i:s'),
            'requestId' => $requestId,
        ]) ?>
    </div>
</div>
