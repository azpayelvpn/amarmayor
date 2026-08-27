<div class="container py-4">
    <!-- Header -->
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-person-badge-fill text-primary me-2"></i>
            <?= ($locale ?? 'bn') === 'bn' ? 'কে দায়িত্বে আছেন? — নাগরিক জবাবদিহিতা ও দায়িত্বপ্রাপ্ত কর্মকর্তা নির্দেশিকা' : 'Who is Responsible? — Civic Accountability & Ward Responsibility Directory' ?>
        </h3>
        <p class="text-muted small mb-0">
            <?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশনের ৩৩টি সাধারণ ওয়ার্ডের সরকারি আদেশে নিয়োজিত দায়িত্বপ্রাপ্ত কর্মকর্তা ও মসিক সেবা কর্মকর্তাদের অফিসিয়াল তালিকা।' : 'Official directory of Appointed Responsible Officers and MCC Operational Ward Officers across all 33 Wards.' ?>
        </p>
    </div>

    <!-- Ward Filter / Search & Source Provenance Banner -->
    <div class="card border shadow-sm rounded-4 p-3 bg-white mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <label for="wardSearch" class="form-label small fw-semibold text-muted mb-1">
                    <i class="bi bi-search me-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নম্বর বা নাম দিয়ে খুঁজুন' : 'Search by Ward Number or Name' ?>
                </label>
                <input type="text" id="wardSearch" class="form-control rounded-3"
                       placeholder="<?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নম্বর লিখুন (উদাঃ ১, ১৯, ৩৩)...' : 'Enter ward number (e.g. 1, 19, 33)...' ?>"
                       onkeyup="filterWards()">
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-inline-flex flex-wrap gap-2 justify-content-md-end">
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                        <i class="bi bi-patch-check-fill text-success me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'অফিসিয়াল নথি তারিখ: ১৮.০৩.২০২৬' : 'Official Source Date: 18.03.2026' ?>
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                        <?= ($locale ?? 'bn') === 'bn' ? 'মোট ৩৩টি ওয়ার্ড সম্পূর্ণ আচ্ছাদিত' : 'Total 33 Wards Covered' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Wards Accountability List -->
    <div class="row g-4" id="wardDirectoryList">
        <?php foreach ($directory as $item): ?>
            <?php
                $wardNo = (string)($item['ward_number'] ?? '');
                $wardNameBn = $item['ward_name_bn'] ?? '';
                $wardNameEn = $item['ward_name_en'] ?? '';
                $zoneName = ($locale ?? 'bn') === 'bn' ? ($item['zone_name_bn'] ?? '') : ($item['zone_name_en'] ?? '');
                $rep = $item['general_representation'] ?? [];
                $op = $item['operational_responsibility'] ?? [];
                $sub = $op['substitute'] ?? null;
                $resRep = $item['reserved_seat_representation'] ?? [];
            ?>
            <div class="col-12 col-md-6 col-xl-4 ward-card-col" data-ward-no="<?= e($wardNo) ?>">
                <div class="card border shadow-sm rounded-4 h-100 bg-white d-flex flex-column overflow-hidden">
                    <!-- Ward Header -->
                    <div class="card-header bg-light border-bottom py-3 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary fs-6 px-3 py-1 rounded-pill">
                                <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number($wardNo) : 'Ward ' . $wardNo ?>
                            </span>
                            <h6 class="fw-bold text-dark mb-0">
                                <?= ($locale ?? 'bn') === 'bn' ? e($wardNameBn) : e($wardNameEn) ?>
                            </h6>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 small">
                            <?= e($zoneName) ?>
                        </span>
                    </div>

                    <div class="card-body p-3 d-flex flex-column gap-3">
                        <!-- 1. REPRESENTATION / GOVERNANCE RESPONSIBILITY -->
                        <div class="p-3 rounded-3 border bg-light-subtle">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-primary">
                                    <i class="bi bi-shield-check me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? '১. জনপ্রতিনিধিত্ব / দায়িত্বপ্রাপ্ত কর্মকর্তা' : '1. Governance / Responsible Officer' ?>
                                </span>
                                <?php if (!empty($rep['authority_basis']) && $rep['authority_basis'] === 'appointed'): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small">
                                        <?= ($locale ?? 'bn') === 'bn' ? 'দায়িত্বপ্রাপ্ত কর্মকর্তা' : 'Responsible Officer' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border small">
                                        <?= ($locale ?? 'bn') === 'bn' ? 'কাউন্সিলর' : 'Councillor' ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($rep['name_bn'])): ?>
                                <div class="fw-bold text-dark fs-6 mb-1">
                                    <?= ($locale ?? 'bn') === 'bn' ? e($rep['name_bn']) : e($rep['name_en']) ?>
                                </div>
                                <div class="small text-muted mb-2">
                                    <?= e($rep['designation'] ?? '') ?>
                                </div>

                                <?php if (!empty($rep['phone'])): ?>
                                    <div class="small d-flex align-items-center gap-1 text-dark">
                                        <i class="bi bi-telephone-fill text-success"></i>
                                        <span class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'যোগাযোগ:' : 'Contact:' ?></span>
                                        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $rep['phone'])) ?>" class="fw-bold text-primary text-decoration-none font-monospace">
                                            <?= e($rep['phone']) ?>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="small text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <?= ($locale ?? 'bn') === 'bn' ? 'যাচাইকৃত যোগাযোগ তথ্য এখনো যোগ হয়নি।' : 'Verified contact not yet available.' ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($rep['status_note'])): ?>
                                    <div class="mt-2 p-1 px-2 bg-danger-subtle text-danger border border-danger-subtle rounded small">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i>
                                        <?= e($rep['status_note']) ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-muted small p-2 bg-white rounded border border-light-subtle">
                                    <i class="bi bi-info-circle text-secondary me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'এই ওয়ার্ডের দায়িত্বপ্রাপ্ত জনপ্রতিনিধির তথ্য যাচাইকরণ প্রক্রিয়াধীন।' : 'Governance responsibility pending verification.' ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- 2. MCC OPERATIONAL / SERVICE RESPONSIBILITY -->
                        <div class="p-3 rounded-3 border bg-light-subtle">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-teal">
                                    <i class="bi bi-building-gear me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? '২. মসিক সেবা ও প্রশাসনিক কর্মকর্তা' : '2. MCC Operational Ward Officer' ?>
                                </span>
                                <span class="badge bg-info-subtle text-info-emphasis border small">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'প্রশাসনিক দায়িত্ব' : 'Operational' ?>
                                </span>
                            </div>

                            <?php if (!empty($op['name_bn'])): ?>
                                <div class="fw-bold text-dark fs-6 mb-1">
                                    <?= ($locale ?? 'bn') === 'bn' ? e($op['name_bn']) : e($op['name_en']) ?>
                                    <?php if (!empty($op['personnel_id'])): ?>
                                        <span class="badge bg-secondary-subtle text-dark border small ms-1">
                                            <?= ($locale ?? 'bn') === 'bn' ? 'পরিচিতি: ' . to_bn_number($op['personnel_id']) : 'ID: ' . $op['personnel_id'] ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted mb-2">
                                    <?= e($op['designation_bn'] ?? '') ?>
                                </div>

                                <?php if (!empty($op['phone'])): ?>
                                    <div class="small d-flex align-items-center gap-1 text-dark mb-2">
                                        <i class="bi bi-telephone-fill text-success"></i>
                                        <span class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মোবাইল নম্বর:' : 'Mobile:' ?></span>
                                        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $op['phone'])) ?>" class="fw-bold text-primary text-decoration-none font-monospace">
                                            <?= e($op['phone']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <!-- Leave Substitute -->
                                <?php if (!empty($sub['name_bn'])): ?>
                                    <div class="mt-2 pt-2 border-top">
                                        <span class="text-muted small fw-semibold d-block mb-1">
                                            <i class="bi bi-arrow-repeat text-secondary me-1"></i>
                                            <?= ($locale ?? 'bn') === 'bn' ? 'ছুটিকালীন প্রতিস্থাপক কর্মকর্তা:' : 'Leave-time Substitute Officer:' ?>
                                        </span>
                                        <div class="small fw-semibold text-dark">
                                            <?= ($locale ?? 'bn') === 'bn' ? e($sub['name_bn']) : e($sub['name_en']) ?>
                                            <?php if (!empty($sub['personnel_id'])): ?>
                                                <span class="text-muted small ms-1">(পরিচিতি: <?= to_bn_number((string)$sub['personnel_id']) ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small text-muted mb-1"><?= e($sub['designation_bn'] ?? '') ?></div>
                                        <?php if (!empty($sub['phone'])): ?>
                                            <div class="small font-monospace text-primary">
                                                <i class="bi bi-telephone me-1 text-secondary"></i>
                                                <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $sub['phone'])) ?>" class="text-decoration-none text-primary">
                                                    <?= e($sub['phone']) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-muted small p-2 bg-white rounded border border-light-subtle">
                                    <i class="bi bi-info-circle text-secondary me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'এই ওয়ার্ডের কর্মকর্তা পদায়ন তথ্য যাচাই প্রক্রিয়াধীন।' : 'Operational posting pending verification.' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="card-footer bg-white border-top p-3 mt-auto">
                        <a href="/submit?ward_id=<?= (int)($item['ward_id'] ?? 0) ?>" class="btn btn-sm btn-outline-success w-100 rounded-3">
                            <i class="bi bi-megaphone me-1"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'এই ওয়ার্ডের সমস্যা জানান' : 'Submit Issue in this Ward' ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Empty Search State -->
        <div id="noWardsFound" class="col-12 text-center py-5 d-none">
            <div class="p-5 bg-white rounded-4 border shadow-sm max-w-md mx-auto">
                <i class="bi bi-search text-muted fs-1 mb-3 d-block"></i>
                <h5 class="fw-bold text-dark mb-1">
                    <?= ($locale ?? 'bn') === 'bn' ? 'কোনো ওয়ার্ড পাওয়া যায়নি' : 'No Ward Found' ?>
                </h5>
                <p class="text-muted small mb-0">
                    <?= ($locale ?? 'bn') === 'bn' ? 'অনুগ্রহ করে সঠিক ওয়ার্ড নম্বর লিখুন (১ থেকে ৩৩)।' : 'Please enter a valid Ward number (1 to 33).' ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function filterWards() {
    const q = document.getElementById('wardSearch').value.trim().toLowerCase();
    const cols = document.querySelectorAll('.ward-card-col');
    const emptyState = document.getElementById('noWardsFound');
    let visibleCount = 0;

    cols.forEach(col => {
        const wardNo = col.getAttribute('data-ward-no');
        const text = col.textContent.toLowerCase();
        if (!q || wardNo.includes(q) || text.includes(q)) {
            col.style.display = '';
            visibleCount++;
        } else {
            col.style.display = 'none';
        }
    });

    if (emptyState) {
        if (visibleCount === 0) {
            emptyState.classList.remove('d-none');
        } else {
            emptyState.classList.add('d-none');
        }
    }
}
</script>
