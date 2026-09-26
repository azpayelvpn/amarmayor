<?php
/**
 * Complaint Detail & Full Lifecycle View
 * GET /dashboard/complaints/{number}
 * Accessible to all authenticated staff roles.
 */
?>
<div class="container py-4">

    <!-- Back Navigation -->
    <div class="mb-3">
        <a href="/dashboard" class="btn btn-sm btn-outline-secondary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i>ড্যাশবোর্ডে ফিরুন
        </a>
    </div>

    <?php
        $c = $complaint;
        $statusLabels = [
            'submitted'       => ['label' => 'নতুন দাখিল', 'color' => 'secondary'],
            'review_required' => ['label' => 'পর্যালোচনা প্রয়োজন', 'color' => 'warning'],
            'assigned'        => ['label' => 'বরাদ্দকৃত', 'color' => 'primary'],
            'work_in_progress'=> ['label' => 'কাজ চলছে', 'color' => 'info'],
            'work_completed'  => ['label' => 'কাজ সম্পন্ন', 'color' => 'success'],
            'resolved'        => ['label' => 'সমাধান সম্পন্ন', 'color' => 'success'],
            'verified'        => ['label' => 'যাচাইকৃত ও বন্ধ', 'color' => 'success'],
            'reopened'        => ['label' => 'পুনরায় খোলা হয়েছে', 'color' => 'danger'],
            'closed'          => ['label' => 'বন্ধ', 'color' => 'dark'],
        ];
        $st = $statusLabels[$c['internal_status'] ?? 'submitted'] ?? ['label' => e($c['internal_status'] ?? ''), 'color' => 'secondary'];
    ?>

    <!-- Complaint Header Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-dark font-monospace fs-6 px-3 py-2 rounded-3">
                            <?= e($c['public_complaint_number'] ?? '') ?>
                        </span>
                        <span class="badge bg-<?= $st['color'] ?> px-3 py-2 fs-6 rounded-pill">
                            <?= $st['label'] ?>
                        </span>
                        <?php if (!empty($c['priority'])): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($c['priority']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">
                        <?= e($c['subcategory_name_bn'] ?? 'অভিযোগ') ?>
                    </h4>
                    <div class="text-muted small">
                        <i class="bi bi-folder2-open me-1"></i><?= e($c['category_name_bn'] ?? '') ?>
                        &bull;
                        <i class="bi bi-building me-1"></i><?= e($c['department_name_bn'] ?? '') ?>
                    </div>
                </div>
                <div class="text-end">
                    <?php if (!empty($c['ward_number'])): ?>
                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-2 rounded-3">
                            <i class="bi bi-geo me-1"></i>ওয়ার্ড <?= to_bn_number((string)$c['ward_number']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="text-muted small mt-1">
                        দাখিল: <?= e(date('d M Y, H:i', strtotime($c['created_at'] ?? 'now'))) ?>
                    </div>
                </div>
            </div>
            <?php if (!empty($c['description'])): ?>
                <div class="p-3 bg-light rounded-3 border mt-3">
                    <strong class="small text-muted d-block mb-1">নাগরিকের অভিযোগ:</strong>
                    <p class="mb-0 text-dark"><?= nl2br(e($c['description'])) ?></p>
                </div>
            <?php endif; ?>
            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <div class="p-3 bg-info-subtle rounded-3 border border-info-subtle h-100">
                        <strong class="small text-info-emphasis d-block mb-1">
                            <i class="bi bi-geo-alt-fill me-1"></i>অভিযোগের স্থান
                        </strong>
                        <?php if (!empty($c['landmark'])): ?>
                            <div class="fw-semibold text-dark"><?= e($c['landmark']) ?></div>
                        <?php endif; ?>
                        <div class="small text-muted"><?= e($c['approximate_address'] ?? $c['public_safe_address'] ?? 'অজানা স্থান') ?></div>
                        <?php if (!empty($c['latitude']) && !empty($c['longitude'])): ?>
                            <a href="https://www.google.com/maps?q=<?= e($c['latitude']) ?>,<?= e($c['longitude']) ?>"
                               target="_blank" class="btn btn-sm btn-outline-info mt-2 text-nowrap">
                                <i class="bi bi-map me-1"></i>মানচিত্রে দেখুন
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-secondary-subtle rounded-3 border border-secondary-subtle h-100">
                        <strong class="small text-secondary-emphasis d-block mb-1">
                            <i class="bi bi-person-circle me-1"></i>অভিযোগকারী নাগরিক
                        </strong>
                        <div class="fw-semibold text-dark"><?= e($c['citizen_name_bn'] ?? 'নাম অজানা') ?></div>
                        <?php if (!empty($c['citizen_phone'])): ?>
                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i><?= e($c['citizen_phone']) ?></div>
                        <?php endif; ?>
                        <div class="small text-muted mt-1">
                            <i class="bi bi-calendar3 me-1"></i>দাখিলের তারিখ: <?= e(date('d M Y', strtotime($c['created_at'] ?? 'now'))) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- Left Column: Operations -->
        <div class="col-lg-8">

            <!-- Field Tasks -->
            <div class="card border shadow-sm rounded-4 bg-white mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-people-fill text-primary me-2"></i>মাঠ পর্যায়ে মোতায়েন দল
                        <span class="badge bg-secondary-subtle text-secondary ms-2"><?= count($fieldTasks) ?> টি</span>
                    </h5>
                    <?php if (empty($fieldTasks)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-hourglass fs-3 d-block mb-2"></i>
                            এখনো কোনো দল মোতায়েন করা হয়নি।
                        </div>
                    <?php else: ?>
                        <?php foreach ($fieldTasks as $ft): ?>
                            <?php
                                $ftStatusMap = [
                                    'pending'     => ['label' => 'অপেক্ষমাণ', 'color' => 'warning'],
                                    'in_progress' => ['label' => 'কাজ চলছে', 'color' => 'primary'],
                                    'completed'   => ['label' => 'সম্পন্ন', 'color' => 'success'],
                                    'verified'    => ['label' => 'যাচাইকৃত', 'color' => 'success'],
                                    'cancelled'   => ['label' => 'বাতিল', 'color' => 'danger'],
                                ];
                                $fts = $ftStatusMap[$ft['task_status'] ?? 'pending'] ?? ['label' => e($ft['task_status']), 'color' => 'secondary'];
                            ?>
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <div>
                                        <span class="font-monospace fw-bold text-primary"><?= e($ft['task_code'] ?? '') ?></span>
                                        <span class="badge bg-<?= $fts['color'] ?> ms-2"><?= $fts['label'] ?></span>
                                    </div>
                                    <small class="text-muted">মোতায়েন: <?= e(date('d M Y, H:i', strtotime($ft['created_at'] ?? 'now'))) ?></small>
                                </div>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <strong class="small text-muted d-block">মাঠ দলনেতা:</strong>
                                        <span class="fw-semibold text-dark">
                                            <i class="bi bi-person-badge text-warning-emphasis me-1"></i><?= e($ft['team_leader_name_bn'] ?? 'বরাদ্দ হয়নি') ?>
                                        </span>
                                        <?php if (!empty($ft['team_leader_phone'])): ?>
                                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i><?= e($ft['team_leader_phone']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-sm-3">
                                        <strong class="small text-muted d-block">কর্মী সংখ্যা:</strong>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-1">
                                            <?= to_bn_number((string)($ft['worker_count'] ?? 0)) ?> জন
                                        </span>
                                    </div>
                                    <div class="col-sm-3">
                                        <strong class="small text-muted d-block">সুপারভাইজার:</strong>
                                        <span class="small text-dark"><?= e($ft['supervisor_name_bn'] ?? '') ?></span>
                                    </div>
                                </div>
                                <?php if (!empty($ft['task_notes'])): ?>
                                    <div class="small text-muted mt-2 border-top pt-2">
                                        <i class="bi bi-chat-text me-1"></i><?= e($ft['task_notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Workforce Requisitions (extra_manpower) -->
            <div class="card border shadow-sm rounded-4 bg-white mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-people-fill text-danger me-2"></i>জনবল রিকুইজিশন (Workforce Requisition)
                        <span class="badge bg-secondary-subtle text-secondary ms-2"><?= count($workforceRequests) ?> টি</span>
                    </h5>
                    <?php if (empty($workforceRequests)): ?>
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-check2-circle fs-3 d-block mb-2 text-success"></i>
                            এই অভিযোগের জন্য কোনো জনবল রিকুইজিশন করা হয়নি।
                        </div>
                    <?php else: ?>
                        <?php foreach ($workforceRequests as $wr): ?>
                            <?php
                                $isApproved  = $wr['status'] === 'approved';
                                $isPending   = $wr['status'] === 'pending';
                                $isRejected  = $wr['status'] === 'rejected';
                                $isDispatched = $wr['fulfillment_status'] === 'dispatched';
                                $isReceived  = $wr['fulfillment_status'] === 'received';
                                $cardClass   = ($isApproved || $isDispatched || $isReceived)
                                    ? 'bg-success-subtle border-success-subtle'
                                    : ($isPending ? 'bg-warning-subtle border-warning-subtle' : 'bg-light');
                            ?>
                            <div class="p-3 rounded-3 border mb-3 <?= $cardClass ?>">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                    <div>
                                        <strong class="text-dark">
                                            <?= to_bn_number((string)($wr['requested_worker_count'] ?? 0)) ?> জন পরিচ্ছন্নতাকর্মীর রিকুইজিশন
                                        </strong>
                                        <div class="small text-muted">
                                            অনুরোধকারী: <?= e($wr['requester_name_bn'] ?? 'সুপারভাইজার') ?>
                                            <?php if (!empty($wr['requester_designation'])): ?> &bull; <?= e($wr['requester_designation']) ?><?php endif; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <?php if ($isReceived): ?>
                                            <span class="badge bg-success px-3 py-2">
                                                <i class="bi bi-check2-circle me-1"></i>মাঠে গৃহীত ও সক্রিয়
                                            </span>
                                        <?php elseif ($isDispatched || $isApproved): ?>
                                            <span class="badge bg-success px-3 py-2">
                                                <i class="bi bi-check-circle-fill me-1"></i>✅ অনুমোদিত ও প্রেরিত
                                            </span>
                                        <?php elseif ($isPending): ?>
                                            <span class="badge bg-warning text-dark px-3 py-2">
                                                <i class="bi bi-hourglass-split me-1"></i>বিভাগীয় প্রধানের সিদ্ধান্ত অপেক্ষমাণ
                                            </span>
                                        <?php elseif ($isRejected): ?>
                                            <span class="badge bg-danger px-3 py-2">
                                                <i class="bi bi-x-circle me-1"></i>প্রত্যাখ্যাত
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="small text-muted mb-2">
                                    <i class="bi bi-chat-text me-1"></i><?= e($wr['details'] ?? '') ?>
                                </div>
                                <?php if ($isApproved || $isDispatched || $isReceived): ?>
                                    <div class="mt-2 p-2 bg-white rounded-2 border border-success-subtle">
                                        <div class="row g-2">
                                            <div class="col-sm-4">
                                                <strong class="small text-success-emphasis d-block">বরাদ্দকৃত কর্মী:</strong>
                                                <span class="fw-bold text-success fs-5"><?= to_bn_number((string)($wr['allocated_worker_count'] ?? 0)) ?> জন</span>
                                            </div>
                                            <div class="col-sm-4">
                                                <strong class="small text-success-emphasis d-block">উৎস / সরবরাহ:</strong>
                                                <span class="small text-dark"><?= e($wr['allocated_resource'] ?? $wr['response_notes'] ?? 'কেন্দ্রীয় রিজার্ভ') ?></span>
                                            </div>
                                            <div class="col-sm-4">
                                                <strong class="small text-success-emphasis d-block">অনুমোদনকারী:</strong>
                                                <span class="small text-dark"><?= e($wr['approver_name_bn'] ?? 'বিভাগীয় প্রধান') ?></span>
                                            </div>
                                        </div>
                                        <?php if (!empty($wr['allocated_operator'])): ?>
                                            <div class="small text-muted mt-1">
                                                <i class="bi bi-person me-1"></i>প্রতিনিধি: <?= e($wr['allocated_operator']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="small text-muted mt-2">
                                    <i class="bi bi-clock me-1"></i>রিকুইজিশন: <?= e(date('d M Y, H:i', strtotime($wr['created_at'] ?? 'now'))) ?>
                                    <?php if (!empty($wr['responded_at'])): ?>
                                        &bull; সিদ্ধান্ত: <?= e(date('d M Y, H:i', strtotime($wr['responded_at']))) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cross-Department Support Requests -->
            <?php if (!empty($supportRequests)): ?>
                <div class="card border shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">
                            <i class="bi bi-diagram-3-fill text-primary me-2"></i>আন্তঃবিভাগীয় সহায়তা আবেদন
                            <span class="badge bg-secondary-subtle text-secondary ms-2"><?= count($supportRequests) ?> টি</span>
                        </h5>
                        <?php foreach ($supportRequests as $sr): ?>
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <div>
                                        <span class="badge bg-info text-dark"><?= e($sr['support_type'] ?? '') ?></span>
                                        <strong class="small text-dark ms-2"><?= e($sr['target_department_name_bn'] ?? 'সংশ্লিষ্ট বিভাগ') ?></strong>
                                    </div>
                                    <?php if ($sr['status'] === 'approved'): ?>
                                        <span class="badge bg-success">✅ অনুমোদিত</span>
                                    <?php elseif ($sr['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">অপেক্ষমাণ</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">প্রত্যাখ্যাত</span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted"><?= e($sr['details'] ?? '') ?></div>
                                <?php if (!empty($sr['allocated_resource'])): ?>
                                    <div class="small text-dark mt-1">
                                        <i class="bi bi-truck me-1"></i><?= e($sr['allocated_resource']) ?>
                                        <?php if (!empty($sr['allocated_operator'])): ?> — <?= e($sr['allocated_operator']) ?><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Evidence / Photos -->
            <?php if (!empty($evidence)): ?>
                <div class="card border shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">
                            <i class="bi bi-camera-fill text-success me-2"></i>ছবির প্রমাণ (Field Evidence)
                        </h5>
                        <div class="row g-3">
                            <?php foreach ($evidence as $ev): ?>
                                <div class="col-sm-4">
                                    <div class="border rounded-3 overflow-hidden">
                                        <?php if (!empty($ev['file_path'])): ?>
                                            <img src="/<?= e(ltrim($ev['file_path'], '/')) ?>" class="img-fluid" alt="ছবির প্রমাণ">
                                        <?php endif; ?>
                                        <div class="p-2 bg-light small text-muted"><?= e($ev['evidence_type'] ?? '') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div><!-- end col-lg-8 -->

        <!-- Right Column: Internal Notes Timeline -->
        <div class="col-lg-4">
            <div class="card border shadow-sm rounded-4 bg-white" style="position: sticky; top: 80px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-clock-history text-primary me-2"></i>অভ্যন্তরীণ কার্যক্রমের ইতিহাস
                        <span class="badge bg-secondary-subtle text-secondary ms-1"><?= count($internalNotes) ?></span>
                    </h6>
                    <?php if (empty($internalNotes)): ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-journal-x fs-3 d-block mb-2"></i>এখনো কোনো নোট নেই।
                        </div>
                    <?php else: ?>
                        <div style="max-height: 500px; overflow-y: auto;">
                            <?php
                                $noteTypeIcons = [
                                    'coordination'  => ['icon' => 'bi-diagram-3',       'color' => 'text-primary'],
                                    'team_progress' => ['icon' => 'bi-person-check',     'color' => 'text-success'],
                                    'inspection'    => ['icon' => 'bi-clipboard-check',  'color' => 'text-info'],
                                    'escalation'    => ['icon' => 'bi-exclamation-triangle','color'=>'text-danger'],
                                    'resolution'    => ['icon' => 'bi-check-circle',     'color' => 'text-success'],
                                    'dispatch'      => ['icon' => 'bi-send',             'color' => 'text-primary'],
                                    'default'       => ['icon' => 'bi-chat-left-text',   'color' => 'text-secondary'],
                                ];
                            ?>
                            <?php foreach ($internalNotes as $note): ?>
                                <?php
                                    $nt = $noteTypeIcons[$note['note_type'] ?? 'default'] ?? $noteTypeIcons['default'];
                                ?>
                                <div class="d-flex gap-2 mb-3 pb-3 border-bottom">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width:30px;height:30px;">
                                            <i class="bi <?= $nt['icon'] ?> small <?= $nt['color'] ?>"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="small text-dark"><?= e($note['author_name_bn'] ?? $note['username'] ?? 'সিস্টেম') ?></strong>
                                            <span class="badge bg-light text-muted border small"><?= e($note['author_role_bn'] ?? $note['note_type'] ?? '') ?></span>
                                        </div>
                                        <div class="small text-dark"><?= nl2br(e($note['note_text'] ?? '')) ?></div>
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-clock me-1"></i><?= e(date('d M Y, H:i', strtotime($note['created_at'] ?? 'now'))) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Quick Note Form -->
                    <?php if (in_array($primaryRole, ['supervisor','ward_officer','zone_officer','department_head','mayor','administrator','ceo','responsible_officer'])): ?>
                        <div class="border-top pt-3 mt-2">
                            <h6 class="fw-semibold text-muted small mb-2">
                                <i class="bi bi-pencil me-1"></i>অভ্যন্তরীণ নোট যোগ করুন:
                            </h6>
                            <form action="/dashboard/complaints/<?= (int)$complaint['id'] ?>/internal-notes" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="complaint_id" value="<?= (int)$complaint['id'] ?>">
                                <input type="hidden" name="note_type" value="inspection">
                                <textarea name="note_text" rows="2" required class="form-control form-control-sm mb-2"
                                          placeholder="অভিযোগ সম্পর্কে অভ্যন্তরীণ মন্তব্য লিখুন..."></textarea>
                                <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">
                                    <i class="bi bi-send-fill me-1"></i>নোট যোগ করুন
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div><!-- end col-lg-4 -->

    </div><!-- end row -->
</div>