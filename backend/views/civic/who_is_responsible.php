<div class="container py-4">
    <!-- Header -->
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-person-badge-fill text-teal me-2"></i>
            <?= ($locale ?? 'bn') === 'bn' ? 'কে দায়িত্বে আছেন? — নাগরিক জবাবদিহিতা নির্দেশিকা' : 'Who is Responsible? — Civic Accountability Directory' ?>
        </h3>
        <p class="text-muted small mb-0">
            <?= ($locale ?? 'bn') === 'bn' ? 'আপনার ওয়ার্ডের দায়িত্বপ্রাপ্ত জনপ্রতিনিধি ও দায়িত্বপ্রাপ্ত কর্মকর্তাদের তালিকা।' : 'Official representation and assigned administrative officers across all 33 MCC Wards.' ?>
        </p>
    </div>

    <!-- Ward Filter / Search -->
    <div class="card border shadow-sm rounded-4 p-3 bg-white mb-4">
        <div class="row align-items-center g-2">
            <div class="col-md-6">
                <label for="wardSearch" class="form-label small fw-semibold text-muted mb-1">
                    <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড অনুযায়ী খুঁজুন' : 'Search by Ward Number' ?>
                </label>
                <input type="text" id="wardSearch" class="form-control"
                       placeholder="<?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নম্বর লিখুন (উদাঃ ১, ২, ৩৩)...' : 'Enter ward number (e.g. 1, 2, 33)...' ?>"
                       onkeyup="filterWards()">
            </div>
            <div class="col-md-6 text-md-end pt-2 pt-md-4">
                <span class="badge bg-light text-muted border px-3 py-2">
                    <?= ($locale ?? 'bn') === 'bn' ? 'মোট ৩৩টি সাধারণ ওয়ার্ড' : 'Total 33 General Wards' ?>
                </span>
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
                $resRep = $item['reserved_seat_representation'] ?? [];
            ?>
            <div class="col-12 col-md-6 col-lg-4 ward-card-col" data-ward-no="<?= e($wardNo) ?>">
                <div class="card border shadow-sm rounded-4 h-100 bg-white p-3">
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                        <div>
                            <span class="badge bg-primary px-2 py-1">
                                <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number($wardNo) : 'Ward ' . $wardNo ?>
                            </span>
                            <h5 class="fw-bold text-dark mt-1 mb-0">
                                <?= ($locale ?? 'bn') === 'bn' ? e($wardNameBn) : e($wardNameEn) ?>
                            </h5>
                        </div>
                        <small class="text-muted"><?= e($zoneName) ?></small>
                    </div>

                    <!-- General Representative / Officer -->
                    <div class="mb-3">
                        <span class="text-muted small fw-semibold d-block">
                            <?= ($locale ?? 'bn') === 'bn' ? 'সাধারণ ওয়ার্ড দায়িত্বপ্রাপ্ত:' : 'General Ward Representation:' ?>
                        </span>
                        <?php if (!empty($rep['name_bn'])): ?>
                            <div class="fw-bold text-dark fs-6 mt-1">
                                <?= ($locale ?? 'bn') === 'bn' ? e($rep['name_bn']) : e($rep['name_en']) ?>
                            </div>
                            <div class="small text-primary fw-semibold">
                                <?php
                                    $roleTitle = $rep['role_title'] ?? 'কাউন্সিলর';
                                    if (($rep['type'] ?? '') === 'responsible_officer') {
                                        echo ($locale ?? 'bn') === 'bn' ? 'দায়িত্বপ্রাপ্ত কর্মকর্তা' : 'Responsible Officer';
                                    } else {
                                        echo ($locale ?? 'bn') === 'bn' ? 'কাউন্সিলর' : 'Ward Councillor';
                                    }
                                ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">
                                <?= ($locale ?? 'bn') === 'bn' ? 'এই ওয়ার্ডের বর্তমান যাচাইকৃত দায়িত্বশীল ব্যক্তির তথ্য এখনো যোগ করা হয়নি।' : 'Information for the verified responsible officer/representative for this ward has not been added yet.' ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Reserved Women Councillor -->
                    <div class="mb-3 pt-2 border-top">
                        <span class="text-muted small fw-semibold d-block">
                            <?= ($locale ?? 'bn') === 'bn' ? 'সংরক্ষিত নারী কাউন্সিলর:' : 'Reserved Women Councillor:' ?>
                        </span>
                        <?php if (!empty($resRep['name_bn'])): ?>
                            <div class="fw-bold text-dark fs-6 mt-1">
                                <?= ($locale ?? 'bn') === 'bn' ? e($resRep['name_bn']) : e($resRep['name_en']) ?>
                            </div>
                            <div class="small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'সংরক্ষিত কাউন্সিলর (' . e($resRep['seat_name_bn'] ?? '') . ')' : 'Reserved Councillor (' . e($resRep['seat_name_en'] ?? '') . ')' ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">
                                <?= ($locale ?? 'bn') === 'bn' ? 'এই ওয়ার্ডের বর্তমান যাচাইকৃত দায়িত্বশীল ব্যক্তির তথ্য এখনো যোগ করা হয়নি।' : 'Information for the verified responsible officer/representative for this ward has not been added yet.' ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Action -->
                    <div class="mt-auto pt-2">
                        <a href="/complaints/create?ward_id=<?= (int)($item['ward_id'] ?? 0) ?>" class="btn btn-sm btn-outline-success w-100">
                            <i class="bi bi-megaphone me-1"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'এই ওয়ার্ডে অভিযোগ করুন' : 'Submit Complaint in this Ward' ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function filterWards() {
    const q = document.getElementById('wardSearch').value.trim().toLowerCase();
    const cols = document.querySelectorAll('.ward-card-col');

    cols.forEach(col => {
        const wardNo = col.getAttribute('data-ward-no');
        const text = col.textContent.toLowerCase();
        if (!q || wardNo.includes(q) || text.includes(q)) {
            col.style.display = '';
        } else {
            col.style.display = 'none';
        }
    });
}
</script>
