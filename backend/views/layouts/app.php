<!DOCTYPE html>
<html lang="<?= e($locale ?? 'bn') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(__('common.app_name')) ?> — <?= e(__('common.mcc_full_name')) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Bengali & Sans Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Application CSS -->
    <link href="/assets/css/app.css" rel="stylesheet">

    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
</head>
<body>
    <!-- Main Header & Navigation -->
    <header class="navbar navbar-expand-lg bg-white border-bottom sticky-top py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <div class="brand-badge bg-primary text-white rounded d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                    <i class="bi bi-buildings fs-5"></i>
                </div>
                <div>
                    <div class="brand-title fw-bold text-dark lh-1"><?= e(__('common.app_name')) ?></div>
                    <small class="text-muted d-block" style="font-size: 0.75rem;"><?= e(__('common.mcc_full_name')) ?></small>
                </div>
            </a>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <!-- Language Switcher Toggle -->
                <div class="btn-group btn-group-sm" role="group" aria-label="Language Selector">
                    <a href="?lang=bn" class="btn btn-outline-primary <?= ($locale ?? 'bn') === 'bn' ? 'active' : '' ?>">বাংলা</a>
                    <a href="?lang=en" class="btn btn-outline-primary <?= ($locale ?? 'bn') === 'en' ? 'active' : '' ?>">EN</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Body -->
    <main class="py-4">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto py-4 bg-light border-top text-center text-muted">
        <div class="container">
            <p class="mb-1 fw-semibold text-dark"><?= e(__('common.mcc_full_name')) ?></p>
            <p class="mb-0 small">&copy; <?= date('Y') ?> <?= e(__('common.all_rights_reserved')) ?> | <?= e(__('common.app_slogan')) ?></p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
