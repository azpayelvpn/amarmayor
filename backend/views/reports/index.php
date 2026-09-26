<div class="container-fluid py-4">
    <!-- Header with Action Buttons -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary px-2 py-1 rounded-pill">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'অফিসিয়াল রিপোর্ট' : 'Official Report' ?>
                </span>
                <small class="text-muted"><?= e($periodLabelBn) ?></small>
            </div>
            <h3 class="fw-bold text-dark mb-0">
                <?= ($locale ?? 'bn') === 'bn' ? 'পৌর সেবা ও অভিযোগ রিপোর্ট কেন্দ্র' : 'Civic Service & Grievance Report Center' ?>
            </h3>
        </div>

        <div class="d-flex align-items-center gap-2">
            <?php
                $queryString = http_build_query(array_filter([
                    'period' => $period,
                    'year' => $year,
                    'month' => $month,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'zone_id' => $zoneId,
                    'ward_id' => $wardId,
                    'category_id' => $categoryId,
                    'supervisor_id' => $supervisorId,
                    'status' => $status,
                ], fn($v) => $v !== '' && $v !== null && $v !== 'all'));
            ?>
            <!-- Print Button -->
            <a href="/dashboard/reports/print?<?= $queryString ?>" target="_blank" class="btn btn-outline-dark fw-semibold shadow-sm">
                <i class="bi bi-printer-fill me-1 text-primary"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'মিটিংয়ের জন্য প্রিন্ট করুন' : 'Print for Meeting' ?>
            </a>

            <!-- Excel Export Button -->
            <a href="/dashboard/reports/export?<?= $queryString ?>" class="btn btn-success fw-semibold shadow-sm">
                <i class="bi bi-file-earmark-excel-fill me-1"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'এক্সেল ডাউনলোড' : 'Export Excel' ?>
            </a>

            <a href="/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'ড্যাশবোর্ড' : 'Dashboard' ?>
            </a>
        </div>
    </div>

    <!-- Filter Form Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
        <div class="card-body p-3 p-md-4">
            <form action="/dashboard/reports" method="GET">
                <!-- Filter Row 1: Time, Year, Month, Dates -->
                <div class="row g-3 align-items-end mb-3">
                    <!-- 1. Time Period Preset -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="bi bi-calendar3 me-1 text-primary"></i><?= ($locale ?? 'bn') === 'bn' ? 'সময়কাল নির্বাচন' : 'Time Period' ?>
                        </label>
                        <select name="period" class="form-select border bg-white" id="period-select" onchange="toggleCustomDates(this.value)">
                            <option value="all" <?= $period === 'all' ? 'selected' : '' ?>><?= ($locale ?? 'bn') === 'bn' ? 'সকল সময়ের তথ্য' : 'All Time' ?></option>
                            <option value="today" <?= $period === 'today' ? 'selected' : '' ?>><?= ($locale ?? 'bn') === 'bn' ? 'আজকের দিন' : 'Today' ?></option>
                            <option value="7days" <?= $period === '7days' ? 'selected' : '' ?>><?= ($locale ?? 'bn') === 'bn' ? 'বিগত ৭ দিন' : 'Last 7 Days' ?></option>
                            <option value="this_month" <?= $period === 'this_month' ? 'selected' : '' ?>><?= ($locale ?? 'bn') === 'bn' ? 'চলতি মাস' : 'This Month' ?></option>
                            <option value="custom" <?= $period === 'custom' ? 'selected' : '' ?>><?= ($locale ?? 'bn') === 'bn' ? 'নির্দিষ্ট তারিখ থেকে তারিখ...' : 'Custom Date Range...' ?></option>
                        </select>
                    </div>

                    <!-- 2. Year Filter -->
                    <div class="col-6 col-sm-3 col-lg-2">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="bi bi-calendar-event me-1 text-primary"></i><?= ($locale ?? 'bn') === 'bn' ? 'বছর' : 'Year' ?>
                        </label>
                        <select name="year" class="form-select border bg-white">
                            <option value=""><?= ($locale ?? 'bn') === 'bn' ? 'সকল বছর' : 'All Years' ?></option>
                            <option value="2026" <?= (string)$year === '2026' ? 'selected' : '' ?>>২০২৬</option>
                            <option value="2025" <?= (string)$year === '2025' ? 'selected' : '' ?>>২০২৫</option>
                        </select>
                    </div>

                    <!-- 3. Month Filter -->
                    <div class="col-6 col-sm-3 col-lg-2">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="bi bi-calendar-month me-1 text-primary"></i><?= ($locale ?? 'bn') === 'bn' ? 'মাস' : 'Month' ?>
                        </label>
                        <select name="month" class="form-select border bg-white">
                            <option value=""><?= ($locale ?? 'bn') === 'bn' ? 'সকল মাস' : 'All Months' ?></option>
                            <?php foreach ($banglaMonths as $mNum => $mName): ?>
                                <option value="<?= $mNum ?>" <?= (string)$month === (string)$mNum ? 'selected' : '' ?>>
                                    <?= ($locale ?? 'bn') === 'bn' ? $mName : date('F', mktime(0,0,0,$mNum,10)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Custom Dates (Conditional) -->
                    <div class="col-6 col-lg-2" id="start-date-col" style="<?= $period === 'custom' ? '' : 'display:none;' ?>">
                        <label class="form-label fw-bold small text-muted mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'শুরুর তারিখ' : 'Start Date' ?></label>
                        <input type="date" name="start_date" value="<?= e($startDate) ?>" class="form-control bg-white">
                    </div>
                    <div class="col-6 col-lg-2" id="end-date-col" style="<?= $period === 'custom' ? '' : 'display:none;' ?>">
                        <label class="form-label fw-bold small text-muted mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'শেষ তারিখ' : 'End Date' ?></label>
                        <input type="date" name="end_date" value="<?= e($endDate) ?>" class="form-control bg-white">
                    </div>
                </div>

                <!-- Filter Row 2: Zone, Ward, Department, Supervisor, Status & Action -->
                <div class="row g-3 align-items-end">
                    <!-- Zone Selection -->
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="bi bi-diagram-3-fill me-1 text-info"></i><?= ($locale ?? 'bn') === 'bn' ? 'অঞ্চল' : 'Zone' ?>
                        </label>
                        <select name="zone_id" class="form-select border bg-white">
                            <option value=""><?= ($locale ?? 'bn') === 'bn' ? 'সকল অঞ্চল' : 'All Zones' ?></option>
                            <?php foreach ($allZones as $z): ?>
                                <option value="<?= (int)$z['id'] ?>" <?= (string)$zoneId === (string)$z['id'] ? 'selected' : '' ?>>
                                    <?= ($locale ?? 'bn') === 'bn' ? e($z['name_bn']) : e($z['name_en']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Ward Selection -->
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="bi bi-geo-alt-fill me-1 text-danger"></i><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং' : 'Ward' ?>
                        </label>
                        <select name="ward_id" class="form-select border bg-white">
                            <option value=""><?= ($locale ?? 'bn') === 'bn' ? 'সকল ওয়ার্ড (১-৩৩)' : 'All Wards (1-33)' ?></option>
                            <?php foreach ($allWards as $w): ?>
                                <option value="<?= (int)$w['id'] ?>" <?= (string)$wardId === (string)$w['id'] ? 'selected' : '' ?>>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)$w['ward_number']) : 'Ward ' . $w['ward_number'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Category / Service -->
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="bi bi-tags-fill me-1 text-primary"></i><?= ($locale ?? 'bn') === 'bn' ? 'পৌর সেবা / বিভাগ' : 'Department' ?>
                        </label>
                        <select name="category_id" class="form-select border bg-white">
                            <option value=""><?= ($locale ?? 'bn') === 'bn' ? 'সকল সেবা বিভাগ' : 'All Departments' ?></option>
                            <?php foreach ($allCategories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>" <?= (string)$categoryId === (string)$cat['id'] ? 'selected' : '' ?>>
                                    <?= ($locale ?? 'bn') === 'bn' ? e($cat['name_bn']) : e($cat['name_en']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Supervisor Selection -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="bi bi-person-badge-fill me-1 text-success"></i><?= ($locale ?? 'bn') === 'bn' ? 'দায়িত্বপ্রাপ্ত সুপারভাইজার' : 'Supervisor' ?>
                        </label>
                        <select name="supervisor_id" class="form-select border bg-white">
                            <option value=""><?= ($locale ?? 'bn') === 'bn' ? 'সকল সুপারভাইজার (৩৩ জন)' : 'All Supervisors (33)' ?></option>
                            <?php foreach ($allSupervisors as $sup): ?>
                                <option value="<?= (int)$sup['employee_id'] ?>" <?= (string)$supervisorId === (string)$sup['employee_id'] ? 'selected' : '' ?>>
                                    <?= e($sup['full_name_bn']) ?> (<?= to_bn_number((string)$sup['official_phone']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="bi bi-flag-fill me-1 text-warning"></i><?= ($locale ?? 'bn') === 'bn' ? 'কাজের অবস্থা' : 'Status' ?>
                        </label>
                        <select name="status" class="form-select border bg-white">
                            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>><?= ($locale ?? 'bn') === 'bn' ? 'সকল অবস্থা' : 'All Status' ?></option>
                            <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>><?= ($locale ?? 'bn') === 'bn' ? 'মাঠে কাজ চলছে' : 'In Progress' ?></option>
                            <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>><?= ($locale ?? 'bn') === 'bn' ? '🚨 সময় পেরিয়ে গেছে' : '🚨 Overdue' ?></option>
                            <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>><?= ($locale ?? 'bn') === 'bn' ? '✅ সমাধান হয়েছে' : '✅ Resolved' ?></option>
                            <option value="reopened" <?= $status === 'reopened' ? 'selected' : '' ?>><?= ($locale ?? 'bn') === 'bn' ? 'পুনরায় খোলা অভিযোগ' : 'Reopened' ?></option>
                        </select>
                    </div>

                    <!-- Submit & Reset Buttons -->
                    <div class="col-12 col-lg-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-3 fw-bold flex-grow-1">
                            <i class="bi bi-funnel-fill me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'ফিল্টার' : 'Filter' ?>
                        </button>
                        <a href="/dashboard/reports" class="btn btn-outline-secondary" title="ফিল্টার মুছুন">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 6 Executive KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Complaints -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border shadow-sm rounded-4 bg-white p-3 text-center h-100">
                <small class="text-muted fw-semibold mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'মোট অভিযোগ' : 'Total Complaints' ?></small>
                <div class="fs-2 fw-bold text-primary"><?= to_bn_number((string)$metrics['total_complaints']) ?></div>
                <small class="text-muted" style="font-size: 0.75rem;"><?= ($locale ?? 'bn') === 'bn' ? 'নির্বাচিত সময়ে' : 'in selection' ?></small>
            </div>
        </div>

        <!-- 2. Resolved -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border shadow-sm rounded-4 bg-white p-3 text-center h-100 border-success-subtle">
                <small class="text-success fw-semibold mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'কাজ সম্পন্ন ও সমাধান' : 'Resolved' ?></small>
                <div class="fs-2 fw-bold text-success"><?= to_bn_number((string)$metrics['resolved_count']) ?></div>
                <span class="badge bg-success-subtle text-success small"><?= to_bn_number((string)$metrics['resolution_rate']) ?>% <?= ($locale ?? 'bn') === 'bn' ? 'হার' : 'rate' ?></span>
            </div>
        </div>

        <!-- 3. In Progress -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border shadow-sm rounded-4 bg-white p-3 text-center h-100">
                <small class="text-warning-emphasis fw-semibold mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'মাঠে কাজ চলছে' : 'In Progress' ?></small>
                <div class="fs-2 fw-bold text-warning-emphasis"><?= to_bn_number((string)$metrics['in_progress_count']) ?></div>
                <small class="text-muted" style="font-size: 0.75rem;"><?= ($locale ?? 'bn') === 'bn' ? 'তত্ত্বাবধানে' : 'active' ?></small>
            </div>
        </div>

        <!-- 4. Overdue -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border shadow-sm rounded-4 p-3 text-center h-100 bg-danger-subtle border-danger">
                <small class="text-danger fw-bold mb-1"><?= ($locale ?? 'bn') === 'bn' ? '🚨 সময় পেরিয়ে গেছে' : '🚨 Overdue' ?></small>
                <div class="fs-2 fw-bold text-danger"><?= to_bn_number((string)$metrics['overdue_count']) ?></div>
                <small class="text-danger" style="font-size: 0.75rem;"><?= ($locale ?? 'bn') === 'bn' ? 'মেয়রের দৃষ্টি আকর্ষণ' : 'urgent attention' ?></small>
            </div>
        </div>

        <!-- 5. Average Resolution Hours -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border shadow-sm rounded-4 bg-white p-3 text-center h-100">
                <small class="text-dark fw-semibold mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'গড় সমাধানের সময়' : 'Avg Resolution' ?></small>
                <div class="fs-2 fw-bold text-dark">
                    <?= $metrics['avg_resolution_hours'] > 0 ? to_bn_number((string)$metrics['avg_resolution_hours']) . 'h' : '-' ?>
                </div>
                <small class="text-muted" style="font-size: 0.75rem;"><?= ($locale ?? 'bn') === 'bn' ? 'ঘণ্টা প্রতি সমাধান' : 'hours/case' ?></small>
            </div>
        </div>

        <!-- 6. Citizen Satisfaction -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border shadow-sm rounded-4 bg-white p-3 text-center h-100">
                <small class="text-info fw-semibold mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক সন্তুষ্টি' : 'Citizen Rating' ?></small>
                <div class="fs-2 fw-bold text-info">
                    <?= $metrics['satisfaction_rate'] !== null ? to_bn_number((string)$metrics['satisfaction_rate']) . '%' : '-' ?>
                </div>
                <small class="text-muted" style="font-size: 0.75rem;">
                    <?= $metrics['total_feedback'] > 0 ? to_bn_number((string)$metrics['total_feedback']) . ' ' . (($locale ?? 'bn') === 'bn' ? 'মতামত' : 'reviews') : (($locale ?? 'bn') === 'bn' ? 'তথ্য নেই' : 'no data') ?>
                </small>
            </div>
        </div>
    </div>

    <!-- 1. Zonal Performance Comparison Table -->
    <div class="card border shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-diagram-3-fill text-info me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'অঞ্চলভিত্তিক সেবার পারফরম্যান্স ও কাজের অবস্থা' : 'Zonal Performance & Work Status' ?>
            </h5>
            <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশনের ৩টি অঞ্চল' : '3 MCC Administrative Zones' ?></small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'অঞ্চল' : 'Zone' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'মোট অভিযোগ' : 'Total' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'মাঠে চলমান' : 'In Progress' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'সমাধান সম্পন্ন' : 'Resolved' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'সময় পেরিয়ে গেছে' : 'Overdue' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'সাফল্যের হার' : 'Success Rate' ?></th>
                            <th class="text-center pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'গড় সমাধানের সময়' : 'Avg Time' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($zoneBreakdown as $zb): ?>
                            <?php
                                $zTotal = (int)$zb['total_complaints'];
                                $zResolved = (int)$zb['resolved_count'];
                                $zInProgress = (int)$zb['in_progress_count'];
                                $zOverdue = (int)$zb['overdue_count'];
                                $zRate = $zTotal > 0 ? round(($zResolved / $zTotal) * 100, 1) : 0;
                                $zAvg = round((float)($zb['avg_resolution_hours'] ?? 0), 1);
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark">
                                    <span class="badge bg-info-subtle text-info-emphasis px-2 py-1 me-1">
                                        <i class="bi bi-geo-alt me-1"></i><?= e($zb['name_bn']) ?>
                                    </span>
                                </td>
                                <td class="text-center fw-bold fs-6"><?= to_bn_number((string)$zTotal) ?></td>
                                <td class="text-center text-warning-emphasis fw-semibold"><?= to_bn_number((string)$zInProgress) ?></td>
                                <td class="text-center text-success fw-bold"><?= to_bn_number((string)$zResolved) ?></td>
                                <td class="text-center">
                                    <?php if ($zOverdue > 0): ?>
                                        <span class="badge bg-danger rounded-pill"><?= to_bn_number((string)$zOverdue) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" style="min-width: 140px;">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar <?= $zRate >= 75 ? 'bg-success' : ($zRate >= 40 ? 'bg-warning' : 'bg-danger') ?>" 
                                                 style="width: <?= $zRate ?>%"></div>
                                        </div>
                                        <small class="fw-bold"><?= to_bn_number((string)$zRate) ?>%</small>
                                    </div>
                                </td>
                                <td class="text-center pe-3 fw-semibold text-muted">
                                    <?= $zAvg > 0 ? to_bn_number((string)$zAvg) . ' ঘণ্টা' : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Supervisor Performance & Accountability Scorecard -->
    <div class="card border shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-people-fill text-success me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? '৩৩ জন ওয়ার্ড সুপারভাইজারের জবাবদিহিতা ও পারফরম্যান্স তালিকা' : 'Ward Supervisors Performance & Accountability' ?>
                </h5>
                <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'প্রত্যেক ওয়ার্ডের দায়িত্বপ্রাপ্ত সুপারভাইজারের কাজের অগ্রগতি ও মূল্যায়ন' : 'Individual Supervisor Metrics & Accountability' ?></small>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                ৩৩ জন সুপারভাইজার সংযুক্ত
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 440px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ও অঞ্চল' : 'Ward & Zone' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'সুপারভাইজারের নাম ও যোগাযোগ' : 'Supervisor Info' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'মোট কাজ' : 'Assigned' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'চলমান' : 'Active' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'সমাধান' : 'Resolved' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'সময় পেরিয়েছে' : 'Overdue' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'সাফল্যের হার' : 'Success %' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'গড় সময়' : 'Avg Time' ?></th>
                            <th class="text-center pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'মূল্যায়ন' : 'Status' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($supervisorBreakdown as $sb): ?>
                            <?php
                                $sTotal = (int)$sb['total_complaints'];
                                $sResolved = (int)$sb['resolved_count'];
                                $sInProgress = (int)$sb['in_progress_count'];
                                $sOverdue = (int)$sb['overdue_count'];
                                $sRate = $sTotal > 0 ? round(($sResolved / $sTotal) * 100, 1) : 0;
                                $sAvg = round((float)($sb['avg_resolution_hours'] ?? 0), 1);
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-bold text-dark">
                                        <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number((string)$sb['ward_number']) : 'Ward ' . $sb['ward_number'] ?>
                                    </span>
                                    <small class="text-muted d-block"><?= e($sb['zone_name_bn']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($sb['supervisor_name_bn']) ?></div>
                                    <small class="text-muted font-monospace"><i class="bi bi-telephone me-1"></i><?= to_bn_number((string)$sb['official_phone']) ?></small>
                                </td>
                                <td class="text-center fw-bold"><?= to_bn_number((string)$sTotal) ?></td>
                                <td class="text-center text-warning-emphasis fw-semibold"><?= to_bn_number((string)$sInProgress) ?></td>
                                <td class="text-center text-success fw-bold"><?= to_bn_number((string)$sResolved) ?></td>
                                <td class="text-center">
                                    <?php if ($sOverdue > 0): ?>
                                        <span class="badge bg-danger rounded-pill"><?= to_bn_number((string)$sOverdue) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" style="min-width: 120px;">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar <?= $sRate >= 75 ? 'bg-success' : ($sRate >= 40 ? 'bg-warning' : 'bg-danger') ?>" 
                                                 style="width: <?= $sRate ?>%"></div>
                                        </div>
                                        <small class="fw-bold"><?= to_bn_number((string)$sRate) ?>%</small>
                                    </div>
                                </td>
                                <td class="text-center text-muted small">
                                    <?= $sAvg > 0 ? to_bn_number((string)$sAvg) . 'h' : '-' ?>
                                </td>
                                <td class="text-center pe-3">
                                    <?php if ($sOverdue > 5): ?>
                                        <span class="badge bg-danger"><?= ($locale ?? 'bn') === 'bn' ? '🔴 জরুরি নজর' : 'Critical' ?></span>
                                    <?php elseif ($sOverdue > 0): ?>
                                        <span class="badge bg-warning text-dark"><?= ($locale ?? 'bn') === 'bn' ? '🟡 সতর্কবার্তা' : 'Warning' ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= ($locale ?? 'bn') === 'bn' ? '🟢 চমৎকার' : 'Good' ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Ward Breakdown Table -->
    <div class="card border shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ডভিত্তিক সেবার পারফরম্যান্স ও অগ্রগতি' : 'Ward-wise Civic Performance Scorecard' ?>
            </h5>
            <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? '৩৩টি ওয়ার্ডের সার্বিক চিত্র' : '33 Wards Summary' ?></small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 380px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং' : 'Ward #' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'মোট অভিযোগ' : 'Total' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'সমাধানকৃত' : 'Resolved' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'সময় পেরিয়ে গেছে' : 'Overdue' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'সমাধানের অগ্রগতি' : 'Resolution Rate' ?></th>
                            <th class="text-center"><?= ($locale ?? 'bn') === 'bn' ? 'অবস্থা' : 'Health' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wardBreakdown as $w): ?>
                            <?php
                                $wTotal = (int)$w['total'];
                                $wResolved = (int)$w['resolved'];
                                $wOverdue = (int)$w['overdue'];
                                $wRate = $wTotal > 0 ? round(($wResolved / $wTotal) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number((string)$w['ward_number']) : 'Ward ' . $w['ward_number'] ?>
                                    <small class="text-muted fw-normal">(<?= e($w['zone_name_bn'] ?? '') ?>)</small>
                                </td>
                                <td class="text-center fw-bold"><?= to_bn_number((string)$wTotal) ?></td>
                                <td class="text-center text-success fw-bold"><?= to_bn_number((string)$wResolved) ?></td>
                                <td class="text-center">
                                    <?php if ($wOverdue > 0): ?>
                                        <span class="badge bg-danger rounded-pill"><?= to_bn_number((string)$wOverdue) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" style="min-width: 140px;">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar <?= $wRate >= 75 ? 'bg-success' : ($wRate >= 40 ? 'bg-warning' : 'bg-danger') ?>" 
                                                 style="width: <?= $wRate ?>%"></div>
                                        </div>
                                        <small class="fw-bold"><?= to_bn_number((string)$wRate) ?>%</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($wOverdue > 5): ?>
                                        <span class="badge bg-danger"><?= ($locale ?? 'bn') === 'bn' ? '🔴 জরুরি নজর' : 'Critical' ?></span>
                                    <?php elseif ($wOverdue > 0): ?>
                                        <span class="badge bg-warning text-dark"><?= ($locale ?? 'bn') === 'bn' ? '🟡 সতর্কবার্তা' : 'Warning' ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= ($locale ?? 'bn') === 'bn' ? '🟢 চমৎকার' : 'Good' ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. Detailed Filtered Complaints Log Table -->
    <div class="card border shadow-sm rounded-4 bg-white">
        <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-list-check text-primary me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগসমূহের তালিকা (' . to_bn_number((string)count($complaints)) . ' টি প্রদর্শিত)' : 'Complaints Register (' . count($complaints) . ' Shown)' ?>
                </h5>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($complaints)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                    <h5><?= ($locale ?? 'bn') === 'bn' ? 'এই ফিল্টারে কোনো অভিযোগ পাওয়া যায়নি।' : 'No complaints match the selected filter.' ?></h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ও অঞ্চল' : 'Ward & Zone' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'সেবা ও সমস্যা' : 'Service & Issue' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'সুনির্দিষ্ট ঠিকানা' : 'Address' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'দায়িত্বপ্রাপ্ত সুপারভাইজার' : 'Supervisor' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'দাখিলের তারিখ' : 'Submitted' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'কাজের অবস্থা' : 'Status' ?></th>
                                <th class="pe-3 text-end"><?= ($locale ?? 'bn') === 'bn' ? 'সময়সীমা' : 'Deadline' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $c): ?>
                                <?php
                                    $isOverdue = !empty($c['deadline_at']) && strtotime($c['deadline_at']) < time() && empty($c['closed_at']);
                                    $isResolved = in_array($c['internal_status'], ['citizen_confirmed', 'closed']);
                                ?>
                                <tr>
                                    <td class="ps-3">
                                        <a href="/track/<?= e($c['public_complaint_number'] ?? $c['tracking_number'] ?? '') ?>" target="_blank" class="fw-bold font-monospace text-primary text-decoration-none">
                                            <?= e($c['public_complaint_number'] ?? $c['tracking_number'] ?? '') ?>
                                        </a>
                                        <?php if (!empty($c['reopen_count']) && (int)$c['reopen_count'] > 0): ?>
                                            <span class="badge bg-danger-subtle text-danger ms-1"><?= ($locale ?? 'bn') === 'bn' ? 'পুনরায় খোলা' : 'Reopened' ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)($c['ward_number'] ?? '')) : 'Ward ' . ($c['ward_number'] ?? '') ?>
                                        </span>
                                        <small class="text-muted d-block"><?= e($c['zone_name_bn'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            <?= ($locale ?? 'bn') === 'bn' ? e($c['subcategory_name_bn'] ?? '') : e($c['subcategory_name_en'] ?? '') ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= ($locale ?? 'bn') === 'bn' ? e($c['category_name_bn'] ?? '') : e($c['category_name_en'] ?? '') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-dark d-block text-truncate" style="max-width: 180px;">
                                            <?= e($c['landmark'] ? $c['landmark'] . ', ' : '') ?><?= e($c['public_safe_address'] ?? '') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-dark fw-semibold">
                                            <?= e($c['supervisor_name_bn'] ?? 'অনির্ধারিত') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= to_bn_number(date('d M Y', strtotime($c['created_at']))) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $isResolved ? 'bg-success' : ($isOverdue ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                            <?= e(human_status($c['internal_status'] ?? '', $locale)) ?>
                                        </span>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <?php if ($isResolved): ?>
                                            <small class="text-success fw-bold"><i class="bi bi-check2 me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'সম্পন্ন' : 'Closed' ?></small>
                                        <?php elseif ($isOverdue): ?>
                                            <small class="text-danger fw-bold"><i class="bi bi-alarm me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'সময় অতিক্রান্ত' : 'Overdue' ?></small>
                                        <?php else: ?>
                                            <small class="text-muted"><?= !empty($c['deadline_at']) ? to_bn_number(date('d M H:i', strtotime($c['deadline_at']))) : '-' ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleCustomDates(period) {
    const startCol = document.getElementById('start-date-col');
    const endCol = document.getElementById('end-date-col');
    if (period === 'custom') {
        startCol.style.display = 'block';
        endCol.style.display = 'block';
    } else {
        startCol.style.display = 'none';
        endCol.style.display = 'none';
    }
}
</script>
