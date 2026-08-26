<div class="container">
    <!-- Hero Banner -->
    <div class="hero-civic mb-4">
        <div class="row align-items-center gy-3">
            <div class="col-lg-8">
                <span class="badge bg-white text-dark px-3 py-1 rounded-pill mb-2 fw-semibold">
                    <?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশন' : 'Mymensingh City Corporation' ?>
                </span>
                <h1 class="hero-title mb-2">
                    <?= ($locale ?? 'bn') === 'bn' ? 'আমার ময়মনসিংহ' : 'Amar Mayor' ?>
                </h1>
                <p class="hero-subtitle mb-0">
                    <?= ($locale ?? 'bn') === 'bn' ? 'আপনার এলাকার সমস্যা জানান, কাজের অগ্রগতি দেখুন।' : 'Report local municipal issues and monitor resolution progress in real time.' ?>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="/complaints/create" class="btn btn-light btn-lg text-success fw-bold px-4 py-3 shadow-sm rounded-pill d-inline-flex align-items-center gap-2">
                    <i class="bi bi-megaphone-fill text-danger"></i>
                    <span><?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ করুন' : 'Submit Complaint' ?></span>
                </a>
            </div>
        </div>
    </div>

    <!-- 5 Primary Citizen Action Cards -->
    <div class="row g-3 mb-5">
        <!-- 1. Submit Complaint -->
        <div class="col-6 col-md-4 col-lg">
            <a href="/complaints/create" class="action-card">
                <div class="icon-circle icon-green">
                    <i class="bi bi-plus-circle-fill"></i>
                </div>
                <div class="action-card-title"><?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ করুন' : 'Submit Complaint' ?></div>
                <p class="action-card-desc"><?= ($locale ?? 'bn') === 'bn' ? 'নতুন সমস্যা দাখিল' : 'Report new issue' ?></p>
            </a>
        </div>

        <!-- 2. My Complaints -->
        <div class="col-6 col-md-4 col-lg">
            <a href="/my-complaints" class="action-card">
                <div class="icon-circle icon-blue">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div class="action-card-title"><?= ($locale ?? 'bn') === 'bn' ? 'আমার অভিযোগ' : 'My Complaints' ?></div>
                <p class="action-card-desc"><?= ($locale ?? 'bn') === 'bn' ? 'পূর্বের আবেদনের তালিকা' : 'View submitted list' ?></p>
            </a>
        </div>

        <!-- 3. Track Status -->
        <div class="col-6 col-md-4 col-lg">
            <a href="/track" class="action-card">
                <div class="icon-circle icon-amber">
                    <i class="bi bi-search"></i>
                </div>
                <div class="action-card-title"><?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগের অবস্থা দেখুন' : 'Track Status' ?></div>
                <p class="action-card-desc"><?= ($locale ?? 'bn') === 'bn' ? 'ট্র্যাকিং নম্বর দিয়ে খুঁজুন' : 'Track by number' ?></p>
            </a>
        </div>

        <!-- 4. My Ward -->
        <div class="col-6 col-md-6 col-lg">
            <a href="/wards" class="action-card">
                <div class="icon-circle icon-purple">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div class="action-card-title"><?= ($locale ?? 'bn') === 'bn' ? 'আমার ওয়ার্ড' : 'My Ward' ?></div>
                <p class="action-card-desc"><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ও অঞ্চল তথ্য' : 'Ward details' ?></p>
            </a>
        </div>

        <!-- 5. Who is Responsible -->
        <div class="col-12 col-md-6 col-lg">
            <a href="/who-is-responsible" class="action-card">
                <div class="icon-circle icon-teal">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div class="action-card-title"><?= ($locale ?? 'bn') === 'bn' ? 'কে দায়িত্বে আছেন?' : 'Who is Responsible?' ?></div>
                <p class="action-card-desc"><?= ($locale ?? 'bn') === 'bn' ? 'দায়িত্বপ্রাপ্ত প্রতিনিধি' : 'Assigned officers' ?></p>
            </a>
        </div>
    </div>

    <!-- Public Accountability Snapshot -->
    <div class="card border shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="card-title fw-bold mb-1">
                        <i class="bi bi-graph-up text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'পৌর সেবা ও কার্যক্রমের পরিসংখ্যান' : 'Public Service & Accountability Snapshot' ?>
                    </h5>
                    <small class="text-muted">
                        <?= ($locale ?? 'bn') === 'bn' ? 'সিটি কর্পোরেশন এলাকার সামগ্রিক সেবার অবস্থা' : 'Real-time citywide operational overview' ?>
                    </small>
                </div>
                <a href="/who-is-responsible" class="btn btn-sm btn-outline-secondary">
                    <?= ($locale ?? 'bn') === 'bn' ? 'বিস্তারিত দেখুন' : 'View Details' ?> &rarr;
                </a>
            </div>

            <div class="row g-3">
                <!-- Received -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="metric-box">
                        <div class="metric-number text-primary"><?= to_bn_number((string)($metrics['total_received'] ?? 0)) ?></div>
                        <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'মোট অভিযোগ' : 'Total Received' ?></div>
                    </div>
                </div>

                <!-- In Progress -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="metric-box">
                        <div class="metric-number text-warning"><?= to_bn_number((string)($metrics['in_progress'] ?? 0)) ?></div>
                        <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'কাজ চলছে' : 'In Progress' ?></div>
                    </div>
                </div>

                <!-- Work Completed -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="metric-box">
                        <div class="metric-number text-info"><?= to_bn_number((string)($metrics['work_completed'] ?? 0)) ?></div>
                        <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'কাজ সম্পন্ন' : 'Work Completed' ?></div>
                    </div>
                </div>

                <!-- Citizen Confirmed Resolved -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="metric-box">
                        <div class="metric-number text-success"><?= to_bn_number((string)($metrics['citizen_confirmed_resolved'] ?? 0)) ?></div>
                        <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'সমাধান হয়েছে' : 'Resolved' ?></div>
                    </div>
                </div>

                <!-- Currently Overdue -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="metric-box">
                        <div class="metric-number text-danger"><?= to_bn_number((string)($metrics['currently_overdue'] ?? 0)) ?></div>
                        <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'সময় পেরিয়েছে' : 'Overdue' ?></div>
                    </div>
                </div>

                <!-- Needs More Work -->
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="metric-box">
                        <div class="metric-number text-secondary"><?= to_bn_number((string)($metrics['needs_more_work'] ?? 0)) ?></div>
                        <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'আবার কাজ প্রয়োজন' : 'Needs More Work' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Emergency Helpline Card -->
    <div class="card bg-light border-0 rounded-4 p-3 mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:1.4rem;">
                    <i class="bi bi-telephone-inbound-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'জরুরি পৌর সেবা হটলাইন' : 'Emergency Municipal Helpline' ?></h6>
                    <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? '২৪/৭ কন্ট্রোল রুম সহায়তা ও জরুরি সেবা' : '24/7 Control Room Support' ?></small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="tel:16106" class="btn btn-outline-danger fw-bold px-3 py-2">
                    <i class="bi bi-telephone me-1"></i> ১৬১০৬
                </a>
                <a href="tel:02996663123" class="btn btn-outline-secondary fw-semibold px-3 py-2">
                    ০২৯৯৬৬-৬৩১২৩
                </a>
            </div>
        </div>
    </div>
</div>
