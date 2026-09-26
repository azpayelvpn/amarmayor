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
                                            <button type="button" class="btn btn-sm btn-danger px-2 py-1" data-bs-toggle="modal" data-bs-target="#explanationModal" data-dept-id="<?= (int)$d['id'] ?>">
                                                <i class="bi bi-megaphone me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'কৈফিয়ত তলব' : 'Show Cause' ?>
                                            </button>
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

    <!-- Active Explanation Requests / Show Cause Queue -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-file-earmark-text text-danger me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'বিভাগীয় প্রধানদের কাছে কৈফিয়ত তলব ও জবাবদিহিতা রেজিস্টার' : 'Formal Explanation Requests & Accountability Register' ?>
                </h5>
                <button type="button" class="btn btn-sm btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#explanationModal">
                    <i class="bi bi-plus-circle me-1"></i>নতুন ব্যাখ্যা তলব
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">অভিযোগ নং</th>
                            <th>দায়িত্বপ্রাপ্ত কর্মকর্তা</th>
                            <th>তলবকৃত কৈফিয়ত / প্রশ্ন</th>
                            <th>জবাবের সময়সীমা</th>
                            <th>কর্মকর্তার লিখিত জবাব</th>
                            <th class="text-end pe-3">অবস্থা</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($explanationRequests)): ?>
                            <tr><td colspan="6" class="text-center py-3 text-muted">বর্তমানে কোনো বিচারাধীন কৈফিয়ত তলব নেই।</td></tr>
                        <?php else: ?>
                            <?php foreach ($explanationRequests as $er): ?>
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-dark">#<?= e($er['public_complaint_number'] ?? $er['complaint_id']) ?></td>
                                    <td>
                                        <strong><?= e($er['target_employee_name'] ?? 'বিভাগীয় কর্মকর্তা') ?></strong>
                                        <small class="text-muted d-block"><?= e($er['designation_bn'] ?? '') ?></small>
                                    </td>
                                    <td><div class="small text-danger fw-semibold"><?= e($er['question']) ?></div></td>
                                    <td class="small text-muted font-monospace"><?= e($er['due_date'] ?? '২৪ ঘণ্টা') ?></td>
                                    <td>
                                        <?php if (!empty($er['explanation_response'])): ?>
                                            <div class="small text-success bg-success-subtle p-1 rounded"><?= e($er['explanation_response']) ?></div>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">জবাবের অপেক্ষায়</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if (!empty($er['explanation_response'])): ?>
                                            <span class="badge bg-success">জবাব দাখিলকৃত</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">চলমান নোটিশ</span>
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

    <!-- 3 Zones Executive Progress -->
    <?php if (!empty($zonalProgress)): ?>
        <div class="col-12">
            <div class="card border shadow-sm rounded-4 bg-white p-4">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="bi bi-pin-map-fill text-primary me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'অঞ্চলভিত্তিক সামগ্রিক নিষ্পত্তি হার ও মনিটরিং' : 'Zonal Performance & Resolution Rates' ?>
                </h5>
                <div class="row g-3">
                    <?php foreach ($zonalProgress as $zp): ?>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold text-dark mb-2"><?= e($zp['name_bn']) ?></h6>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>মোট সমস্যা: <strong><?= to_bn_number((string)$zp['total']) ?></strong></span>
                                    <span>সমাধান: <strong class="text-success"><?= to_bn_number((string)$zp['resolved']) ?></strong></span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>সময় পার: <strong class="text-danger"><?= to_bn_number((string)$zp['overdue']) ?></strong></span>
                                    <span>হার: <strong><?= $zp['total'] > 0 ? to_bn_number((string)round(($zp['resolved'] / $zp['total']) * 100)) : '১০০' ?>%</strong></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

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
                                <th class="text-end">তাত্ক্ষণিক শোকজ / ব্যবস্থা</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($supportBottlenecks as $sb): ?>
                                <tr>
                                    <td>
                                        <div class="font-monospace fw-bold text-danger"><?= e($sb['public_complaint_number']) ?></div>
                                        <small class="text-muted">ওয়ার্ড <?= to_bn_number((string)($sb['ward_number'] ?? '')) ?></small>
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
                                        <button type="button" class="btn btn-sm btn-danger text-nowrap" data-bs-toggle="modal" data-bs-target="#explanationModal" data-dept-id="<?= (int)($sb['target_department_id'] ?? 1) ?>">
                                            <i class="bi bi-megaphone-fill me-1"></i>কৈফিয়ত তলব
                                        </button>
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
                                <?= ($locale ?? 'bn') === 'bn' ? '🚨 ওয়ার্ডে পরিচ্ছন্নতাকর্মী সংকট অচলাবস্থা (Workforce Shortage Red Alert)' : '🚨 Ward Workforce Shortage Red Alert' ?>
                            </h5>
                            <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ডে জনবল ঘাটতির কারণে নাগরিক সেবা ব্যাহত হচ্ছে — সিইও সরাসরি কৈফিয়ত তলব বা ব্যবস্থা গ্রহণ করতে পারেন' : 'Cleaners shortage causing civic deadlock — CEO can issue show-cause or intervention' ?></small>
                        </div>
                    </div>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-bold">
                        <?= to_bn_number((string)count($manpowerShortageAlerts)) ?> <?= ($locale ?? 'bn') === 'bn' ? 'টি ওয়ার্ডে সংকট' : 'ward shortages' ?>
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-danger">
                            <tr>
                                <th>ওয়ার্ড ও অভিযোগ নং</th>
                                <th>অনুরোধকারী সুপারভাইজার</th>
                                <th>ঘাটতি / চাহিত কর্মী</th>
                                <th>বর্তমান অবস্থা</th>
                                <th class="text-end">সিইও একশন</th>
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
                                            <span class="badge bg-warning text-dark fw-bold">বিভাগীয় ব্যবস্থা অপেক্ষমাণ</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">সরবরাহ ব্যর্থ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-danger text-nowrap" data-bs-toggle="modal" data-bs-target="#explanationModal" data-dept-id="<?= (int)($msa['target_department_id'] ?? 1) ?>">
                                            <i class="bi bi-megaphone-fill me-1"></i>কৈফিয়ত তলব
                                        </button>
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

<!-- Modal: Issue Explanation Request -->
<div class="modal fade" id="explanationModal" tabindex="-1" aria-labelledby="explanationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/dashboard/ceo/explanation-requests" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="explanationModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>আনুষ্ঠানিক কৈফিয়ত তলব (Show Cause)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">নির্ধারিত সময়সীমা (SLA) পেরিয়ে যাওয়া বা দায়িত্বে অবহেলার জন্য সংশ্লিষ্ট বিভাগীয় কর্মকর্তা বা দায়িত্বপ্রাপ্ত কর্মচারীর কাছে ব্যাখ্যা চান:</p>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">সংশ্লিষ্ট অভিযোগ নির্বাচন করুন</label>
                    <select name="complaint_id" class="form-select" required>
                        <?php if (empty($overdueComplaints)): ?>
                            <option value="1">অভিযোগ #MCC-2026-0001 (ডেমো কেস)</option>
                        <?php else: ?>
                            <?php foreach ($overdueComplaints as $oc): ?>
                                <option value="<?= (int)$oc['id'] ?>">#<?= e($oc['public_complaint_number']) ?> — <?= e($oc['subcategory_name_bn']) ?> (ওয়ার্ড <?= to_bn_number((string)$oc['ward_number']) ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">দায়িত্বপ্রাপ্ত কর্মকর্তা নির্বাচন করুন</label>
                    <select name="target_employee_id" class="form-select" required>
                        <?php if (empty($departmentOfficers)): ?>
                            <option value="1">সহকারী বর্জ্য ব্যবস্থাপনা কর্মকর্তা (প্রকৌশল/বর্জ্য)</option>
                        <?php else: ?>
                            <?php foreach ($departmentOfficers as $dOff): ?>
                                <option value="<?= (int)$dOff['employee_id'] ?>"><?= e($dOff['full_name_bn']) ?> (<?= e($dOff['designation_bn']) ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">কৈফিয়তের বিবরণ / সুনির্দিষ্ট প্রশ্ন</label>
                    <textarea name="question" rows="3" class="form-control" placeholder="যেমন: নির্ধারিত ৪৮ ঘণ্টার মধ্যে ড্রেনের বর্জ্য অপসারণ না হওয়ার কারণ কি এবং আজকেই কেন টিম পাঠানো হয়নি?" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">জবাব দাখিলের সময়সীমা</label>
                    <input type="datetime-local" name="due_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime('+24 hours')) ?>">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-danger fw-bold">কৈফিয়ত তলব জারি করুন</button>
            </div>
        </form>
    </div>
</div>
