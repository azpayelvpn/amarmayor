<!-- Administrator Governance Command Center -->
<div class="row g-4 mb-4">
    <!-- Top Executive KPIs -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-shield-check text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'প্রশাসক মহোদয়ের সার্বিক প্রশাসনিক তদারকি কমান্ড সেন্টার' : 'City Administrator Executive Governance Command Center' ?>
                    </h5>
                    <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশনের ৩৩টি ওয়ার্ড, ৩টি অঞ্চল ও প্রশাসনিক কার্যক্রম তদারকি' : 'Administrative oversight across 33 wards, 3 zones and civic operations' ?></small>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill"><?= ($locale ?? 'bn') === 'bn' ? 'সিটি প্রশাসক' : 'City Administrator' ?></span>
                    <a href="/dashboard/reports" class="btn btn-sm btn-outline-primary fw-semibold">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'পূর্ণাঙ্গ রিপোর্ট' : 'Full Reports' ?>
                    </a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-primary"><?= to_bn_number((string)($kpis['total_complaints'] ?? 0)) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মোট নাগরিক সমস্যা' : 'Total Complaints' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-warning-emphasis"><?= to_bn_number((string)($kpis['in_progress'] ?? 0)) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মাঠে চলমান সমাধান' : 'In Progress' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-danger-subtle rounded-3 text-center border border-danger-subtle">
                        <div class="fs-2 fw-bold text-danger"><?= to_bn_number((string)($kpis['overdue_count'] ?? 0)) ?></div>
                        <small class="text-danger fw-semibold"><?= ($locale ?? 'bn') === 'bn' ? 'সময় অতিক্রান্ত (Red Alert)' : 'Overdue' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-success"><?= to_bn_number((string)($kpis['citizen_satisfaction_percent'] ?? 100)) ?>%</div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক সন্তুষ্টি হার' : 'Satisfaction Rate' ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 Zones Executive Progress -->
    <?php if (!empty($zonalProgress)): ?>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-pin-map-fill text-danger me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? '৩টি প্রশাসনিক অঞ্চলের অগ্রগতি' : 'Zonal Performance Overview' ?>
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>অঞ্চল</th>
                                <th class="text-center">মোট কেস</th>
                                <th class="text-center">সমাধান</th>
                                <th class="text-center">মেয়াদোত্তীর্ণ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($zonalProgress as $zp): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?= e($zp['name_bn']) ?></td>
                                    <td class="text-center"><?= to_bn_number((string)$zp['total']) ?></td>
                                    <td class="text-center text-success fw-bold"><?= to_bn_number((string)$zp['resolved']) ?></td>
                                    <td class="text-center text-danger fw-bold"><?= to_bn_number((string)$zp['overdue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Attention Items & Directives -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-exclamation-octagon-fill text-danger me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'জরুরি প্রশাসনিক দৃষ্টি আকর্ষণ কিউ' : 'Executive Attention Queue' ?>
                </h6>
                <span class="badge bg-danger rounded-pill"><?= count($attentionQueue ?? []) ?> টি কেস</span>
            </div>
            <?php if (empty($attentionQueue)): ?>
                <div class="text-center py-4 text-muted">বর্তমানে কোনো জরুরি রেড অ্যালার্ট নেই। সকল কার্যক্রম স্বাভাবিক।</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($attentionQueue, 0, 5) as $aq): ?>
                        <div class="list-group-item px-0 py-2 border-0 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <span class="font-monospace fw-bold text-dark me-2">#<?= e($aq['public_complaint_number'] ?? $aq['complaint_id']) ?></span>
                                <span class="badge bg-danger-subtle text-danger"><?= e($aq['trigger_type']) ?></span>
                                <div class="small text-muted mt-1"><?= e($aq['description'] ?? 'জরুরি নাগরিক সমস্যা') ?></div>
                            </div>
                            <a href="/track/<?= urlencode($aq['public_complaint_number'] ?? '') ?>" class="btn btn-sm btn-outline-danger">তদন্ত</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Responsible Officers Directory in Wards -->
    <?php if (!empty($responsibleOfficers)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-person-badge-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ডভিত্তিক সরকারি দায়িত্বপ্রাপ্ত কর্মকর্তা ও রূপরেখা' : 'Ward Responsible Officers & Governance Mapping' ?>
                    </h6>
                    <small class="text-muted">মোট ৩৩টি ওয়ার্ডে নিযুক্ত কর্মকর্তা</small>
                </div>
                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3">ওয়ার্ড নং</th>
                                <th>দায়িত্বপ্রাপ্ত কর্মকর্তা</th>
                                <th>পদবি</th>
                                <th>সরকারি মোবাইল নম্বর</th>
                                <th class="text-end pe-3">কাজের ধরন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($responsibleOfficers as $ro): ?>
                                <tr>
                                    <td class="ps-3 fw-bold">ওয়ার্ড <?= to_bn_number((string)$ro['ward_number']) ?></td>
                                    <td><strong><?= e($ro['full_name_bn']) ?></strong></td>
                                    <td class="small text-muted"><?= e($ro['designation_bn']) ?></td>
                                    <td class="font-monospace"><?= e($ro['phone']) ?></td>
                                    <td class="text-end pe-3">
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1"><?= e($ro['responsibility_type']) ?></span>
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
                                <th class="text-end">প্রশাসকের নির্দেশ জারি</th>
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
