<!-- Data & Monitoring Officer Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-graph-up-arrow text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'ডাটা ও মনিটরিং কর্মকর্তা — অ্যানালিটিক্স, বটলনেক ও হটস্পট বিশ্লেষণ' : 'Data & Monitoring Officer — Analytics, Bottlenecks & Hotspots' ?>
                    </h5>
                    <p class="text-muted small mb-0">
                        <?= ($locale ?? 'bn') === 'bn' ? 'নগরসেবার ডেডলাইন ব্যত্যয়, বিভাগভিত্তিক দীর্ঘসূত্রিতা ও অসন্তোষের মূল কারণ পর্যালোচনা করুন।' : 'Identify service bottlenecks, SLA breaches, ward hotspots and recurring complaint patterns.' ?>
                    </p>
                </div>
                <a href="/dashboard/reports" class="btn btn-outline-primary rounded-3">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'পূর্ণাঙ্গ রিপোর্ট সেন্টার' : 'Full Reports Center' ?>
                </a>
            </div>

            <!-- Top Row: Ward Hotspots and Satisfaction / Quality Indices -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="bi bi-fire text-danger me-2"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'শীর্ষ অভিযোগপ্রবণ ওয়ার্ডসমূহ (Top Hotspots)' : 'Top Grievance Hotspots by Ward' ?>
                        </h6>
                        <ul class="list-group list-group-flush bg-transparent">
                            <?php if (empty($hotspots)): ?>
                                <li class="list-group-item bg-transparent text-muted text-center py-3">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'কোন হটস্পট তথ্য পাওয়া যায়নি।' : 'No hotspot data available.' ?>
                                </li>
                            <?php else: ?>
                                <?php foreach ($hotspots as $h): ?>
                                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-secondary-subtle text-dark me-2">#<?= to_bn_number((string)$h['ward_number']) ?></span>
                                            <span><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number((string)$h['ward_number']) : 'Ward #' . $h['ward_number'] ?></span>
                                        </div>
                                        <span class="badge bg-danger rounded-pill px-3 py-1"><?= to_bn_number((string)$h['complaint_count']) ?> <?= ($locale ?? 'bn') === 'bn' ? 'টি অভিযোগ' : 'complaints' ?></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="bi bi-speedometer2 text-success me-2"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'সামগ্রিক সন্তুষ্টি ও সমাধান গুণমান সূচক' : 'Overall Satisfaction & Quality Indices' ?>
                        </h6>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক সন্তুষ্টি হার (Citizen Satisfaction)' : 'Citizen Satisfaction' ?></span>
                                <strong class="text-success"><?= to_bn_number((string)($kpis['citizen_satisfaction_percent'] ?? 100)) ?>%</strong>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: <?= (float)($kpis['citizen_satisfaction_percent'] ?? 100) ?>%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span><?= ($locale ?? 'bn') === 'bn' ? 'এসএলএ সময়সীমা রক্ষা (SLA Compliance)' : 'SLA Compliance' ?></span>
                                <strong class="text-primary"><?= to_bn_number(number_format((float)($kpis['sla_compliance_rate'] ?? 95), 1)) ?>%</strong>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" style="width: <?= (float)($kpis['sla_compliance_rate'] ?? 95) ?>%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span><?= ($locale ?? 'bn') === 'bn' ? 'পুনরায় চালুর হার (Reopen Rate)' : 'Reopen Rate' ?></span>
                                <strong class="text-warning"><?= to_bn_number((string)($kpis['reopen_rate_percent'] ?? 0)) ?>%</strong>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: <?= min(100, (float)($kpis['reopen_rate_percent'] ?? 0) * 5) ?>%"></div>
                            </div>
                            <small class="text-muted mt-1 d-block">
                                <?= ($locale ?? 'bn') === 'bn' ? 'লক্ষ্যমাত্রা: ৫% এর নিচে বজায় রাখা।' : 'Target: Keep below 5%.' ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Bottlenecks Table -->
            <div class="card border rounded-3 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-clock-history text-warning me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'বিভাগভিত্তিক দীর্ঘসূত্রিতা ও সময়সীমা ব্যত্যয় (Department SLA Bottlenecks)' : 'Department Bottlenecks & SLA Overdue' ?>
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'বিভাগ' : 'Department' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'মোট অভিযোগ' : 'Total Complaints' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'মেয়াদোত্তীর্ণ (Overdue)' : 'Overdue' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'গড় নিষ্পত্তির সময় (ঘণ্টা)' : 'Avg Resolution Time (hrs)' ?></th>
                                <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'ঝুঁকি স্তর' : 'Risk Level' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($deptBottlenecks)): ?>
                                <tr><td colspan="5" class="text-center py-3 text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'কোন তথ্য নেই।' : 'No department bottleneck data.' ?></td></tr>
                            <?php else: ?>
                                <?php foreach ($deptBottlenecks as $db): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark"><?= e($db['name_bn']) ?></td>
                                        <td><?= to_bn_number((string)$db['total']) ?></td>
                                        <td>
                                            <?php if ((int)$db['overdue'] > 0): ?>
                                                <span class="badge bg-danger-subtle text-danger fw-bold"><?= to_bn_number((string)$db['overdue']) ?> টি বিলম্বিত</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success">সময়মতো চলছে</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="font-monospace"><?= to_bn_number((string)($db['avg_hours'] ?? 0)) ?> ঘণ্টা</span></td>
                                        <td class="text-end pe-3">
                                            <?php if ((int)$db['overdue'] > 2): ?>
                                                <span class="badge bg-danger text-white">উচ্চ ঝুঁকি</span>
                                            <?php elseif ((int)$db['overdue'] > 0): ?>
                                                <span class="badge bg-warning text-dark">মাঝারি</span>
                                            <?php else: ?>
                                                <span class="badge bg-success text-white">স্বাভাবিক</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reopened Cases Analysis -->
            <div class="card border rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-arrow-repeat text-danger me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক কর্তৃক পুনরায় চালু হওয়া অভিযোগ (Reopened Cases Queue)' : 'Reopened Cases Queue' ?>
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড' : 'Ward' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার ধরন' : 'Category' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'পুনরায় চালুর তারিখ' : 'Reopened Date' ?></th>
                                <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'পদক্ষেপ' : 'Action' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reopenCases)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-emoji-smile text-success fs-3 d-block mb-1"></i>
                                        <?= ($locale ?? 'bn') === 'bn' ? 'কোন অভিযোগ পুনরায় চালু নেই। নাগরিকরা সমাধানে সন্তুষ্ট।' : 'No reopened cases currently.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reopenCases as $rc): ?>
                                    <tr>
                                        <td class="ps-3 font-monospace fw-bold text-danger"><?= e($rc['public_complaint_number']) ?></td>
                                        <td><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)$rc['ward_number']) : 'Ward ' . $rc['ward_number'] ?></td>
                                        <td><?= e($rc['subcategory_name_bn']) ?></td>
                                        <td class="small text-muted"><?= e(date('d M Y, h:i A', strtotime($rc['updated_at']))) ?></td>
                                        <td class="text-end pe-3">
                                            <a href="/track/<?= urlencode($rc['public_complaint_number']) ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                                <?= ($locale ?? 'bn') === 'bn' ? 'পুনঃতদন্ত কেস দেখুন' : 'Inspect' ?>
                                            </a>
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
</div>
