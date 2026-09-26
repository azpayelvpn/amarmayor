<!-- Department Operational Dashboard -->
<div class="row g-4 mb-4">
    <!-- Cross-Department Support Requests (Inter-Department Coordination) -->
    <?php if (!empty($supportRequests)): ?>
        <div class="col-12">
            <div class="card border-primary shadow-sm rounded-4 bg-white p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-diagram-3-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'আন্তঃবিভাগীয় সমন্বয় ও সহায়তা আবেদন' : 'Cross-Department Support Requests' ?>
                    </h5>
                    <span class="badge bg-primary rounded-pill px-3 py-1"><?= count($supportRequests) ?> টি আবেদন</span>
                </div>
                <p class="text-muted small mb-3">অন্যান্য বিভাগ বা ওয়ার্ড সুপারভাইজার কর্তৃক আপনার বিভাগের কারিগরি সরঞ্জাম বা লজিস্টিক সহায়তা চাওয়া হয়েছে:</p>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">অভিযোগ নং ও ওয়ার্ড</th>
                                <th>আবেদনকারী কর্মকর্তা</th>
                                <th>সহায়তার ধরন ও বিবরণ</th>
                                <th>বরাদ্দকৃত রিসোর্স ও চালক</th>
                                <th>মাঠ প্রাপ্তি অবস্থা</th>
                                <th class="text-end pe-3">বিভাগীয় সিদ্ধান্ত ও টিম প্রেরণ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($supportRequests as $sr): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="font-monospace fw-bold text-dark"><?= e($sr['public_complaint_number']) ?></div>
                                        <span class="badge bg-secondary-subtle text-secondary small">
                                            ওয়ার্ড <?= to_bn_number((string)($sr['ward_number'] ?? '1')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= e($sr['requester_name_bn'] ?? 'ওয়ার্ড সুপারভাইজার') ?></strong>
                                        <small class="text-muted d-block"><?= e($sr['requester_phone'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark mb-1"><?= e($sr['support_type']) ?></span>
                                        <div class="small text-secondary"><?= e($sr['details']) ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($sr['allocated_resource']) || !empty($sr['allocated_operator'])): ?>
                                            <div class="small fw-semibold text-dark"><i class="bi bi-truck me-1"></i><?= e($sr['allocated_resource'] ?? 'নির্দিষ্ট নেই') ?></div>
                                            <div class="small text-muted"><i class="bi bi-person me-1"></i><?= e($sr['allocated_operator'] ?? '') ?></div>
                                            <?php if (!empty($sr['scheduled_arrival'])): ?>
                                                <div class="small text-primary"><i class="bi bi-clock me-1"></i><?= e($sr['scheduled_arrival']) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">এখনও বরাদ্দ দেওয়া হয়নি</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($sr['fulfillment_status'] === 'received'): ?>
                                            <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>মাঠে গৃহীত ও সক্রিয়</span>
                                        <?php elseif ($sr['fulfillment_status'] === 'failed_delivery'): ?>
                                            <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>মাঠে পৌঁছায়নি</span>
                                            <?php if (!empty($sr['receipt_notes'])): ?>
                                                <div class="small text-danger mt-1"><?= e($sr['receipt_notes']) ?></div>
                                            <?php endif; ?>
                                        <?php elseif ($sr['status'] === 'approved'): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary"><i class="bi bi-arrow-right-circle me-1"></i>রওয়ানা / মাঠ যাচাই অপেক্ষমাণ</span>
                                        <?php elseif ($sr['status'] === 'rejected'): ?>
                                            <span class="badge bg-danger-subtle text-danger">বাতিল</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">সিদ্ধান্ত অপেক্ষমাণ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if ($sr['status'] === 'pending'): ?>
                                            <!-- Structured Approval Modal Trigger / Form -->
                                            <button type="button" class="btn btn-sm btn-success fw-bold text-nowrap" data-bs-toggle="collapse" data-bs-target="#alloc-form-<?= (int)$sr['id'] ?>">
                                                <i class="bi bi-check2-square me-1"></i>বরাদ্দ ও অনুমোদন
                                            </button>
                                            <div class="collapse mt-2 text-start p-3 bg-light rounded-3 border" id="alloc-form-<?= (int)$sr['id'] ?>" style="min-width: 290px;">
                                                <?php if ($sr['support_type'] === 'extra_manpower'): ?>
                                                    <form action="/dashboard/workforce/allocate" method="POST">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="support_request_id" value="<?= (int)$sr['id'] ?>">

                                                        <div class="mb-2">
                                                            <label class="form-label small fw-bold mb-1">কর্মী বরাদ্দের উৎস:</label>
                                                            <select name="source_type" class="form-select form-select-sm" onchange="const el=document.getElementById('donor-ward-<?= (int)$sr['id'] ?>'); if(this.value==='deputation'){el.classList.remove('d-none');}else{el.classList.add('d-none');}">
                                                                <option value="reserve_pool">🏛️ কেন্দ্রীয় জরুরি রিজার্ভ পুল</option>
                                                                <option value="deputation">🔄 অন্য ওয়ার্ড থেকে ডেপুটেশন</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-2 d-none" id="donor-ward-<?= (int)$sr['id'] ?>">
                                                            <label class="form-label small fw-bold mb-1 text-primary">উৎস ওয়ার্ড (যেখান থেকে কর্মী আসবে):</label>
                                                            <select name="source_ward_id" class="form-select form-select-sm">
                                                                <option value="">-- উৎস ওয়ার্ড বেছে নিন --</option>
                                                                <?php foreach ($wards ?? [] as $w): ?>
                                                                    <option value="<?= (int)$w['id'] ?>">ওয়ার্ড <?= to_bn_number((string)$w['ward_number']) ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="mb-2">
                                                            <label class="form-label small fw-bold mb-1">বরাদ্দকৃত কর্মী সংখ্যা:</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" name="allocated_worker_count" min="1" max="50" value="<?= (int)($sr['requested_worker_count'] ?? 5) ?>" class="form-control text-center fw-bold" required>
                                                                <span class="input-group-text">জন</span>
                                                            </div>
                                                        </div>

                                                        <div class="mb-2">
                                                            <label class="form-label small fw-bold mb-1">প্রতিনিধির নাম ও মোবাইল:</label>
                                                            <input type="text" name="allocated_operator" class="form-control form-control-sm" placeholder="উদাঃ জামাল হোসেন (০১৭১১-XXXXXX)">
                                                        </div>

                                                        <div class="mb-2">
                                                            <label class="form-label small fw-bold mb-1">নির্দেশনা:</label>
                                                            <input type="text" name="response_notes" class="form-control form-control-sm" value="জরুরি ভিত্তিতে অতিরিক্ত পরিচ্ছন্নতাকর্মী সাইটে প্রেরণ করা হলো।">
                                                        </div>

                                                        <button type="submit" class="btn btn-sm btn-success fw-bold w-100 mt-2">
                                                            <i class="bi bi-people-fill me-1"></i>কর্মী প্রেরণ ও অনুমোদন
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form action="/dashboard/support-requests/<?= (int)$sr['id'] ?>/respond" method="POST">
                                                        <?= csrf_field() ?>
                                                        <div class="mb-2">
                                                            <label class="form-label small fw-bold mb-1">বরাদ্দকৃত সরঞ্জাম / যন্ত্রপাতি:</label>
                                                            <input type="text" name="allocated_resource" class="form-control form-control-sm" placeholder="উদাঃ মিনি-এক্সকাভেটর MCC-EX-02" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small fw-bold mb-1">চালক / অপারেটর ও ফোন:</label>
                                                            <input type="text" name="allocated_operator" class="form-control form-control-sm" placeholder="উদাঃ মোঃ রফিক (০১৭১১-২২৩৩৪৪)" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small fw-bold mb-1">পৌঁছানোর আনুমানিক সময়:</label>
                                                            <input type="text" name="scheduled_arrival" class="form-control form-control-sm" placeholder="উদাঃ আজ দুপুর ২:০০ টায়" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small fw-bold mb-1">নির্দেশনা / মন্তব্য:</label>
                                                            <input type="text" name="response_notes" class="form-control form-control-sm" placeholder="সাইট ব্যবহারের শর্তাবলী...">
                                                        </div>
                                                        <div class="d-flex gap-2 justify-content-end mt-3">
                                                            <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger">
                                                                প্রত্যাখ্যান
                                                            </button>
                                                            <button type="submit" name="status" value="approved" class="btn btn-sm btn-success fw-bold">
                                                                <i class="bi bi-send me-1"></i>অনুমোদন নিশ্চিত করুন
                                                            </button>
                                                        </div>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted d-block"><?= e($sr['response_notes'] ?? 'কার্যক্রম সম্পন্ন') ?></small>
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

    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-trash-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? e($departmentNameBn ?? 'বর্জ্য ব্যবস্থাপনা বিভাগ') . ' — অপারেশন ও তদারকি' : e($departmentNameEn ?? 'Waste Management') . ' — Departmental Operations' ?>
                    </h5>
                    <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'বিভাগীয় সেবাসমূহ, কাজের অগ্রগতি ও মাঠ কার্যক্রম' : 'Departmental service units, field work progress and SLA compliance' ?></small>
                </div>
                <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill"><?= ($locale ?? 'bn') === 'bn' ? 'বিভাগীয় পোর্টাল' : 'Department Portal' ?></span>
            </div>

            <!-- Complaint Filter Toolbar -->
            <div class="card border-0 bg-light rounded-3 p-3 mb-3 table-filter-toolbar" data-filter-target="#dept-complaints-table">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 filter-search" placeholder="ট্র্যাকিং নং, বিবরণ বা এলাকা খুঁজুন...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select filter-status">
                            <option value="">সকল অবস্থা (Status)</option>
                            <option value="submitted">নতুন জমা (Submitted)</option>
                            <option value="assigned">দায়িত্বপ্রাপ্ত (Assigned)</option>
                            <option value="in_progress">কাজ চলছে (In Progress)</option>
                            <option value="resolved">সমাধানকৃত (Resolved)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select filter-ward">
                            <option value="">সকল ওয়ার্ড</option>
                            <?php for ($w = 1; $w <= 33; $w++): ?>
                                <option value="<?= $w ?>">ওয়ার্ড <?= to_bn_number((string)$w) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-outline-secondary w-100 filter-reset" title="ফিল্টার রিসেট">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>রিসেট
                        </button>
                    </div>
                </div>
                <div class="mt-2 text-muted small filter-count"></div>
            </div>

            <!-- Complaints Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="dept-complaints-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নং' : 'Tracking #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং' : 'Ward #' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'সেবা / সমস্যা' : 'Service / Problem' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'বর্তমান অবস্থা' : 'Status' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'অ্যাকশন' : 'Action' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($complaints)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No complaints under this department.</td></tr>
                        <?php else: ?>
                            <?php foreach ($complaints as $c): ?>
                                <?php
                                    $searchStr = ($c['public_complaint_number'] ?? '') . ' ' . ($c['subcategory_name_bn'] ?? '') . ' ' . ($c['landmark'] ?? '') . ' ' . ($c['public_safe_address'] ?? '');
                                ?>
                                <tr data-search="<?= e($searchStr) ?>" data-status="<?= e($c['internal_status'] ?? '') ?>" data-ward="<?= e((string)($c['ward_number'] ?? '')) ?>">
                                    <td class="ps-3">
                                        <a href="/dashboard/complaints/<?= urlencode($c['public_complaint_number'] ?? '') ?>"
                                           class="font-monospace fw-bold text-primary text-decoration-none">
                                            <?= e($c['public_complaint_number'] ?? '') ?>
                                            <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)($c['ward_number'] ?? '')) : 'Ward ' . ($c['ward_number'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td><strong><?= ($locale ?? 'bn') === 'bn' ? e($c['subcategory_name_bn'] ?? '') : e($c['subcategory_name_en'] ?? '') ?></strong></td>
                                    <td><span class="badge bg-info-subtle text-info-emphasis px-2 py-1"><?= e(human_status($c['internal_status'], $locale)) ?></span></td>
                                    <td class="text-end pe-3">
                                        <a href="/dashboard/complaints/<?= urlencode($c['public_complaint_number'] ?? '') ?>" class="btn btn-sm btn-primary fw-semibold">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'বিস্তারিত →' : 'Full Detail →' ?>
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
