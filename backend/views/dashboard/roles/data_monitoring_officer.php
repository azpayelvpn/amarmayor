<!-- Data & Monitoring Officer Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-graph-up-arrow text-primary me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'ডাটা ও মনিটরিং কর্মকর্তা — অ্যানালিটিক্স ও হটস্পট বিশ্লেষণ' : 'Data & Monitoring Officer — Analytics & Hotspots' ?>
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold text-dark mb-3"><?= ($locale ?? 'bn') === 'bn' ? 'শীর্ষ অভিযোগপ্রবণ ওয়ার্ডসমূহ (Top Hotspots)' : 'Top Grievance Hotspots by Ward' ?></h6>
                        <ul class="list-group list-group-flush">
                            <?php if (empty($hotspots)): ?>
                                <li class="list-group-item bg-transparent text-muted">No hotspot data available.</li>
                            <?php else: ?>
                                <?php foreach ($hotspots as $h): ?>
                                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2">
                                        <span><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number((string)$h['ward_number']) : 'Ward #' . $h['ward_number'] ?></span>
                                        <span class="badge bg-danger rounded-pill"><?= to_bn_number((string)$h['complaint_count']) ?> <?= ($locale ?? 'bn') === 'bn' ? 'টি' : '' ?></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold text-dark mb-3"><?= ($locale ?? 'bn') === 'bn' ? 'সামগ্রিক সন্তুষ্টি ও সমাধান সূচক' : 'Overall Satisfaction & Quality Indices' ?></h6>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক সন্তুষ্টি হার' : 'Citizen Satisfaction' ?></span>
                                <strong><?= to_bn_number((string)($kpis['citizen_satisfaction_percent'] ?? 100)) ?>%</strong>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: <?= (float)($kpis['citizen_satisfaction_percent'] ?? 100) ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span><?= ($locale ?? 'bn') === 'bn' ? 'পুনরায় চালুর হার (Reopen Rate)' : 'Reopen Rate' ?></span>
                                <strong><?= to_bn_number((string)($kpis['reopen_rate_percent'] ?? 0)) ?>%</strong>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: <?= min(100, (float)($kpis['reopen_rate_percent'] ?? 0) * 5) ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
