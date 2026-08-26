<!-- Public Information Officer Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-megaphone-fill text-primary me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'জনসংযোগ কর্মকর্তা — পৌর বিজ্ঞপ্তি ও নাগরিক তথ্য প্রচার' : 'Public Information Officer — City Notices & Broadcast' ?>
                </h5>
                <a href="/notices" class="btn btn-sm btn-outline-primary"><?= ($locale ?? 'bn') === 'bn' ? 'পাবলিক নোটিশ বোর্ড দেখুন' : 'Public Notice Board' ?></a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'বিজ্ঞপ্তির শিরোনাম' : 'Notice Title' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'ক্যাটাগরি' : 'Category' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'প্রকাশের তারিখ' : 'Published Date' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'অবস্থা' : 'Status' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notices)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No notices published yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($notices as $n): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-dark"><?= ($locale ?? 'bn') === 'bn' ? e($n['title_bn']) : e($n['title_en']) ?></td>
                                    <td><span class="badge bg-secondary"><?= e($n['notice_type'] ?? 'general') ?></span></td>
                                    <td class="small text-muted"><?= e($n['published_at'] ?? 'Draft') ?></td>
                                    <td class="text-end pe-3"><span class="badge bg-success-subtle text-success"><?= e($n['status'] ?? 'published') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
