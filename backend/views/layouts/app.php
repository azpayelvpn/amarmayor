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
    <!-- Main Public Header & Navigation -->
    <header class="navbar navbar-expand-lg navbar-civic sticky-top py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <div class="brand-badge">
                    <i class="bi bi-buildings fs-5"></i>
                </div>
                <div>
                    <div class="brand-title"><?= e(__('common.app_name')) ?></div>
                    <small class="text-muted d-block" style="font-size: 0.75rem;"><?= e(__('common.mcc_full_name')) ?></small>
                </div>
            </a>

            <!-- Mobile Hamburger Toggle -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#civicNavbar" aria-controls="civicNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="civicNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-2" href="/"><?= ($locale ?? 'bn') === 'bn' ? 'হোম' : 'Home' ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-2" href="/complaints/create"><?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ করুন' : 'Submit Complaint' ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-2" href="/track"><?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ ট্র্যাক করুন' : 'Track Complaint' ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-2" href="/wards"><?= ($locale ?? 'bn') === 'bn' ? 'আমার ওয়ার্ড' : 'My Ward' ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-2" href="/who-is-responsible"><?= ($locale ?? 'bn') === 'bn' ? 'দায়িত্বে কে?' : 'Who is Responsible?' ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-2" href="/notices"><?= ($locale ?? 'bn') === 'bn' ? 'নোটিশ' : 'Notices' ?></a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <!-- Auth Actions -->
                    <?php if (\AmarMayor\Auth\Auth::check()): ?>
                        <?php $user = \AmarMayor\Auth\Auth::user(); ?>
                        <?php if ($user && ($user->userType !== 'citizen' || count($user->getRoleSlugs()) > 1 || !$user->hasRole('citizen'))): ?>
                            <a href="/dashboard" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                                <i class="bi bi-speedometer2"></i>
                                <span><?= ($locale ?? 'bn') === 'bn' ? 'ড্যাশবোর্ড' : 'Dashboard' ?></span>
                            </a>
                        <?php else: ?>
                            <a href="/my-complaints" class="btn btn-sm btn-outline-success d-flex align-items-center gap-1">
                                <i class="bi bi-person-circle"></i>
                                <span><?= ($locale ?? 'bn') === 'bn' ? 'আমার অভিযোগ' : 'My Complaints' ?></span>
                            </a>
                        <?php endif; ?>
                        <form action="/logout" method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <?= ($locale ?? 'bn') === 'bn' ? 'লগআউট' : 'Logout' ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="/login" class="btn btn-sm btn-civic-primary d-flex align-items-center gap-1">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span><?= ($locale ?? 'bn') === 'bn' ? 'প্রবেশ' : 'Login' ?></span>
                        </a>
                    <?php endif; ?>

                    <!-- Language Switcher Toggle -->
                    <div class="btn-group btn-group-sm ms-2" role="group" aria-label="Language Selector">
                        <a href="?lang=bn" class="btn btn-outline-secondary <?= ($locale ?? 'bn') === 'bn' ? 'active' : '' ?>">বাংলা</a>
                        <a href="?lang=en" class="btn btn-outline-secondary <?= ($locale ?? 'bn') === 'en' ? 'active' : '' ?>">EN</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Body -->
    <main class="py-4">
        <?= $content ?? '' ?>
    </main>

    <!-- Public Footer -->
    <footer class="footer mt-auto py-4 bg-white border-top">
        <div class="container">
            <div class="row align-items-center gy-3">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-1 fw-bold text-dark"><?= e(__('common.mcc_full_name')) ?></p>
                    <p class="mb-0 text-muted small">
                        <?= ($locale ?? 'bn') === 'bn' ? 'জরুরি সেবা ও কন্ট্রোল রুম: যোগাযোগের তথ্য শীঘ্রই যোগ করা হবে' : 'Emergency Services & Control Room: Official contact details will be added soon' ?>
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-1 text-muted small">
                        &copy; <?= date('Y') ?> <?= e(__('common.all_rights_reserved')) ?> | <?= e(__('common.app_name')) ?>
                    </p>
                    <?php if (\AmarMayor\Support\Config::get('app.env') !== 'production'): ?>
                        <div class="small">
                            <span class="badge bg-warning text-dark me-1">DEV</span>
                            <a href="/dev/otp-inbox" class="text-decoration-underline text-muted me-2">OTP Inbox</a>
                            <a href="/dev/testing-access" class="text-decoration-underline text-muted">Demo Roles</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
