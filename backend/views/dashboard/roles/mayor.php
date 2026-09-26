<!-- Mayor / Administrator Executive Command Center -->
<div class="row g-4 mb-4">
    <!-- 6 Executive KPIs -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-speedometer2 text-primary me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'শীর্ষ নির্বাহী সূচক (Executive KPIs)' : 'Executive Key Performance Indicators' ?>
                </h5>
                <a href="/dashboard/reports" class="btn btn-primary fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-bar-graph-fill me-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'পৌর রিপোর্ট তৈরি ও প্রিন্ট করুন' : 'Generate Civic Reports' ?>
                </a>
            </div>
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-primary"><?= to_bn_number((string)($kpis['total_complaints'] ?? 0)) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মোট অভিযোগ' : 'Total Complaints' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-warning-emphasis"><?= to_bn_number((string)($kpis['in_progress'] ?? 0)) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'কাজ চলছে' : 'In Progress' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="p-3 bg-danger-subtle rounded-3 text-center border border-danger-subtle">
                        <div class="fs-2 fw-bold text-danger"><?= to_bn_number((string)($kpis['overdue_count'] ?? 0)) ?></div>
                        <small class="text-danger fw-semibold"><?= ($locale ?? 'bn') === 'bn' ? 'সময় পেরিয়েছে' : 'Overdue' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-secondary">
                            <?= ($kpis['total_complaints'] ?? 0) > 0 ? to_bn_number((string)($kpis['reopen_rate_percent'] ?? 0)) . '%' : '-' ?>
                        </div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'পুনরায় খোলা হার' : 'Reopen Rate' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-success">
                            <?php if (isset($kpis['citizen_satisfaction_percent']) && $kpis['citizen_satisfaction_percent'] !== null && ($kpis['total_feedback'] ?? 0) > 0): ?>
                                <?= to_bn_number((string)$kpis['citizen_satisfaction_percent']) ?>%
                            <?php else: ?>
                                <span class="fs-6 text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'পর্যাপ্ত তথ্য নেই' : 'Insufficient Data' ?></span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক সন্তুষ্টি' : 'Citizen Satisfaction' ?></small>
                        <?php if (($kpis['total_feedback'] ?? 0) > 0): ?>
                            <small class="text-muted" style="font-size: 0.7rem;">(<?= to_bn_number((string)$kpis['total_feedback']) ?> <?= ($locale ?? 'bn') === 'bn' ? 'মতামত' : 'reviews' ?>)</small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-dark">
                            <?= ($kpis['avg_resolution_hours'] ?? 0) > 0 ? to_bn_number((string)($kpis['avg_resolution_hours'] ?? 0)) . 'h' : '-' ?>
                        </div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'গড় সময়' : 'Avg Hours' ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 24-hour Daily Brief -->
    <div class="col-md-5">
        <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-clock-history text-info me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'বিগত ২৪ ঘণ্টার সারসংক্ষেপ' : '24-Hour Daily Brief' ?>
            </h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span><?= ($locale ?? 'bn') === 'bn' ? 'নতুন অভিযোগ দাখিল' : 'New Complaints Submitted' ?></span>
                    <span class="badge bg-primary rounded-pill"><?= to_bn_number((string)($dailyBrief['new_complaints_24h'] ?? 0)) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span><?= ($locale ?? 'bn') === 'bn' ? 'সমাধান ও নিষ্পত্তি' : 'Resolved & Closed' ?></span>
                    <span class="badge bg-success rounded-pill"><?= to_bn_number((string)($dailyBrief['resolved_24h'] ?? 0)) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span><?= ($locale ?? 'bn') === 'bn' ? 'সময়সীমা লঙ্ঘন (Overdue)' : 'Overdue Breaches' ?></span>
                    <span class="badge bg-danger rounded-pill"><?= to_bn_number((string)($dailyBrief['overdue_breaches_24h'] ?? 0)) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক কর্তৃক পুনরায় চালু' : 'Reopened Cases' ?></span>
                    <span class="badge bg-warning text-dark rounded-pill"><?= to_bn_number((string)($dailyBrief['reopened_24h'] ?? 0)) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <span><?= ($locale ?? 'bn') === 'bn' ? 'জারি করা নির্বাহী নির্দেশনা' : 'Directives Issued' ?></span>
                    <span class="badge bg-secondary rounded-pill"><?= to_bn_number((string)($dailyBrief['directives_issued_24h'] ?? 0)) ?></span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Executive Attention Required Queue -->
    <div class="col-md-7">
        <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'জরুরি দৃষ্টি আকর্ষণ (Attention Required)' : 'Attention Required Queue' ?>
                </h5>
                <span class="badge bg-danger"><?= count($attentionQueue ?? []) ?></span>
            </div>

            <?php if (empty($attentionQueue)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check2-all fs-1 text-success d-block mb-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'বর্তমানে কোনো জরুরি অভিযোগ নেই।' : 'No complaints currently require executive attention.' ?>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($attentionQueue, 0, 5) as $item): ?>
                        <div class="list-group-item px-0 py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong class="text-dark font-monospace">
                                    <a href="/dashboard/complaints/<?= urlencode($item['public_complaint_number'] ?? $item['tracking_number'] ?? '') ?>"
                                       class="text-primary text-decoration-none fw-bold">
                                        <?= e($item['public_complaint_number'] ?? $item['tracking_number'] ?? '') ?>
                                        <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                    </a>
                                </strong>
                                <span class="badge bg-danger"><?= e(human_status($item['trigger_type'] ?? 'overdue', $locale)) ?></span>
                            </div>
                            <p class="mb-2 small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? e($item['subcategory_name_bn'] ?? '') : e($item['subcategory_name_en'] ?? '') ?>
                                &bull; <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)($item['ward_number'] ?? '')) : 'Ward ' . ($item['ward_number'] ?? '') ?>
                            </p>
                            <!-- Executive Directive Form -->
                            <form action="/dashboard/executive/directive" method="POST" class="d-flex gap-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="complaint_id" value="<?= (int)($item['complaint_id'] ?? $item['id']) ?>">
                                <input type="text" name="instruction" required placeholder="<?= ($locale ?? 'bn') === 'bn' ? 'নির্দেশনা লিখুন...' : 'Enter directive...' ?>" class="form-control form-control-sm">
                                <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'নির্দেশ দিন' : 'Issue Directive' ?>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Inter-Department Support Breakdown Alerts (if any) -->
    <?php if (!empty($supportBottlenecks)): ?>
        <div class="col-12">
            <div class="card border-danger shadow-sm rounded-4 bg-white p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-danger mb-0">
                        <i class="bi bi-shield-slash-fill me-2"></i>
                        আন্তঃবিভাগীয় সমন্বয় সংকট ও সরঞ্জাম ডেলিভারি ব্যর্থতা
                    </h5>
                    <span class="badge bg-danger rounded-pill"><?= count($supportBottlenecks) ?> টি সংকট</span>
                </div>
                <p class="text-muted small mb-3">অনুমোদনের পরও ওয়ার্ডে সরঞ্জাম পৌঁছায়নি অথবা ডেলিভারি ব্যর্থ হয়েছে বলে সুপারভাইজার রিপোর্ট করেছেন:</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-danger">
                            <tr>
                                <th>অভিযোগ নং ও ওয়ার্ড</th>
                                <th>টার্গেট বিভাগ</th>
                                <th>অনুরোধকৃত সরঞ্জাম</th>
                                <th>ব্যর্থতার কারণ / অবস্থা</th>
                                <th class="text-end">তাত্ক্ষণিক ব্যবস্থা</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($supportBottlenecks as $sb): ?>
                                <tr>
                                    <td>
                                        <a href="/dashboard/complaints/<?= urlencode($sb['public_complaint_number']) ?>"
                                           class="font-monospace fw-bold text-danger text-decoration-none">
                                            <?= e($sb['public_complaint_number']) ?>
                                            <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                        </a>
                                        <small class="text-muted d-block">ওয়ার্ড <?= to_bn_number((string)($sb['ward_number'] ?? '')) ?></small>
                                    </td>
                                    <td><strong><?= e($sb['target_dept_name'] ?? 'সংশ্লিষ্ট বিভাগ') ?></strong></td>
                                    <td>
                                        <div><?= e($sb['allocated_resource'] ?? $sb['support_type']) ?></div>
                                        <small class="text-muted"><?= e($sb['allocated_operator'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">ডেলিভারি ব্যর্থ</span>
                                        <div class="small text-danger fw-semibold mt-1"><?= e($sb['receipt_notes'] ?? 'মাঠে নির্ধারিত সময়ে পৌঁছায়নি') ?></div>
                                    </td>
                                    <td class="text-end">
                                        <form action="/dashboard/executive/directive" method="POST" class="d-inline-flex gap-2">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="complaint_id" value="<?= (int)$sb['complaint_id'] ?>">
                                            <input type="text" name="instruction" required placeholder="বিভাগীয় প্রধানকে নির্দেশ দিন..." class="form-control form-control-sm" style="max-width: 220px;">
                                            <button type="submit" class="btn btn-sm btn-danger text-nowrap">
                                                <i class="bi bi-lightning-fill me-1"></i>জরুরি নির্দেশ
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- Ward Manpower Shortage Red Alerts (if any) -->
    <?php if (!empty($manpowerShortageAlerts)): ?>
        <div class="col-12">
            <div class="card border-danger border-2 shadow-sm rounded-4 bg-white p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger p-2 rounded-circle fs-6"><i class="bi bi-people-fill text-white"></i></span>
                        <div>
                            <h5 class="fw-bold text-danger mb-0">
                                <?= ($locale ?? 'bn') === 'bn' ? '🚨 ওয়ার্ডে জরুরি পরিচ্ছন্নতাকর্মী সংকট (Workforce Shortage Red Alert)' : '🚨 Ward Workforce Shortage Red Alert' ?>
                            </h5>
                            <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মাঠ পর্যায়ে পর্যাপ্ত পরিচ্ছন্নতাকর্মী না থাকায় ওয়ার্ডে আবর্জনা পরিষ্কার কাজ বিলম্বিত হচ্ছে' : 'Sanitation work delayed due to cleaner shortage in wards' ?></small>
                        </div>
                    </div>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-bold">
                        <?= to_bn_number((string)count($manpowerShortageAlerts)) ?> <?= ($locale ?? 'bn') === 'bn' ? 'টি ওয়ার্ডে কর্মী সংকট' : 'ward shortages' ?>
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-danger">
                            <tr>
                                <th>ওয়ার্ড ও অভিযোগ নং</th>
                                <th>অনুরোধকারী সুপারভাইজার</th>
                                <th>চাহিত অতিরিক্ত কর্মী</th>
                                <th>বর্তমান অবস্থা</th>
                                <th class="text-end">মেয়রের নির্দেশ জারি</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($manpowerShortageAlerts as $msa): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">ওয়ার্ড <?= to_bn_number((string)($msa['ward_number'] ?? '')) ?></div>
                                        <small class="font-monospace text-muted"><?= e($msa['public_complaint_number']) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= e($msa['requester_name'] ?? 'ওয়ার্ড সুপারভাইজার') ?></div>
                                        <small class="text-muted font-monospace"><?= e($msa['requester_phone'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger fs-6">
                                            <i class="bi bi-person-fill-exclamation me-1"></i><?= to_bn_number((string)($msa['requested_worker_count'] ?? 0)) ?> জন কর্মী
                                        </span>
                                        <?php if (!empty($msa['description'])): ?>
                                            <div class="small text-muted mt-1"><?= e($msa['description']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($msa['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark fw-bold">বিভাগীয় মঞ্জুরি অপেক্ষমাণ</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">ডেলিভারি ব্যর্থ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <form action="/dashboard/executive/directive" method="POST" class="d-inline-flex gap-2">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="complaint_id" value="<?= (int)$msa['complaint_id'] ?>">
                                            <input type="text" name="instruction" required placeholder="প্রধান বর্জ্য কর্মকর্তাকে তাৎক্ষণিক অতিরিক্ত কর্মী প্রেরণের নির্দেশ..." class="form-control form-control-sm" style="min-width: 250px;">
                                            <button type="submit" class="btn btn-sm btn-danger text-nowrap">
                                                <i class="bi bi-lightning-fill me-1"></i>নির্দেশ জারি
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
