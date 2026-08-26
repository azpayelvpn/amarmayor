<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-journal-text text-primary me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'আমার অভিযোগসমূহ' : 'My Submitted Complaints' ?>
            </h3>
            <p class="text-muted small mb-0">
                <?= ($locale ?? 'bn') === 'bn' ? 'আপনার মোবাইল নম্বর দিয়ে দাখিলকৃত সকল অভিযোগের তালিকা।' : 'List of all complaints submitted with your account.' ?>
            </p>
        </div>
        <a href="/complaints/create" class="btn btn-civic-primary">
            <i class="bi bi-plus-circle me-1"></i>
            <?= ($locale ?? 'bn') === 'bn' ? 'নতুন অভিযোগ' : 'New Complaint' ?>
        </a>
    </div>

    <?php if (empty($complaints)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <div class="rounded-circle bg-light text-muted mx-auto d-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;font-size:2rem;">
                <i class="bi bi-inbox"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2"><?= ($locale ?? 'bn') === 'bn' ? 'কোনো অভিযোগ পাওয়া যায়নি' : 'No Complaints Found' ?></h5>
            <p class="text-muted small mb-4"><?= ($locale ?? 'bn') === 'bn' ? 'আপনার এলাকায় কোনো সমস্যা থাকলে নতুন অভিযোগ দাখিল করুন।' : 'If you have any local issue, submit a new complaint.' ?></p>
            <div>
                <a href="/complaints/create" class="btn btn-civic-primary px-4 py-2">
                    <?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ দাখিল করুন' : 'Submit Complaint' ?>
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php
            $statusMapBn = [
                'received' => 'অভিযোগ পেয়েছি',
                'assigned' => 'দায়িত্ব দেওয়া হয়েছে',
                'in_progress' => 'কাজ চলছে',
                'work_completed' => 'কাজ সম্পন্ন হয়েছে',
                'confirmation_needed' => 'আপনার নিশ্চিতকরণ প্রয়োজন',
                'resolved' => 'সমাধান হয়েছে',
                'needs_more_work' => 'আবার কাজ প্রয়োজন',
            ];
            $statusMapEn = [
                'received' => 'Received',
                'assigned' => 'Assigned',
                'in_progress' => 'In Progress',
                'work_completed' => 'Work Completed',
                'confirmation_needed' => 'Confirmation Needed',
                'resolved' => 'Resolved',
                'needs_more_work' => 'Needs More Work',
            ];
        ?>

        <div class="card border shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নম্বর' : 'Tracking #' ?></th>
                            <th class="py-3"><?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার ধরন' : 'Category / Issue' ?></th>
                            <th class="py-3"><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড' : 'Ward' ?></th>
                            <th class="py-3"><?= ($locale ?? 'bn') === 'bn' ? 'বর্তমান অবস্থা' : 'Status' ?></th>
                            <th class="py-3"><?= ($locale ?? 'bn') === 'bn' ? 'দাখিলের তারিখ' : 'Submitted Date' ?></th>
                            <th class="text-end pe-4 py-3"><?= ($locale ?? 'bn') === 'bn' ? 'পদক্ষেপ' : 'Action' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $c): ?>
                            <?php
                                $st = $c['citizen_status'] ?? 'received';
                                $badgeText = ($locale ?? 'bn') === 'bn' ? ($statusMapBn[$st] ?? $st) : ($statusMapEn[$st] ?? $st);
                            ?>
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-primary">
                                    <?= e($c['public_complaint_number']) ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <?= ($locale ?? 'bn') === 'bn' ? e($c['subcategory_name_bn']) : e($c['subcategory_name_en']) ?>
                                    </div>
                                    <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? e($c['category_name_bn']) : e($c['category_name_en']) ?></small>
                                </td>
                                <td>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ' . to_bn_number((string)$c['ward_number']) : 'Ward ' . $c['ward_number'] ?>
                                </td>
                                <td>
                                    <span class="badge-status status-<?= e($st) ?> small">
                                        <?= e($badgeText) ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <?= format_bn_date((string)$c['submitted_at']) ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="/track/<?= e($c['public_complaint_number']) ?>" class="btn btn-sm btn-outline-primary">
                                        <?= ($locale ?? 'bn') === 'bn' ? 'বিস্তারিত' : 'View' ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
