<!-- Supervisor Operations & Verification Portal -->
<div class="row g-4 mb-4">
    <!-- Executive Directives Banner (Mayor's Direct Orders Loop) -->
    <?php if (!empty($directives)): ?>
        <div class="col-12">
            <?php foreach ($directives as $d): ?>
                <div class="card border-danger bg-danger bg-opacity-10 shadow-sm rounded-4 p-4 mb-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <h6 class="fw-bold text-danger mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-shield-fill-exclamation fs-4"></i>
                            <span>🚨 মেয়র মহোদয়ের সরাসরি জরুরি নির্দেশনা! (অভিযোগ নং: <?= e($d['public_complaint_number']) ?>)</span>
                        </h6>
                        <span class="badge bg-danger">উচ্চ অগ্রাধিকার (Expedite)</span>
                    </div>
                    <div class="p-3 bg-white rounded-3 border border-danger-subtle mb-3">
                        <strong class="text-dark d-block mb-1">নির্দেশনা:</strong>
                        <p class="mb-0 text-danger fw-semibold"><?= nl2br(e($d['instruction'])) ?></p>
                    </div>
                    <!-- Direct Feedback Form to Mayor -->
                    <form action="/dashboard/directives/<?= (int)$d['id'] ?>/respond" method="POST" class="row g-2 align-items-center">
                        <?= csrf_field() ?>
                        <div class="col-md-9">
                            <input type="text" name="response_text" required
                                   placeholder="মেয়র মহোদয়কে কাজের বাস্তব অগ্রগতি ও পদক্ষেপ জানান..."
                                   class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold">
                                <i class="bi bi-send-fill me-1"></i>মেয়রকে জবাব পাঠান
                            </button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Supervisor Quick Metrics -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-people-fill text-primary me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'সুপারভাইজার অপারেশন হাব — ওয়ার্ড নং ' . to_bn_number((string)($wardNumber ?? 1)) : 'Supervisor Operations Hub — Ward ' . ($wardNumber ?? 1) ?>
                </h5>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#manpowerShortageModal">
                        <i class="bi bi-people-fill me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'জরুরি কর্মী রিকুইজিশন' : 'Request Extra Workers' ?>
                    </button>
                    <button type="button" class="btn btn-outline-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#supportRequestModal">
                        <i class="bi bi-diagram-3-fill me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'যন্ত্রপাতি ও অন্য বিভাগ' : 'Cross-Dept Support' ?>
                    </button>
                    <a href="/dashboard/tasks/print-sheet" target="_blank" class="btn btn-outline-success fw-bold shadow-sm">
                        <i class="bi bi-printer-fill me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'রুট-স্লিপ প্রিন্ট' : 'Print Route Sheet' ?>
                    </a>
                </div>
            </div>
            <div class="row g-3">
                <?php
                    $pendingCount = count(array_filter($tasks ?? [], fn($t) => $t['task_status'] === 'pending'));
                    $inProgressCount = count(array_filter($tasks ?? [], fn($t) => $t['task_status'] === 'in_progress'));
                    $verificationCount = count(array_filter($tasks ?? [], fn($t) => $t['task_status'] === 'completed' || ($t['complaint_status'] ?? '') === 'work_completed'));
                    $unassignedCount = count($unassignedComplaints ?? []);
                ?>
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-primary"><?= to_bn_number((string)($unassignedCount + $pendingCount)) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'নতুন ও অপেক্ষমাণ কাজ' : 'New / Pending' ?></small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-warning-emphasis"><?= to_bn_number((string)$inProgressCount) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মাঠে চলমান কাজ' : 'In Progress' ?></small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-success-subtle rounded-3 text-center border border-success-subtle">
                        <div class="fs-2 fw-bold text-success"><?= to_bn_number((string)$verificationCount) ?></div>
                        <small class="text-success fw-semibold"><?= ($locale ?? 'bn') === 'bn' ? 'সম্পন্ন — যাচাই প্রয়োজন' : 'Verification Needed' ?></small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-info-subtle rounded-3 text-center border border-info-subtle">
                        <div class="fs-2 fw-bold <?= ($workforceStats['available_workers'] ?? 8) > 0 ? 'text-info-emphasis' : 'text-danger' ?>">
                            <?= to_bn_number((string)($workforceStats['available_workers'] ?? 8)) ?>
                            <span class="fs-6 text-muted">/ <?= to_bn_number((string)($workforceStats['total_workers'] ?? 8)) ?></span>
                        </div>
                        <small class="text-dark fw-semibold"><?= ($locale ?? 'bn') === 'bn' ? 'উপলব্ধ কর্মী (ফাঁকা / মোট)' : 'Available Crew' ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Quick Squad Dispatch Queue (Ward Supervisor Operations Control) -->
    <?php if (!empty($unassignedComplaints)): ?>
        <div class="col-12">
            <div class="card border-primary border-opacity-50 shadow-sm rounded-4 bg-white p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-send-check-fill text-primary me-2"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'নতুন নাগরিক অভিযোগ — দলনেতা ও কর্মী মোতায়েন' : 'New Complaints — Dispatch Squad & Crew' ?>
                        </h5>
                        <small class="text-muted">
                            <?= ($locale ?? 'bn') === 'bn' ? 'সুপারভাইজার হিসেবে অভিযোগ অনুযায়ী উপযুক্ত দলনেতা ও কর্মী সংখ্যা নির্ধারণ করে কাজ শুরু করান:' : 'Assign designated team leader and field crew count for each task' ?>
                        </small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                            <i class="bi bi-people-fill text-primary me-1"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ডে সক্রিয় কর্মী: ' . to_bn_number((string)($workforceStats['available_workers'] ?? 8)) . ' জন' : 'Available: ' . ($workforceStats['available_workers'] ?? 8) ?>
                        </span>
                        <?php if (($workforceStats['available_workers'] ?? 8) < 2): ?>
                            <button type="button" class="btn btn-sm btn-danger fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#manpowerShortageModal">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>অতিরিক্ত কর্মী চান
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 25%;">অভিযোগ ট্র্যাকিং ও স্থান</th>
                                <th style="width: 30%;">নাগরিকের বিবরণ</th>
                                <th style="width: 45%;" class="text-end pe-3">দলনেতা ও কর্মী নির্ধারণ (Dispatch Squad)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unassignedComplaints as $uComp): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="font-monospace fw-bold text-primary mb-1">
                                            <a href="/dashboard/complaints/<?= urlencode($uComp['public_complaint_number']) ?>" class="text-primary text-decoration-none">
                                                <?= e($uComp['public_complaint_number']) ?>
                                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                            </a>
                                        </div>
                                        <strong class="text-dark small d-block"><?= e($uComp['subcategory_name_bn'] ?? '') ?></strong>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-geo-alt me-1"></i><?= e($uComp['landmark'] ? $uComp['landmark'] . ', ' : '') ?><?= e($uComp['approximate_address'] ?? 'ওয়ার্ড এলাকা') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="small text-dark fw-medium mb-1">
                                            <?= e(mb_substr($uComp['description'] ?? '', 0, 110)) ?><?= mb_strlen($uComp['description'] ?? '') > 110 ? '...' : '' ?>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary small">
                                            <?= e($uComp['citizen_name_bn'] ?? 'নাগরিক') ?>
                                        </span>
                                    </td>
                                    <td class="pe-3">
                                        <form action="/dashboard/tasks/dispatch-squad" method="POST" class="p-3 bg-light rounded-3 border">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="complaint_id" value="<?= (int)$uComp['id'] ?>">

                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-5">
                                                    <label class="form-label small fw-bold text-dark mb-1">
                                                        <i class="bi bi-person-badge text-warning-emphasis me-1"></i>মাঠ দলনেতা:
                                                    </label>
                                                    <select name="team_leader_id" class="form-select form-select-sm" required>
                                                        <?php foreach ($teamLeaders ?? [] as $tl): ?>
                                                            <option value="<?= (int)$tl['employee_id'] ?>">
                                                                <?= e($tl['full_name_bn']) ?> (<?= e($tl['official_phone'] ?: $tl['employee_code']) ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold text-dark mb-1">
                                                        <i class="bi bi-people text-primary me-1"></i>কর্মী সংখ্যা:
                                                    </label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="worker_count" min="1" max="20" value="2" class="form-control text-center fw-bold" required>
                                                        <span class="input-group-text bg-white small">জন</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4 text-end">
                                                    <label class="form-label d-none d-md-block small text-transparent mb-1">&nbsp;</label>
                                                    <button type="submit" class="btn btn-sm btn-success fw-bold w-100 shadow-xs">
                                                        <i class="bi bi-send-fill me-1"></i>কাজ শুরু করুন
                                                    </button>
                                                </div>

                                                <div class="col-12 mt-2">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                                        <input type="text" name="instructions" class="form-control form-control-sm flex-grow-1"
                                                               placeholder="কাজের নির্দেশনা (উদাঃ দ্রুত ড্রেন পরিষ্কার করুন, আবর্জনা ভ্যানে তুলুন...)">
                                                        <?php if (($workforceStats['available_workers'] ?? 8) < 2): ?>
                                                            <button type="button" class="btn btn-xs btn-outline-danger fw-bold" data-bs-toggle="modal" data-bs-target="#manpowerShortageModal">
                                                                <i class="bi bi-exclamation-triangle me-1"></i>কর্মী সংকট? সহায়তা চান
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
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

    <?php
        // Split support requests into two groups
        $workforceReqs   = array_filter($supportRequests ?? [], fn($sr) => ($sr['support_type'] ?? '') === 'extra_manpower');
        $crossDeptReqs   = array_filter($supportRequests ?? [], fn($sr) => ($sr['support_type'] ?? '') !== 'extra_manpower');
    ?>

    <!-- ════ জনবল রিকুইজিশন স্ট্যাটাস (Workforce Requisition Status) ════ -->
    <?php if (!empty($workforceReqs)): ?>
        <div class="col-12">
            <div class="card border-success border-opacity-75 shadow-sm rounded-4 bg-white p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-people-fill text-danger me-2"></i>জনবল রিকুইজিশন — বিভাগীয় অনুমোদন ও বরাদ্দ স্ট্যাটাস
                    </h5>
                    <span class="badge bg-danger rounded-pill"><?= count($workforceReqs) ?> টি রিকুইজিশন</span>
                </div>
                <p class="text-muted small mb-3">আপনার পাঠানো অতিরিক্ত পরিচ্ছন্নতাকর্মীর রিকুইজিশনের অনুমোদন ও বরাদ্দের সর্বশেষ অবস্থা:</p>

                <?php foreach ($workforceReqs as $wr): ?>
                    <?php
                        $isApproved  = $wr['status'] === 'approved';
                        $isPending   = $wr['status'] === 'pending';
                        $isRejected  = $wr['status'] === 'rejected';
                        $isDispatched = $wr['fulfillment_status'] === 'dispatched';
                        $isReceived  = $wr['fulfillment_status'] === 'received';
                        $cardBg = ($isApproved || $isDispatched || $isReceived)
                            ? 'bg-success-subtle border-success-subtle'
                            : ($isPending ? 'bg-warning-subtle border-warning-subtle' : 'bg-danger-subtle border-danger-subtle');
                    ?>
                    <div class="p-3 rounded-3 border mb-3 <?= $cardBg ?>">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                            <div>
                                <a href="/dashboard/complaints/<?= urlencode($wr['public_complaint_number']) ?>"
                                   class="font-monospace fw-bold text-primary text-decoration-none">
                                    <?= e($wr['public_complaint_number']) ?>
                                    <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                </a>
                                <div class="small text-dark mt-1">
                                    <strong><?= to_bn_number((string)($wr['requested_worker_count'] ?? 0)) ?> জন</strong> পরিচ্ছন্নতাকর্মীর রিকুইজিশন
                                </div>
                                <div class="small text-muted"><?= e($wr['details'] ?? '') ?></div>
                            </div>
                            <div class="text-end">
                                <?php if ($isReceived): ?>
                                    <span class="badge bg-success fs-6 px-3 py-2">
                                        <i class="bi bi-check2-circle me-1"></i>মাঠে গৃহীত ও সক্রিয়
                                    </span>
                                <?php elseif ($isDispatched || $isApproved): ?>
                                    <span class="badge bg-success fs-6 px-3 py-2">
                                        <i class="bi bi-check-circle-fill me-1"></i>✅ অনুমোদিত ও প্রেরিত
                                    </span>
                                <?php elseif ($isPending): ?>
                                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                        <i class="bi bi-hourglass-split me-1"></i>বিভাগীয় প্রধানের সিদ্ধান্ত অপেক্ষমাণ
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger fs-6 px-3 py-2">
                                        <i class="bi bi-x-circle me-1"></i>প্রত্যাখ্যাত
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($isApproved || $isDispatched || $isReceived): ?>
                            <!-- Approved/Dispatched: Show allocation details prominently -->
                            <div class="p-3 bg-white rounded-3 border border-success-subtle mt-2">
                                <div class="row g-2 align-items-center">
                                    <div class="col-sm-3">
                                        <strong class="small text-success-emphasis d-block">বরাদ্দকৃত কর্মী:</strong>
                                        <span class="fw-bold text-success fs-4"><?= to_bn_number((string)($wr['allocated_worker_count'] ?? 0)) ?> জন</span>
                                    </div>
                                    <div class="col-sm-5">
                                        <strong class="small text-success-emphasis d-block">উৎস / সরবরাহ:</strong>
                                        <span class="text-dark small"><?= e($wr['allocated_resource'] ?? $wr['response_notes'] ?? 'কেন্দ্রীয় রিজার্ভ') ?></span>
                                        <?php if (!empty($wr['allocated_operator'])): ?>
                                            <div class="small text-muted"><i class="bi bi-person me-1"></i><?= e($wr['allocated_operator']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-sm-4 text-sm-end">
                                        <strong class="small text-success-emphasis d-block">মাঠ রসিদ (Handover):</strong>
                                        <?php if ($isReceived): ?>
                                            <span class="badge bg-success">✅ রসিদ গৃহীত</span>
                                        <?php else: ?>
                                            <div class="d-flex gap-1 justify-content-sm-end mt-1 flex-wrap">
                                                <form action="/dashboard/support-requests/<?= (int)$wr['id'] ?>/verify-receipt" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="fulfillment_status" value="received">
                                                    <input type="hidden" name="receipt_notes" value="মাঠে কর্মীরা যথাসময়ে উপস্থিত হয়েছে।">
                                                    <button type="submit" class="btn btn-sm btn-success fw-bold"
                                                            onclick="return confirm('কর্মীরা মাঠে উপস্থিত হয়েছে নিশ্চিত করুন?')">
                                                        <i class="bi bi-check-lg me-1"></i>মাঠে পেয়েছি
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#wr-failed-<?= (int)$wr['id'] ?>">
                                                    <i class="bi bi-x-circle me-1"></i>পৌঁছায়নি
                                                </button>
                                            </div>
                                            <div class="collapse mt-2 text-start p-2 bg-light border border-danger-subtle rounded" id="wr-failed-<?= (int)$wr['id'] ?>">
                                                <form action="/dashboard/support-requests/<?= (int)$wr['id'] ?>/verify-receipt" method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="fulfillment_status" value="failed_delivery">
                                                    <label class="form-label small text-danger fw-bold mb-1">না পৌঁছানোর কারণ:</label>
                                                    <input type="text" name="receipt_notes" class="form-control form-control-sm mb-2"
                                                           placeholder="উদাঃ নির্ধারিত সময়ে কর্মীরা আসেনি..." required>
                                                    <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold">ব্যর্থতা রিপোর্ট করুন</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="small text-muted mt-2">
                            <i class="bi bi-clock me-1"></i>রিকুইজিশন: <?= e(date('d M Y, H:i', strtotime($wr['created_at'] ?? 'now'))) ?>
                            <?php if (!empty($wr['responded_at'])): ?>
                                &bull; বিভাগীয় সিদ্ধান্ত: <?= e(date('d M Y, H:i', strtotime($wr['responded_at']))) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ════ আন্তঃবিভাগীয় সহায়তা ও মাঠ প্রাপ্তি যাচাইকরণ (Cross-Dept Support) ════ -->
    <?php if (!empty($crossDeptReqs)): ?>
        <div class="col-12">
            <div class="card border-primary shadow-sm rounded-4 bg-white p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-diagram-3-fill text-primary me-2"></i>অন্য বিভাগে প্রেরিত সহায়তা ও মাঠ প্রাপ্তি যাচাইকরণ
                    </h5>
                    <span class="badge bg-primary rounded-pill"><?= count($crossDeptReqs) ?> টি আবেদন</span>
                </div>
                <p class="text-muted small mb-3">অনুরোধকৃত সহায়তা ও যন্ত্রপাতি সাইটে আসলেই পৌঁছেছে কিনা তা যাচাই করে নিশ্চিত করুন:</p>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">অভিযোগ নং</th>
                                <th>টার্গেট বিভাগ ও ধরন</th>
                                <th>বরাদ্দকৃত যন্ত্রপাতি ও চালক</th>
                                <th>মাঠ প্রাপ্তি অবস্থা</th>
                                <th class="text-end pe-3">মাঠ প্রাপ্তি যাচাইকরণ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($crossDeptReqs as $sr): ?>
                                <tr>
                                    <td class="ps-3">
                                        <a href="/dashboard/complaints/<?= urlencode($sr['public_complaint_number']) ?>"
                                           class="font-monospace fw-bold text-primary text-decoration-none">
                                            <?= e($sr['public_complaint_number']) ?>
                                            <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <strong><?= e($sr['target_department_name_bn'] ?? 'সংশ্লিষ্ট বিভাগ') ?></strong>
                                        <div class="badge bg-secondary-subtle text-secondary small d-inline-block"><?= e($sr['support_type']) ?></div>
                                        <div class="text-muted small mt-1"><?= e($sr['details']) ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($sr['allocated_resource']) || !empty($sr['allocated_operator'])): ?>
                                            <div class="fw-semibold text-dark small"><i class="bi bi-truck me-1"></i><?= e($sr['allocated_resource'] ?? '') ?></div>
                                            <div class="text-muted small"><i class="bi bi-person me-1"></i><?= e($sr['allocated_operator'] ?? '') ?></div>
                                            <?php if (!empty($sr['scheduled_arrival'])): ?>
                                                <div class="text-primary small"><i class="bi bi-clock me-1"></i><?= e($sr['scheduled_arrival']) ?></div>
                                            <?php endif; ?>
                                        <?php elseif (!empty($sr['response_notes'])): ?>
                                            <div class="small text-muted"><?= e($sr['response_notes']) ?></div>
                                        <?php else: ?>
                                            <span class="text-muted small">বিভাগীয় সিদ্ধান্তের অপেক্ষায়</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($sr['fulfillment_status'] === 'received'): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>মাঠে গৃহীত ও নিশ্চিত</span>
                                        <?php elseif ($sr['fulfillment_status'] === 'failed_delivery'): ?>
                                            <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>মাঠে পৌঁছায়নি</span>
                                            <?php if (!empty($sr['receipt_notes'])): ?>
                                                <div class="small text-danger mt-1"><?= e($sr['receipt_notes']) ?></div>
                                            <?php endif; ?>
                                        <?php elseif ($sr['status'] === 'approved' || $sr['fulfillment_status'] === 'dispatched'): ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>বরাদ্দকৃত — মাঠ প্রাপ্তি বাকি</span>
                                        <?php elseif ($sr['status'] === 'rejected'): ?>
                                            <span class="badge bg-danger">অস্বীকৃত</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">সিদ্ধান্ত অপেক্ষমাণ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if (($sr['status'] === 'approved' || $sr['fulfillment_status'] === 'dispatched') && $sr['fulfillment_status'] !== 'received' && $sr['fulfillment_status'] !== 'failed_delivery'): ?>
                                            <!-- Verification Action Buttons -->
                                            <div class="d-flex gap-2 justify-content-end">
                                                <!-- Acknowledge Receipt Form -->
                                                <form action="/dashboard/support-requests/<?= (int)$sr['id'] ?>/verify-receipt" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="fulfillment_status" value="received">
                                                    <input type="hidden" name="receipt_notes" value="মাঠে যন্ত্রপাতি ও দল যথাসময়ে উপস্থিত হয়েছে এবং কাজ চলছে।">
                                                    <button type="submit" class="btn btn-sm btn-success fw-bold text-nowrap" onclick="return confirm('আপনি কি নিশ্চিত যে যন্ত্রপাতি ও সহায়তা সাইটে বুঝে পেয়েছেন?')">
                                                        <i class="bi bi-check-lg me-1"></i>মাঠে পেয়েছি
                                                    </button>
                                                </form>

                                                <!-- Delivery Failed Report Modal Trigger -->
                                                <button type="button" class="btn btn-sm btn-outline-danger text-nowrap" data-bs-toggle="collapse" data-bs-target="#failed-report-<?= (int)$sr['id'] ?>">
                                                    <i class="bi bi-x-circle me-1"></i>পৌঁছায়নি
                                                </button>
                                            </div>
                                            <div class="collapse mt-2 text-start p-2 bg-light border border-danger-subtle rounded" id="failed-report-<?= (int)$sr['id'] ?>">
                                                <form action="/dashboard/support-requests/<?= (int)$sr['id'] ?>/verify-receipt" method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="fulfillment_status" value="failed_delivery">
                                                    <label class="form-label small text-danger fw-bold mb-1">সহায়তা না পৌঁছানোর কারণ লিখুন:</label>
                                                    <input type="text" name="receipt_notes" class="form-control form-control-sm mb-2" placeholder="উদাঃ নির্ধারিত সময়ে গাড়ি আসেনি, চালকের ফোন বন্ধ..." required>
                                                    <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold">
                                                        ব্যর্থতা রিপোর্ট জমা দিন (মেয়র ও সিইও-র কাছে অ্যালার্ট যাবে)
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted"><?= e($sr['receipt_notes'] ?? 'যাচাই সম্পন্ন') ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Verification Pending Tasks (Priority Queue) with Photo Upload -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-shield-check text-success me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'মাঠ পর্যায়ে সম্পন্ন কাজ — সুপারভাইজার যাচাই ও সমাধানের ছবি প্রমাণ' : 'Field Work Completed — Verification & Resolution Photo' ?>
            </h5>

            <?php
                $completedTasks = array_filter($tasks ?? [], fn($t) => $t['task_status'] === 'completed' || ($t['complaint_status'] ?? '') === 'work_completed');
            ?>

            <?php if (empty($completedTasks)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check2-circle fs-2 text-success d-block mb-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'বর্তমানে যাচাইয়ের জন্য কোনো কাজ অপেক্ষমাণ নেই।' : 'No tasks currently awaiting verification.' ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার ধরন ও স্থান' : 'Problem & Location' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'সম্পন্নকারী কর্মী' : 'Field Worker' ?></th>
                                <th><?= ($locale ?? 'bn') === 'bn' ? 'বর্তমান অবস্থা' : 'Status' ?></th>
                                <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'সমাধানের ছবি ও অনুমোদন' : 'Action' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completedTasks as $t): ?>
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-dark">
                                        <?= e($t['public_complaint_number'] ?? $t['tracking_number'] ?? '') ?>
                                    </td>
                                    <td>
                                        <strong><?= ($locale ?? 'bn') === 'bn' ? e($t['subcategory_name_bn'] ?? '') : e($t['subcategory_name_en'] ?? '') ?></strong>
                                        <small class="text-muted d-block">
                                            <?= e($t['landmark'] ? $t['landmark'] . ', ' : '') ?><?= e($t['approximate_address'] ?? '') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= ($locale ?? 'bn') === 'bn' ? e($t['worker_name_bn'] ?? 'মাঠকর্মী') : e($t['worker_name_en'] ?? 'Field Worker') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'যাচাই অপেক্ষমাণ' : 'Verification Needed' ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <form action="/dashboard/tasks/<?= (int)$t['id'] ?>/verify" method="POST" enctype="multipart/form-data" class="d-inline-flex align-items-center gap-2">
                                            <?= csrf_field() ?>
                                            <input type="file" name="photo" accept="image/*" class="form-control form-control-sm" style="max-width: 170px;" title="কাজের পরের ছবি সংযুক্ত করুন">
                                            <button type="submit" class="btn btn-sm btn-success fw-bold text-nowrap">
                                                <i class="bi bi-check-lg me-1"></i>
                                                <?= ($locale ?? 'bn') === 'bn' ? 'অনুমোদন করুন' : 'Verify & Approve' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- All Ward Field Tasks with Live Filter -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-list-task text-primary me-2"></i>ওয়ার্ডের সার্বিক ফিল্ড টাস্ক ও অভিযোগ তালিকা
                </h5>
                <span class="badge bg-secondary-subtle text-secondary"><?= count($tasks ?? []) ?> টি মোট টাস্ক</span>
            </div>

            <!-- Task Filter Toolbar -->
            <div class="card border-0 bg-light rounded-3 p-3 mb-3 table-filter-toolbar" data-filter-target="#supervisor-tasks-table">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 filter-search" placeholder="ট্র্যাকিং নং, কর্মী বা এলাকা খুঁজুন...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select filter-status">
                            <option value="">সকল অবস্থা (All Status)</option>
                            <option value="pending">বরাদ্দ বাকি (Pending)</option>
                            <option value="in_progress">চলমান (In Progress)</option>
                            <option value="completed">সম্পন্ন (Completed)</option>
                            <option value="verified">যাচাইকৃত (Verified)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100 filter-reset">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>রিসেট
                        </button>
                    </div>
                </div>
                <div class="mt-2 text-muted small filter-count"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="supervisor-tasks-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">টাস্ক কোড ও ট্র্যাকিং</th>
                            <th>কাজের ধরন ও স্থান</th>
                            <th>বরাদ্দকৃত কর্মী / দল</th>
                            <th>অবস্থা</th>
                            <th class="text-end pe-3">বিস্তারিত</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">ওয়ার্ডে কোনো টাস্ক নেই।</td></tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $tk): ?>
                                <?php
                                    $search = ($tk['task_code'] ?? '') . ' ' . ($tk['public_complaint_number'] ?? '') . ' ' . ($tk['subcategory_name_bn'] ?? '') . ' ' . ($tk['landmark'] ?? '') . ' ' . ($tk['worker_name_bn'] ?? '');
                                ?>
                                <tr data-search="<?= e($search) ?>" data-status="<?= e($tk['task_status'] ?? '') ?>">
                                    <td class="ps-3 font-monospace fw-bold text-dark">
                                        <?= e($tk['public_complaint_number'] ?? '') ?>
                                        <small class="d-block text-muted"><?= e($tk['task_code'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <strong><?= e($tk['subcategory_name_bn'] ?? '') ?></strong>
                                        <small class="text-muted d-block"><?= e($tk['landmark'] ?? $tk['approximate_address'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($tk['team_leader_name_bn'])): ?>
                                            <strong class="text-dark d-block">
                                                <i class="bi bi-person-badge text-warning-emphasis me-1"></i><?= e($tk['team_leader_name_bn']) ?>
                                            </strong>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                                                <i class="bi bi-people-fill me-1"></i><?= to_bn_number((string)($tk['worker_count'] ?? 1)) ?> জন পরিচ্ছন্নতাকর্মী
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted"><?= e($tk['worker_name_bn'] ?? $tk['team_name_bn'] ?? 'বরাদ্দ বাকি') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            <?= e($tk['task_status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="/track/<?= urlencode($tk['public_complaint_number'] ?? '') ?>" class="btn btn-sm btn-outline-primary">
                                            ট্র্যাক করুন &rarr;
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

<!-- Modal: Request Emergency Manpower Support (Workforce Shortage) -->
<div class="modal fade" id="manpowerShortageModal" tabindex="-1" aria-labelledby="manpowerShortageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="manpowerShortageModalLabel">
                    <i class="bi bi-people-fill me-2"></i>জরুরি অতিরিক্ত পরিচ্ছন্নতাকর্মী রিকুইজিশন
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/dashboard/workforce/request" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-warning small border-0 bg-warning-subtle text-warning-emphasis mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        ওয়ার্ডে পরিচ্ছন্নতাকর্মী সংকট বা ভারী বর্জ্যের জন্য অতিরিক্ত জনবল প্রয়োজন হলে সরাসরি প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তার নিকট রিকুইজিশন পাঠান। কেন্দ্রীয় রিজার্ভ পুল বা অন্য ওয়ার্ড থেকে কর্মী সাময়িক ডেপুটেশনে পাঠানো হবে।
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">সংশ্লিষ্ট অভিযোগ নির্বাচন করুন *</label>
                        <select name="complaint_id" class="form-select" required>
                            <option value="">-- অভিযোগ নির্বাচন করুন --</option>
                            <?php foreach ($unassignedComplaints ?? [] as $uc): ?>
                                <option value="<?= (int)$uc['id'] ?>">
                                    <?= e($uc['public_complaint_number']) ?> — <?= e($uc['subcategory_name_bn']) ?> (<?= e($uc['landmark'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                            <?php foreach ($tasks ?? [] as $tk): ?>
                                <option value="<?= (int)$tk['complaint_id'] ?>">
                                    <?= e($tk['public_complaint_number']) ?> — <?= e($tk['subcategory_name_bn']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">কতজন অতিরিক্ত কর্মী প্রয়োজন? *</label>
                        <div class="input-group">
                            <input type="number" name="worker_count" min="1" max="50" value="5" class="form-control form-control-lg fw-bold text-center" required>
                            <span class="input-group-text">জন পরিচ্ছন্নতাকর্মী</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">কর্মী সংকটের কারণ ও কাজের স্থান *</label>
                        <textarea name="details" rows="3" required class="form-control"
                                  placeholder="উদাঃ নর্দমার গভীর কাদা ও ভারী বর্জ্য অপসারণের জন্য ওয়ার্ডের নিয়মিত কর্মী অপ্রতুল, জরুরি ৫ জন কর্মী প্রয়োজন..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-danger fw-bold">
                        <i class="bi bi-send-fill me-1"></i>রিকুইজিশন পাঠান
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Request Cross-Department Support -->
<div class="modal fade" id="supportRequestModal" tabindex="-1" aria-labelledby="supportRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="supportRequestModalLabel">
                    <i class="bi bi-diagram-3-fill me-2"></i>আন্তঃবিভাগীয় সহায়তা আবেদন
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/dashboard/support-requests/create" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">সংশ্লিষ্ট অভিযোগ নির্বাচন করুন *</label>
                        <select name="complaint_id" class="form-select" required>
                            <option value="">-- অভিযোগ বেছে নিন --</option>
                            <?php foreach ($tasks ?? [] as $tk): ?>
                                <option value="<?= (int)$tk['complaint_id'] ?>">
                                    <?= e($tk['public_complaint_number']) ?> — <?= e($tk['subcategory_name_bn']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">কোন বিভাগের সহায়তা প্রয়োজন? *</label>
                        <select name="target_department_id" class="form-select" required>
                            <option value="">-- বিভাগ নির্বাচন করুন --</option>
                            <?php foreach ($departments ?? [] as $dept): ?>
                                <option value="<?= (int)$dept['id'] ?>">
                                    <?= e($dept['name_bn']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">সহায়তার ধরন *</label>
                        <select name="support_type" class="form-select" required>
                            <option value="machinery">ভারী যন্ত্রপাতি / বুলডোজার / হাইড্রোলিক ক্রেন</option>
                            <option value="extra_manpower">জরুরি অতিরিক্ত জনবল ও পরিচ্ছন্নতাকর্মী</option>
                            <option value="electrical_cutting">বিদ্যুৎ সংযোগ বিচ্ছিন্ন / সড়কবাতি মেরামত ভ্যান</option>
                            <option value="engineering_slab">ম্যানহোল স্ল্যাব / গভীর ড্রেন কাটিং</option>
                            <option value="joint_team">যৌথ টাস্কফোর্স ও সমন্বিত অভিযান</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">প্রয়োজনীয় সহায়তার বিস্তারিত কারণ *</label>
                        <textarea name="details" rows="3" required class="form-control"
                                  placeholder="উদাঃ নর্দমার ওপর ভারী কংক্রিট স্ল্যাব ভাঙার জন্য হাইড্রোলিক ব্রেকার প্রয়োজন..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold">
                        <i class="bi bi-send-fill me-1"></i>সহায়তা আবেদন পাঠান
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
