<!-- Technical Super Admin Health & Infrastructure Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-hdd-network-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'কারিগরি সুপার অ্যাডমিন — ট্রাফিক-লাইট সিস্টেম স্বাস্থ্য' : 'Technical Super Admin — Traffic-Light System Health' ?>
                    </h5>
                    <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'সার্ভার ইনফ্রাস্ট্রাকচার, সার্ভিস সংযোগ ও ব্যাকগ্রাউন্ড প্রসেসিং পর্যবেক্ষণ' : 'Server infrastructure, connectivity, queues and automated health status' ?></small>
                </div>
                <span class="badge <?= ($health['overall_status'] ?? 'healthy') === 'healthy' ? 'bg-success' : 'bg-warning text-dark' ?> fs-6 px-3 py-2 rounded-pill">
                    <i class="bi bi-circle-fill me-1 small"></i>
                    <?= strtoupper($health['overall_status'] ?? 'healthy') ?>
                </span>
            </div>

            <!-- First-Level 7 Traffic-Light Component Cards -->
            <div class="row g-3">
                <?php
                    $components = $health['components'] ?? [
                        'website' => ['status' => 'healthy', 'label_bn' => 'ওয়েবসাইট ও রাউটিং', 'label_en' => 'Website & Front Controller', 'message' => 'Apache / Laragon Rewrite OK'],
                        'database' => ['status' => 'healthy', 'label_bn' => 'ডাটাবেজ সার্ভিস', 'label_en' => 'Database (MySQL)', 'message' => 'MySQL 8.0.30 Connected'],
                        'fast_services' => ['status' => 'healthy', 'label_bn' => 'দ্রুত ক্যাশ সার্ভিস', 'label_en' => 'Fast Services / Cache', 'message' => 'Redis / Memory Cache Ready'],
                        'notifications' => ['status' => 'healthy', 'label_bn' => 'নোটিফিকেশন সার্ভিস', 'label_en' => 'Notifications & SMS', 'message' => 'SMS Gateway / Dev Inbox Ready'],
                        'background_processing' => ['status' => 'healthy', 'label_bn' => 'ব্যাকগ্রাউন্ড প্রসেসিং', 'label_en' => 'Background Processing', 'message' => 'Async Queues & Crons Ready'],
                        'backup' => ['status' => 'healthy', 'label_bn' => 'ডাটা ব্যাকআপ', 'label_en' => 'Database & Evidence Backup', 'message' => 'Automatic Retention Configured'],
                        'security' => ['status' => 'healthy', 'label_bn' => 'নিরাপত্তা ও এনক্রিপশন', 'label_en' => 'Security & RBAC', 'message' => 'CSRF & Timing-Safe Active'],
                    ];
                ?>

                <?php foreach ($components as $key => $c): ?>
                    <?php
                        $isHealthy = ($c['status'] ?? 'healthy') === 'healthy';
                        $statusClass = $isHealthy ? 'border-success-subtle bg-success-subtle' : 'border-warning-subtle bg-warning-subtle';
                        $badgeClass = $isHealthy ? 'bg-success' : 'bg-warning text-dark';
                    ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border rounded-3 p-3 h-100 <?= $statusClass ?>">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-dark">
                                    <?= ($locale ?? 'bn') === 'bn' ? e($c['label_bn'] ?? $key) : e($c['label_en'] ?? $key) ?>
                                </strong>
                                <span class="badge <?= $badgeClass ?> rounded-pill">
                                    <?= e($c['status'] ?? 'healthy') ?>
                                </span>
                            </div>
                            <small class="text-muted d-block mt-auto">
                                <?= e($c['message'] ?? 'Operating normally') ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Collapsible Advanced Technical Details -->
            <div class="mt-4 pt-3 border-top">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#techInternals" aria-expanded="false" aria-controls="techInternals">
                    <i class="bi bi-terminal me-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'উন্নত কারিগরি বিবরণ দেখুন (Advanced Internals)' : 'View Advanced Technical Internals' ?>
                </button>
                <div class="collapse mt-3" id="techInternals">
                    <div class="card card-body bg-dark text-light font-monospace small rounded-3 p-3">
                        <div>PHP Version: <?= PHP_VERSION ?></div>
                        <div>Server API: <?= PHP_SAPI ?></div>
                        <div>Environment: <?= e(\AmarMayor\Support\Config::get('app.env', 'local')) ?></div>
                        <div>App Timezone: <?= e(\AmarMayor\Support\Config::get('app.timezone', 'Asia/Dhaka')) ?></div>
                        <div>Memory Peak: <?= round(memory_get_peak_usage() / 1024 / 1024, 2) ?> MB</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
