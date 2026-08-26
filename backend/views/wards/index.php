<div class="container py-4">
    <!-- Header -->
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-geo-alt-fill text-primary me-2"></i>
            <?= ($locale ?? 'bn') === 'bn' ? 'আমার ওয়ার্ড ও অঞ্চল নির্দেশিকা' : 'Mymensingh City Wards & Zones' ?>
        </h3>
        <p class="text-muted small mb-0">
            <?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশনের ৩টি অঞ্চল ও ৩৩টি ওয়ার্ডের সার্বিক তথ্য।' : 'Directory of 3 Zones and 33 Wards across Mymensingh City Corporation.' ?>
        </p>
    </div>

    <!-- City Overview Stats -->
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card border shadow-sm rounded-4 p-3 text-center bg-white">
                <div class="fs-3 fw-bold text-primary"><?= to_bn_number((string)($profile['total_zones'] ?? 3)) ?></div>
                <div class="small text-muted fw-semibold"><?= ($locale ?? 'bn') === 'bn' ? 'প্রশাসনিক অঞ্চল' : 'Administrative Zones' ?></div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border shadow-sm rounded-4 p-3 text-center bg-white">
                <div class="fs-3 fw-bold text-success"><?= to_bn_number((string)($profile['total_wards'] ?? 33)) ?></div>
                <div class="small text-muted fw-semibold"><?= ($locale ?? 'bn') === 'bn' ? 'সাধারণ ওয়ার্ড' : 'General Wards' ?></div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border shadow-sm rounded-4 p-3 text-center bg-white">
                <div class="fs-3 fw-bold text-warning"><?= to_bn_number((string)($profile['total_reserved_seats'] ?? 11)) ?></div>
                <div class="small text-muted fw-semibold"><?= ($locale ?? 'bn') === 'bn' ? 'সংরক্ষিত নারী আসন' : 'Reserved Seats' ?></div>
            </div>
        </div>
    </div>

    <!-- Zones and Wards Grid -->
    <?php foreach ($zones as $zone): ?>
        <div class="card border shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-header bg-light border-0 py-3 px-4 rounded-top-4">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-diagram-3-fill text-primary me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? e($zone['name_bn']) : e($zone['name_en']) ?>
                    <small class="text-muted fs-6 ms-2">(অঞ্চল কার্যালয়: <?= e($zone['office_address'] ?? 'ময়মনসিংহ') ?>)</small>
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <?php foreach ($zone['wards'] as $w): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="p-3 border rounded-3 text-center h-100 bg-light">
                                <div class="badge bg-primary px-2 py-1 mb-2">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number((string)$w['ward_number']) : 'Ward ' . $w['ward_number'] ?>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">
                                    <?= ($locale ?? 'bn') === 'bn' ? e($w['name_bn']) : e($w['name_en']) ?>
                                </h6>
                                <div class="mt-2">
                                    <a href="/who-is-responsible?ward_id=<?= (int)$w['id'] ?>" class="btn btn-sm btn-outline-secondary w-100">
                                        <?= ($locale ?? 'bn') === 'bn' ? 'প্রতিনিধি দেখুন' : 'View Officers' ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
