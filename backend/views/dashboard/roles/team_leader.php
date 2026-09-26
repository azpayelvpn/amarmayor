<!-- Field Team Leader Dashboard -->
<div class="row g-4 mb-4">
    <!-- Header & Action Bar -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-people-fill text-warning-emphasis me-2"></i>
                        দলনেতা — মাঠের কাজের রুট ও পরিচ্ছন্নতাকর্মী সমন্বয় হাব
                    </h5>
                    <small class="text-muted">দলের পরিচ্ছন্নতাকর্মীদের রুট বণ্টন, কাজের অগ্রগতি তদারকি ও ওয়ার্ড সুপারভাইজারকে তাৎক্ষণিক রিপোর্ট প্রেরণ</small>
                </div>
                <button type="button" class="btn btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#teamProgressModal">
                    <i class="bi bi-check-all me-1"></i>দলের কাজের প্রগ্রেস রিপোর্ট করুন
                </button>
            </div>

            <!-- Assigned Tasks Count -->
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-primary"><?= count($tasks ?? []) ?></div>
                        <small class="text-muted">দলের আজকের মোট কাজ</small>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-success"><?= count($teamMembers ?? []) ?></div>
                        <small class="text-muted">দলে নিয়োজিত সক্রিয় কর্মী</small>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <div class="fs-2 fw-bold text-warning-emphasis"><?= count($recentTeamReports ?? []) ?></div>
                        <small class="text-muted">সুপারভাইজারকে দাখিলকৃত রিপোর্ট</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members Roster -->
    <?php if (!empty($teamMembers)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-person-lines-fill text-secondary me-2"></i>আপনার অধীনে নিয়োজিত মাঠ পরিচ্ছন্নতাকর্মীদের তালিকা
                </h6>
                <div class="row g-2">
                    <?php foreach ($teamMembers as $tm): ?>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-2 border rounded-3 bg-light d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:32px; height:32px; font-size: 0.9rem;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <strong class="d-block text-truncate small text-dark"><?= e($tm['full_name_bn']) ?></strong>
                                    <small class="text-muted font-monospace" style="font-size: 0.75rem;"><?= e($tm['employee_code']) ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Field Tasks Table -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h6 class="fw-bold text-dark mb-3">আজকের জন্য বরাদ্দকৃত মাঠের কাজের তালিকা</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">টাস্ক কোড ও ট্র্যাকিং</th>
                            <th>কাজের ধরন ও নিয়োজিত কর্মী</th>
                            <th>স্থান / সড়ক</th>
                            <th>বর্তমান অবস্থা</th>
                            <th class="text-end pe-3">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">আজকের জন্য কোনো কাজ নির্ধারিত নেই।</td></tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $t): ?>
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-dark">
                                        <?= e($t['public_complaint_number'] ?? ('#TASK-' . $t['id'])) ?>
                                        <small class="d-block text-muted"><?= e($t['task_code'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <strong><?= e($t['subcategory_name_bn'] ?? 'ফিল্ড অপারেশন') ?></strong>
                                        <div class="mt-1">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                                                <i class="bi bi-people-fill me-1"></i><?= to_bn_number((string)($t['worker_count'] ?? 1)) ?> জন পরিচ্ছন্নতাকর্মী
                                            </span>
                                        </div>
                                    </td>
                                    <td class="small text-muted">
                                        <?= e($t['landmark'] ? $t['landmark'] . ', ' : '') ?><?= e($t['approximate_address'] ?? 'ওয়ার্ড এলাকা') ?>
                                        <?php if (!empty($t['instructions'])): ?>
                                            <div class="text-dark fw-semibold mt-1"><i class="bi bi-info-circle me-1"></i><?= e($t['instructions']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (($t['task_status'] ?? '') === 'in_progress'): ?>
                                            <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-gear-wide-connected me-1"></i>চলমান</span>
                                        <?php elseif (($t['task_status'] ?? '') === 'completed'): ?>
                                            <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>সম্পন্ন</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary px-2 py-1">বরাদ্দকৃত (শুরু বাকি)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <?php if (($t['task_status'] ?? '') === 'pending'): ?>
                                                <form action="/dashboard/tasks/<?= (int)$t['id'] ?>/start" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-success fw-bold text-nowrap">
                                                        <i class="bi bi-play-fill me-1"></i>কাজ শুরু
                                                    </button>
                                                </form>
                                            <?php elseif (($t['task_status'] ?? '') === 'in_progress'): ?>
                                                <form action="/dashboard/tasks/<?= (int)$t['id'] ?>/complete" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="notes" value="দলনেতা ও কর্মীরা সাইটের কাজ সফলভাবে সম্পন্ন করেছেন।">
                                                    <button type="submit" class="btn btn-sm btn-primary fw-bold text-nowrap">
                                                        <i class="bi bi-check-lg me-1"></i>কাজ শেষ
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#teamProgressModal" data-task-id="<?= (int)$t['id'] ?>" data-complaint-id="<?= (int)($t['complaint_id'] ?? 0) ?>">
                                                    রিপোর্ট
                                                </button>
                                            <?php else: ?>
                                                <small class="text-muted">সুপারভাইজার যাচাই করছেন</small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Team Reports -->
    <?php if (!empty($recentTeamReports)): ?>
        <div class="col-12">
            <div class="card border shadow-sm rounded-4 bg-white p-4">
                <h6 class="fw-bold text-dark mb-3">সুপারভাইজারকে সম্প্রতি প্রেরিত টিম রিপোর্ট</h6>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentTeamReports as $rtr): ?>
                        <div class="list-group-item px-0 py-2 border-0 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-success-subtle text-success me-2">দাখিলকৃত</span>
                                <span class="small text-dark"><?= e($rtr['note_text']) ?></span>
                            </div>
                            <small class="text-muted font-monospace"><?= e($rtr['created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Report Team Progress -->
<div class="modal fade" id="teamProgressModal" tabindex="-1" aria-labelledby="progModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/dashboard/team-leader/report-progress" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="progModalLabel">
                    <i class="bi bi-send-check me-2"></i>দলের কাজের প্রগ্রেস ও রুট রিপোর্ট
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">সংশ্লিষ্ট কাজের আইডি বা অভিযোগ আইডি</label>
                    <input type="number" name="complaint_id" class="form-control" placeholder="যেমন: 1" value="<?= !empty($tasks) ? (int)($tasks[0]['complaint_id'] ?? 1) : 1 ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">সড়কের নাম / যে অংশে কাজ হয়েছে</label>
                    <input type="text" name="road_name" class="form-control" placeholder="যেমন: জিলা স্কুল রোড থেকে বড় বাজার মোড় পর্যন্ত" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">কাজের অগ্রগতি অবস্থা</label>
                    <select name="progress_status" class="form-select">
                        <option value="completed">সড়ক ও ড্রেন পরিষ্কার সম্পূর্ণ (Completed)</option>
                        <option value="partially_done">আংশিক সম্পন্ন, আগামীকাল বাকি অংশ (Partial)</option>
                        <option value="blocked">মাঠে বাধা আছে, অতিরিক্ত সরঞ্জাম লাগবে (Blocked)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">দলনেতার মন্তব্য / কত ভ্যান বর্জ্য অপসারণ হলো</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="যেমন: ৩ ভ্যান বর্জ্য অপসারণ করে ডাম্পিং পয়েন্টে পাঠানো হয়েছে..." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-warning fw-bold text-dark">সুপারভাইজারকে রিপোর্ট পাঠান</button>
            </div>
        </form>
    </div>
</div>
