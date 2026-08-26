<!-- CEO Operational Dashboard -->
<div class="row g-4 mb-4">
    <!-- Top KPIs -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'প্রধান নির্বাহী কর্মকর্তা — বিভাগীয় কর্মক্ষমতা ও তদারকি' : 'Chief Executive Officer — Operational & Departmental Oversight' ?>
            </h5>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-primary"><?= to_bn_number((string)($kpis['total_complaints'] ?? 0)) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মোট নাগরিক অভিযোগ' : 'Total Complaints' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-warning-emphasis"><?= to_bn_number((string)($kpis['in_progress'] ?? 0)) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মাঠে চলমান কাজ' : 'In Progress' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-danger-subtle rounded-3 text-center border border-danger-subtle">
                        <div class="fs-2 fw-bold text-danger"><?= to_bn_number((string)($kpis['overdue_count'] ?? 0)) ?></div>
                        <small class="text-danger fw-semibold"><?= ($locale ?? 'bn') === 'bn' ? 'সময়সীমা অতিক্রান্ত' : 'Overdue' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-success"><?= to_bn_number((string)($kpis['citizen_satisfaction_percent'] ?? 100)) ?>%</div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক সমাধান হার' : 'Satisfaction Rate' ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 9 Departments SLA Breakdown -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-buildings-fill text-info me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'সকল ৯টি বিভাগের বর্তমান অবস্থা' : 'All 9 Departments SLA & Workload Breakdown' ?>
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'বিভাগের নাম' : 'Department Name' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'মোট অভিযোগ' : 'Total Cases' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'চলমান কাজ' : 'In Progress' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'সময় পেরিয়েছে (Overdue)' : 'Overdue' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'কর্মক্ষমতা অবস্থা' : 'SLA Status' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($departments)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No department data available.</td></tr>
                        <?php else: ?>
                            <?php foreach ($departments as $d): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark">
                                        <?= ($locale ?? 'bn') === 'bn' ? e($d['name_bn']) : e($d['name_en']) ?>
                                    </td>
                                    <td class="text-center"><?= to_bn_number((string)($d['total_complaints'] ?? 0)) ?></td>
                                    <td class="text-center text-warning-emphasis fw-bold"><?= to_bn_number((string)($d['in_progress'] ?? 0)) ?></td>
                                    <td class="text-center text-danger fw-bold"><?= to_bn_number((string)($d['overdue_count'] ?? 0)) ?></td>
                                    <td class="text-end pe-3">
                                        <?php if (($d['overdue_count'] ?? 0) > 0): ?>
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1"><?= ($locale ?? 'bn') === 'bn' ? 'তদারকি প্রয়োজন' : 'Overdue Alert' ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success px-2 py-1"><?= ($locale ?? 'bn') === 'bn' ? 'স্বাভাবিক' : 'Normal' ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
