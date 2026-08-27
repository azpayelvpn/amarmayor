<div class="container py-4">
    <!-- Header -->
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-geo-alt-fill text-primary me-2"></i>
            <?= ($locale ?? 'bn') === 'bn' ? 'আমার এলাকা ও ওয়ার্ড নির্দেশিকা' : 'My Area & City Wards Directory' ?>
        </h3>
        <p class="text-muted small mb-0">
            <?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশনের ৩টি অঞ্চল ও ৩৩টি ওয়ার্ডের সার্বিক তথ্য ও স্থানীয় সেবা দায়িত্ব।' : 'Directory of 3 Zones and 33 Wards across Mymensingh City Corporation with localized service responsibilities.' ?>
        </p>
    </div>

    <?php if (!empty($homeWard)): ?>
        <!-- Personalized "My Area / আমার এলাকা" Hero Card -->
        <div class="card border-primary border-2 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
            <div class="card-header bg-primary text-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-house-heart-fill fs-4"></i>
                    <div>
                        <span class="badge bg-white text-primary fw-bold px-2 py-1 mb-1">
                            <?= ($locale ?? 'bn') === 'bn' ? 'আমার নির্ধারিত ওয়ার্ড' : 'My Designated Ward' ?>
                        </span>
                        <h5 class="fw-bold mb-0">
                            <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number((string)$homeWard['ward_number']) . ' — ' . e($homeWard['name_bn']) : 'Ward ' . $homeWard['ward_number'] . ' — ' . e($homeWard['name_en']) ?>
                        </h5>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="/who-is-responsible?ward_id=<?= (int)$homeWard['id'] ?>" class="btn btn-sm btn-light text-primary fw-semibold rounded-pill px-3">
                        <i class="bi bi-person-badge me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'সম্পূর্ণ বিবরণ' : 'Full Details' ?>
                    </a>
                    <a href="/profile" class="btn btn-sm btn-outline-light rounded-pill">
                        <i class="bi bi-pencil-square me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড পরিবর্তন' : 'Change Ward' ?>
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- 1. Governance Representation -->
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="bi bi-shield-check text-primary me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'জনপ্রতিনিধিত্ব / শাসনভার' : 'Ward Representation' ?>
                                </h6>
                            </div>
                            <?php 
                                $rep = $homeRepresentation['general_representation'] ?? [];
                                $resRep = $homeRepresentation['reserved_seat_representation'] ?? [];
                            ?>
                            <?php if (!empty($rep['name_bn'])): ?>
                                <div class="fw-bold text-dark fs-6"><?= ($locale ?? 'bn') === 'bn' ? e($rep['name_bn']) : e($rep['name_en']) ?></div>
                                <div class="small text-primary fw-semibold mb-1">
                                    <?= ($rep['role_slug'] ?? '') === 'responsible_officer' ? (($locale ?? 'bn') === 'bn' ? 'দায়িত্বপ্রাপ্ত কর্মকর্তা' : 'Responsible Officer') : (($locale ?? 'bn') === 'bn' ? 'কাউন্সিলর' : 'Ward Councillor') ?>
                                </div>
                                <div class="small text-muted mb-2"><?= e($rep['designation'] ?? '') ?></div>
                                <?php if (!empty($rep['phone'])): ?>
                                    <div class="small mt-auto pt-2 border-top">
                                        <i class="bi bi-telephone-fill text-success me-1"></i>
                                        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $rep['phone'])) ?>" class="fw-bold text-primary text-decoration-none font-monospace">
                                            <?= e($rep['phone']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-muted small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'এই ওয়ার্ডের যাচাইকৃত প্রতিনিধিত্ব/দায়িত্বপ্রাপ্ত তথ্য এখনো যোগ হয়নি।' : 'Verified representation information for this Ward is not yet available.' ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($resRep['name_bn'])): ?>
                                <div class="mt-2 pt-2 border-top">
                                    <div class="fw-bold text-dark small"><?= ($locale ?? 'bn') === 'bn' ? e($resRep['name_bn']) : e($resRep['name_en']) ?></div>
                                    <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'সংরক্ষিত কাউন্সিলর' : 'Reserved Councillor' ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 2. Operational & Services -->
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border d-flex flex-column">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="bi bi-building-gear text-teal me-1"></i>
                                <?= ($locale ?? 'bn') === 'bn' ? 'প্রশাসনিক ও সেবা কর্মকর্তা' : 'MCC Operational Officer' ?>
                            </h6>
                            <?php $op = $homeRepresentation['operational_responsibility'] ?? []; ?>
                            <?php if (!empty($op['name_bn'])): ?>
                                <div class="fw-bold text-dark fs-6">
                                    <?= ($locale ?? 'bn') === 'bn' ? e($op['name_bn']) : e($op['name_en']) ?>
                                    <?php if (!empty($op['personnel_id'])): ?>
                                        <span class="text-muted small ms-1">(পরিচিতি: <?= to_bn_number((string)$op['personnel_id']) ?>)</span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted mb-2"><?= e($op['designation_bn'] ?? '') ?></div>
                                <div class="small mb-1">
                                    <span class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'প্রশাসনিক অঞ্চল:' : 'Zone:' ?></span>
                                    <span class="fw-bold text-dark ms-1"><?= ($locale ?? 'bn') === 'bn' ? e($homeWard['zone_name_bn']) : e($homeWard['zone_name_en']) ?></span>
                                </div>
                                <?php if (!empty($op['phone'])): ?>
                                    <div class="small mt-auto pt-2 border-top">
                                        <i class="bi bi-telephone-fill text-success me-1"></i>
                                        <span class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'অফিসিয়াল যোগাযোগ:' : 'Official Contact:' ?></span>
                                        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $op['phone'])) ?>" class="fw-bold text-primary text-decoration-none font-monospace ms-1">
                                            <?= e($op['phone']) ?>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="small text-muted mt-auto pt-2 border-top">
                                        <?= ($locale ?? 'bn') === 'bn' ? 'যাচাইকৃত যোগাযোগ তথ্য এখনো যোগ হয়নি।' : 'Verified contact not yet available.' ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="small mb-1">
                                    <span class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'প্রশাসনিক অঞ্চল:' : 'Zone:' ?></span>
                                    <span class="fw-bold text-dark ms-1"><?= ($locale ?? 'bn') === 'bn' ? e($homeWard['zone_name_bn']) : e($homeWard['zone_name_en']) ?></span>
                                </div>
                                <div class="small text-muted mt-2">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'যাচাইকৃত যোগাযোগ তথ্য এখনো যোগ হয়নি।' : 'Verified contact not yet available.' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 3. Local Activity Snapshot -->
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border d-flex flex-column">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="bi bi-activity text-success me-1"></i>
                                <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ডের নাগরিক কার্যক্রম' : 'Ward Civic Activity' ?>
                            </h6>
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মোট নাগরিক অভিযোগ:' : 'Total Complaints:' ?></span>
                                <span class="fw-bold"><?= to_bn_number((string)($homeSnapshot['total_in_ward'] ?? 0)) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'কাজ চলমান:' : 'Active in progress:' ?></span>
                                <span class="fw-bold text-warning-emphasis"><?= to_bn_number((string)($homeSnapshot['active_in_ward'] ?? 0)) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'সমাধানকৃত:' : 'Resolved:' ?></span>
                                <span class="fw-bold text-success"><?= to_bn_number((string)($homeSnapshot['resolved_in_ward'] ?? 0)) ?></span>
                            </div>
                            <div class="mt-auto pt-3">
                                <a href="/submit?ward_id=<?= (int)$homeWard['id'] ?>" class="btn btn-sm btn-success w-100">
                                    <i class="bi bi-megaphone me-1"></i>
                                    <?= ($locale ?? 'bn') === 'bn' ? 'আমার ওয়ার্ডে সমস্যা জানান' : 'Submit Issue in My Ward' ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif (!empty($user)): ?>
        <!-- Prompt to set home ward -->
        <div class="alert alert-info border-info-subtle shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'আপনার বসবাসের এলাকা বা ওয়ার্ড নির্বাচন করলে এখানে আপনার ওয়ার্ডের তাৎক্ষণিক সেবা, দায়িত্বপ্রাপ্ত কর্মকর্তা ও কার্যক্রম দেখতে পাবেন।' : 'Set your Home Ward in your profile to view localized service responsibilities, contacts, and area activity.' ?>
            </div>
            <a href="/profile" class="btn btn-sm btn-primary rounded-pill px-3">
                <i class="bi bi-geo-alt me-1"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নির্বাচন করুন' : 'Select Ward' ?>
            </a>
        </div>
    <?php endif; ?>

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
                            <div class="p-3 border rounded-3 text-center h-100 bg-light d-flex flex-column">
                                <div class="badge bg-primary px-2 py-1 mb-2">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নং ' . to_bn_number((string)$w['ward_number']) : 'Ward ' . $w['ward_number'] ?>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">
                                    <?= ($locale ?? 'bn') === 'bn' ? e($w['name_bn']) : e($w['name_en']) ?>
                                </h6>
                                <div class="mt-auto pt-2">
                                    <a href="/who-is-responsible?ward_id=<?= (int)$w['id'] ?>" class="btn btn-sm btn-outline-secondary w-100">
                                        <?= ($locale ?? 'bn') === 'bn' ? 'দায়িত্বপ্রাপ্ত কর্মকর্তা' : 'View Officers' ?>
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
