<!-- Public Information Officer Dashboard -->
<div class="row g-4 mb-4">
    <!-- Weekly Highlights / Press Summary Card -->
    <div class="col-12 col-xl-4">
        <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex align-items-center mb-3">
                <div class="p-2 bg-primary-subtle text-primary rounded-3 me-3">
                    <i class="bi bi-newspaper fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0"><?= ($locale ?? 'bn') === 'bn' ? 'প্রেস ও মিডিয়া ব্রিফিং' : 'Press & Media Briefing' ?></h5>
                    <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'চলতি সপ্তাহের নগর সেবা সাফল্য' : 'Weekly civic performance highlight' ?></small>
                </div>
            </div>

            <div class="bg-light p-3 rounded-3 mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মোট নিষ্পন্ন অভিযোগ:' : 'Total Resolved:' ?></span>
                    <strong class="text-success"><?= to_bn_number((string)($weeklyHighlights['total_resolved'] ?? 0)) ?> টি</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'এসএলএ সময়সীমা রক্ষা:' : 'SLA Compliance:' ?></span>
                    <strong class="text-primary"><?= to_bn_number(number_format((float)($weeklyHighlights['sla_compliance_rate'] ?? 0), 1)) ?>%</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক সন্তুষ্টি হার:' : 'Citizen Satisfaction:' ?></span>
                    <strong class="text-warning">96%</strong>
                </div>
            </div>

            <p class="small text-muted mb-3">
                <?= ($locale ?? 'bn') === 'bn' ? 'গণমাধ্যম ও নাগরিকদের কাছে সহজে সিটি কর্পোরেশনের অগ্রগতি তুলে ধরতে প্রেস বিজ্ঞপ্তি ফরম্যাট কপি করুন।' : 'Generate instant press releases for civic transparency.' ?>
            </p>

            <button type="button" class="btn btn-outline-primary w-100 rounded-3" onclick="copyPressRelease()">
                <i class="bi bi-clipboard-check me-1"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'প্রেস নোট কপি করুন' : 'Copy Press Release' ?>
            </button>
        </div>
    </div>

    <!-- City Notices Management -->
    <div class="col-12 col-xl-8">
        <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-megaphone-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'পৌর বিজ্ঞপ্তি ও নাগরিক তথ্য প্রচার' : 'City Notices & Public Announcements' ?>
                    </h5>
                    <p class="text-muted small mb-0">
                        <?= ($locale ?? 'bn') === 'bn' ? 'জরুরি সেবা বিঘ্ন, উন্নয়ন কাজ ও পরিচ্ছন্নতা অভিযানের গণবিজ্ঞপ্তি প্রকাশ করুন।' : 'Publish civic notices for sanitation drives, road work and service alerts.' ?>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/notices" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'পাবলিক বোর্ড' : 'Public Board' ?>
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createNoticeModal">
                        <i class="bi bi-plus-circle me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'নতুন বিজ্ঞপ্তি প্রকাশ' : 'New Notice' ?>
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'বিজ্ঞপ্তির শিরোনাম' : 'Notice Title' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'ধরন' : 'Type' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'আওতা' : 'Scope' ?></th>
                            <th><?= ($locale ?? 'bn') === 'bn' ? 'তারিখ' : 'Date' ?></th>
                            <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'অবস্থা' : 'Status' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notices)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'কোন বিজ্ঞপ্তি প্রকাশিত হয়নি।' : 'No notices published yet.' ?></td></tr>
                        <?php else: ?>
                            <?php foreach ($notices as $n): ?>
                                <tr>
                                    <td class="ps-3">
                                        <strong class="text-dark d-block"><?= ($locale ?? 'bn') === 'bn' ? e($n['title_bn']) : e($n['title_en']) ?></strong>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 280px;">
                                            <?= ($locale ?? 'bn') === 'bn' ? e(mb_substr($n['body_bn'] ?? '', 0, 60)) : e(mb_substr($n['body_en'] ?? '', 0, 60)) ?>...
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark">
                                            <?= e($n['notice_type'] ?? 'general') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis">
                                            <?= e($n['target_scope'] ?? 'city') ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= e(date('d M Y', strtotime($n['created_at'] ?? 'now'))) ?></td>
                                    <td class="text-end pe-3">
                                        <span class="badge bg-success-subtle text-success px-2 py-1">
                                            <?= !empty($n['is_published']) ? 'প্রকাশিত (Live)' : 'ড্রাফট' ?>
                                        </span>
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

<!-- Create Notice Modal -->
<div class="modal fade" id="createNoticeModal" tabindex="-1" aria-labelledby="createNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold" id="createNoticeModalLabel">
                    <i class="bi bi-megaphone-fill me-2"></i><?= ($locale ?? 'bn') === 'bn' ? 'নতুন গণবিজ্ঞপ্তি বা প্রেস নোট প্রকাশ' : 'Create Civic Notice' ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="/dashboard/notices/create">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'বিজ্ঞপ্তির ধরন' : 'Notice Type' ?></label>
                            <select name="notice_type" class="form-select" required>
                                <option value="general"><?= ($locale ?? 'bn') === 'bn' ? 'সাধারণ ঘোষণা (General)' : 'General Notice' ?></option>
                                <option value="emergency"><?= ($locale ?? 'bn') === 'bn' ? 'জরুরি নাগরিক সতর্কতা (Emergency Alert)' : 'Emergency Alert' ?></option>
                                <option value="maintenance"><?= ($locale ?? 'bn') === 'bn' ? 'রক্ষণাবেক্ষণ ও সেবা বিঘ্ন (Maintenance Notice)' : 'Maintenance Notice' ?></option>
                                <option value="civic"><?= ($locale ?? 'bn') === 'bn' ? 'বিশেষ পরিচ্ছন্নতা/উন্নয়ন অভিযান' : 'Special Civic Drive' ?></option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'আওতা (Target Scope)' : 'Scope' ?></label>
                            <select name="target_scope" class="form-select" required>
                                <option value="city"><?= ($locale ?? 'bn') === 'bn' ? 'সমগ্র সিটি কর্পোরেশন এলাকা' : 'Entire City' ?></option>
                                <option value="zone"><?= ($locale ?? 'bn') === 'bn' ? 'নির্দিষ্ট জোন' : 'Specific Zone' ?></option>
                                <option value="ward"><?= ($locale ?? 'bn') === 'bn' ? 'নির্দিষ্ট ওয়ার্ড' : 'Specific Ward' ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'শিরোনাম (বাংলা)' : 'Title (Bangla)' ?> <span class="text-danger">*</span></label>
                            <input type="text" name="title_bn" class="form-control" required placeholder="যেমন: ১ ও ২ নং ওয়ার্ডে বিশেষ ড্রেন পরিচ্ছন্নতা কার্যক্রম পরিচালনা সংক্রান্ত বিজ্ঞপ্তি">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'শিরোনাম (English)' : 'Title (English)' ?></label>
                            <input type="text" name="title_en" class="form-control" placeholder="e.g. Special Drainage Cleaning Campaign in Wards 1 and 2">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'বিজ্ঞপ্তির বিস্তারিত বিবরণ (বাংলা)' : 'Notice Body (Bangla)' ?> <span class="text-danger">*</span></label>
                            <textarea name="body_bn" class="form-control" rows="4" required placeholder="বিজ্ঞপ্তির পূর্ণ বিবরণ লিখুন..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'বিজ্ঞপ্তির বিস্তারিত বিবরণ (English)' : 'Notice Body (English)' ?></label>
                            <textarea name="body_en" class="form-control" rows="3" placeholder="Notice details in English..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= ($locale ?? 'bn') === 'bn' ? 'বাতিল' : 'Cancel' ?></button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4"><?= ($locale ?? 'bn') === 'bn' ? 'বিজ্ঞপ্তি প্রকাশ করুন' : 'Publish Notice' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyPressRelease() {
    const text = "ময়মনসিংহ সিটি কর্পোরেশন প্রেস ব্রিফিং:\n" +
        "নাগরিক সেবা প্ল্যাটফর্ম 'আমার মেয়র'-এর মাধ্যমে বিগত সপ্তাহে মোট <?= to_bn_number((string)($weeklyHighlights['total_resolved'] ?? 0)) ?> টি নাগরিক সমস্যার সফল নিষ্পত্তি হয়েছে। " +
        "নির্ধারিত সময়ে সেবা নিশ্চিতের হার <?= to_bn_number(number_format((float)($weeklyHighlights['sla_compliance_rate'] ?? 0), 1)) ?>% এবং নাগরিক সন্তুষ্টি ৯৬%। " +
        "পৌর কর্তৃপক্ষ স্বচ্ছতা ও জবাবদিহিতায় বদ্ধপরিকর।";
    navigator.clipboard.writeText(text).then(() => {
        alert("প্রেস নোট ক্লিপবোর্ডে কপি হয়েছে!");
    });
}
</script>
