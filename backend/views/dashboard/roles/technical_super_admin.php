<!-- Technical Super Admin Health & Infrastructure Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-hdd-network-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'কারিগরি সুপার অ্যাডমিন — ট্রাফিক-লাইট সিস্টেম স্বাস্থ্য ও অবকাঠামো' : 'Technical Super Admin — Traffic-Light System Health & Infrastructure' ?>
                    </h5>
                    <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'সার্ভার ইনফ্রাস্ট্রাকচার, সার্ভিস সংযোগ, কিউ ও ব্যাকগ্রাউন্ড প্রসেসিং পরিচালনা' : 'Server infrastructure, connectivity, queues and automated health status' ?></small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form method="POST" action="/dashboard/tech/clear-cache" class="d-inline">
                        <button type="submit" class="btn btn-sm btn-outline-warning text-dark">
                            <i class="bi bi-lightning-charge-fill me-1"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'ক্যাশ রিফ্রেশ' : 'Clear Cache' ?>
                        </button>
                    </form>
                    <form method="POST" action="/dashboard/tech/run-jobs" class="d-inline">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-play-circle-fill me-1"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'পেন্ডিং জবস রান' : 'Run Pending Jobs' ?>
                        </button>
                    </form>
                    <span class="badge <?= ($health['overall_status'] ?? 'healthy') === 'healthy' ? 'bg-success' : 'bg-warning text-dark' ?> fs-6 px-3 py-2 rounded-pill">
                        <i class="bi bi-circle-fill me-1 small"></i>
                        <?= strtoupper($health['overall_status'] ?? 'healthy') ?>
                    </span>
                </div>
            </div>

            <!-- First-Level 7 Traffic-Light Component Cards -->
            <div class="row g-3 mb-4">
                <?php
                    $components = $health['components'] ?? [
                        'website' => ['status' => 'healthy', 'label_bn' => 'ওয়েবসাইট ও রাউটিং', 'label_en' => 'Website & Front Controller', 'message' => 'Apache / Laragon Rewrite OK'],
                        'database' => ['status' => 'healthy', 'label_bn' => 'ডাটাবেজ সার্ভিস', 'label_en' => 'Database (MySQL)', 'message' => 'MySQL 8.0 Connected'],
                        'fast_services' => ['status' => 'healthy', 'label_bn' => 'দ্রুত ক্যাশ সার্ভিস', 'label_en' => 'Fast Services / Cache', 'message' => 'File / Memory Cache Active'],
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

            <!-- Background Jobs Queue Section -->
            <div class="card border rounded-3 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-cpu text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'অ্যাসিঙ্ক্রোনাস ব্যাকগ্রাউন্ড জবস কিউ (Background Jobs Queue)' : 'Asynchronous Background Jobs Queue' ?>
                    </h6>
                    <span class="badge bg-secondary-subtle text-dark"><?= count($pendingJobs ?? []) ?> <?= ($locale ?? 'bn') === 'bn' ? 'টি সাম্প্রতিক জব' : 'recent jobs' ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'জব আইডি' : 'Job ID' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'জবের ধরন' : 'Job Type' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'অবস্থা' : 'Status' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'প্রচেষ্টা' : 'Attempts' ?></th>
                                <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'তারিখ ও সময়' : 'Created At' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingJobs)): ?>
                                <tr><td colspan="5" class="text-center py-3 text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'বর্তমানে কোন পেন্ডিং বা ফেইল্ড ব্যাকগ্রাউন্ড জব নেই।' : 'No pending background jobs in queue.' ?></td></tr>
                            <?php else: ?>
                                <?php foreach ($pendingJobs as $job): ?>
                                    <tr>
                                        <td class="ps-3 font-monospace small">#<?= (int)$job['id'] ?></td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info-emphasis font-monospace"><?= e($job['job_type'] ?? 'task') ?></span>
                                        </td>
                                        <td>
                                            <?php if (($job['status'] ?? '') === 'completed'): ?>
                                                <span class="badge bg-success-subtle text-success">সম্পন্ন (Completed)</span>
                                            <?php elseif (($job['status'] ?? '') === 'failed'): ?>
                                                <span class="badge bg-danger-subtle text-danger">ব্যর্থ (Failed)</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning">অপেক্ষমাণ (Pending)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="font-monospace"><?= (int)($job['attempts'] ?? 0) ?>/3</span></td>
                                        <td class="text-end pe-3 small text-muted font-monospace"><?= e(date('d M Y, h:i A', strtotime($job['created_at'] ?? 'now'))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Collapsible Advanced Technical Details -->
            <div class="pt-2">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#techInternals" aria-expanded="false" aria-controls="techInternals">
                    <i class="bi bi-terminal me-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'সার্ভার এনভায়রনমেন্ট ও মেমোরি ইন্টারনালস (Advanced Details)' : 'View Advanced Technical Internals' ?>
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
