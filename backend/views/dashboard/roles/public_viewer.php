<!-- Public Viewer Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-eye-fill text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'পাবলিক অবজারভার পোর্টাল — নাগরিক জবাবদিহিতা ও উন্মুক্ত সিটি পালস' : 'Public Viewer Portal — Civic Accountability & City Pulse' ?>
                    </h5>
                    <p class="text-muted small mb-0">
                        <?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশনের উন্মুক্ত নাগরিক সেবা পরিসংখ্যান, দায়িত্বশীল ব্যক্তিবর্গ ও ওয়ার্ড তথ্যাবলি।' : 'Open civic statistics, responsible municipal officials, and ward directories of Mymensingh City Corporation.' ?>
                    </p>
                </div>
                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-shield-check me-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'উন্মুক্ত নাগরিক তথ্য ও স্বচ্ছতা' : 'Open Civic Transparency' ?>
                </span>
            </div>

            <!-- Live City Pulse Metrics Grid -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 p-3 bg-light text-center h-100">
                        <div class="p-2 bg-primary-subtle text-primary rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-inbox-fill fs-5"></i>
                        </div>
                        <small class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'আজ দাখিলকৃত অভিযোগ' : 'Complaints Today' ?></small>
                        <strong class="fs-4 text-dark"><?= to_bn_number((string)($pulse['today_submitted'] ?? 0)) ?></strong>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 p-3 bg-light text-center h-100">
                        <div class="p-2 bg-success-subtle text-success rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                        </div>
                        <small class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'আজ সফল সমাধান' : 'Resolved Today' ?></small>
                        <strong class="fs-4 text-success"><?= to_bn_number((string)($pulse['today_resolved'] ?? 0)) ?></strong>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 p-3 bg-light text-center h-100">
                        <div class="p-2 bg-info-subtle text-info rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-speedometer2 fs-5"></i>
                        </div>
                        <small class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'এসএলএ সময়সীমা রক্ষা' : 'SLA Compliance' ?></small>
                        <strong class="fs-4 text-primary"><?= to_bn_number(number_format((float)($kpis['sla_compliance_rate'] ?? 95), 1)) ?>%</strong>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 p-3 bg-light text-center h-100">
                        <div class="p-2 bg-warning-subtle text-warning rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-emoji-smile-fill fs-5"></i>
                        </div>
                        <small class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক সন্তুষ্টি হার' : 'Satisfaction Rate' ?></small>
                        <strong class="fs-4 text-warning"><?= to_bn_number((string)($pulse['satisfaction_rate'] ?? 96)) ?>%</strong>
                    </div>
                </div>
            </div>

            <!-- Transparency Charter & Directory Access Cards -->
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border rounded-3 p-4 h-100 bg-white shadow-none hover-shadow transition">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 bg-primary text-white rounded-3 me-3">
                                <i class="bi bi-person-lines-fill fs-5"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0"><?= ($locale ?? 'bn') === 'bn' ? 'কে কোন দায়িত্বে আছেন?' : 'Who is Responsible?' ?></h6>
                        </div>
                        <p class="text-muted small mb-3">
                            <?= ($locale ?? 'bn') === 'bn' ? '৩৩টি ওয়ার্ডের দায়িত্বপ্রাপ্ত সাধারণ কাউন্সিলর, নারী কাউন্সিলর ও দায়িত্বপ্রাপ্ত নির্বাহী কর্মকর্তাদের তালিকা ও মোবাইল নম্বর।' : 'Directory of Ward Councillors, Reserved Women Councillors, and Responsible Officers.' ?>
                        </p>
                        <a href="/who-is-responsible" class="btn btn-sm btn-outline-primary mt-auto">
                            <?= ($locale ?? 'bn') === 'bn' ? 'ডিরেক্টরি দেখুন' : 'View Directory' ?> <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border rounded-3 p-4 h-100 bg-white shadow-none hover-shadow transition">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 bg-success text-white rounded-3 me-3">
                                <i class="bi bi-geo-alt-fill fs-5"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0"><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ও সেবার মানচিত্র' : 'Wards & Cleanliness Maps' ?></h6>
                        </div>
                        <p class="text-muted small mb-3">
                            <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ডভিত্তিক সীমানা, এসটিএস ও ডাস্টবিন লোকেশন এবং পরিচ্ছন্নতাকর্মীদের দৈনিক রোড-রুট শিডিউল।' : 'Ward boundaries, STS waste drop-off locations and daily sanitation route schedules.' ?>
                        </p>
                        <a href="/wards" class="btn btn-sm btn-outline-success mt-auto">
                            <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড মানচিত্র দেখুন' : 'Explore Wards' ?> <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border rounded-3 p-4 h-100 bg-white shadow-none hover-shadow transition">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 bg-secondary text-white rounded-3 me-3">
                                <i class="bi bi-megaphone fs-5"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0"><?= ($locale ?? 'bn') === 'bn' ? 'পৌর গণবিজ্ঞপ্তি বোর্ড' : 'Public City Notices' ?></h6>
                        </div>
                        <p class="text-muted small mb-3">
                            <?= ($locale ?? 'bn') === 'bn' ? 'পৌরসভার সর্বশেষ নাগরিক বিজ্ঞপ্তি, ড্রেন পরিচ্ছন্নতা সতর্কতা ও সেবা বিঘ্নের নোটিশবোর্ড।' : 'Official announcements, emergency service advisories and special civic drives.' ?>
                        </p>
                        <a href="/notices" class="btn btn-sm btn-outline-secondary mt-auto">
                            <?= ($locale ?? 'bn') === 'bn' ? 'নোটিশবোর্ড দেখুন' : 'Read Notices' ?> <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
