<!-- Mayor / Administrator Executive Command Center -->
<div class="row g-4 mb-4">
    <!-- 6 Executive KPIs -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-speedometer2 text-primary me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'শীর্ষ নির্বাহী সূচক (Executive KPIs)' : 'Executive Key Performance Indicators' ?>
            </h5>
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
                                <strong class="text-dark font-monospace"><?= e($item['public_complaint_number'] ?? $item['tracking_number'] ?? '') ?></strong>
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
</div>
