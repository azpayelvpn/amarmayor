<!-- Platform Super Admin Non-Technical Governance Dashboard -->
<div class="row g-4 mb-4">
    <!-- Non-Technical Administration Modules -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-1">
                <i class="bi bi-sliders text-primary me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'প্ল্যাটফর্ম সুপার অ্যাডমিন — প্রতিষ্ঠান ও পরিচালনা ব্যবস্থাপনা' : 'Platform Super Admin — Institutional Governance & Operations' ?>
            </h5>
            <p class="text-muted small mb-4">
                <?= ($locale ?? 'bn') === 'bn' ? 'সিটি কর্পোরেশনের জনবল, ওয়ার্ড/অঞ্চল, সেবাসমূহ ও স্বয়ংক্রিয় দায়িত্ব বণ্টন ব্যবস্থাপনা' : 'Manage city workforce, areas, civic services, responsibilities, notices and routing rules' ?>
            </p>

            <div class="row g-3">
                <!-- 1. People -->
                <div class="col-md-4 col-lg-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light text-center">
                        <div class="rounded-circle bg-primary text-white mx-auto d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;font-size:1.4rem;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'কর্মকর্তা ও জনবল' : 'People & Staff' ?></h6>
                        <small class="text-muted d-block mb-3"><?= ($locale ?? 'bn') === 'bn' ? 'মোট সক্রিয় কর্মী: ' . to_bn_number((string)($platform['total_active_staff'] ?? 0)) : 'Active Staff: ' . ($platform['total_active_staff'] ?? 0) ?></small>
                        <button class="btn btn-sm btn-outline-primary mt-auto" onclick="alert('জনবল তালিকা ও পদবি ব্যবস্থাপনা মডিউল প্রস্তুত।')"><?= ($locale ?? 'bn') === 'bn' ? 'জনবল পরিচালনা' : 'Manage People' ?></button>
                    </div>
                </div>

                <!-- 2. Areas -->
                <div class="col-md-4 col-lg-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light text-center">
                        <div class="rounded-circle bg-success text-white mx-auto d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;font-size:1.4rem;">
                            <i class="bi bi-map-fill"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'অঞ্চল ও ওয়ার্ড এলাকা' : 'Areas & Wards' ?></h6>
                        <small class="text-muted d-block mb-3"><?= ($locale ?? 'bn') === 'bn' ? '৩৩টি ওয়ার্ড | ৩টি অঞ্চল' : '33 Wards | 3 Zones' ?></small>
                        <a href="/wards" class="btn btn-sm btn-outline-success mt-auto"><?= ($locale ?? 'bn') === 'bn' ? 'এলাকা ব্রাউজ করুন' : 'View Areas' ?></a>
                    </div>
                </div>

                <!-- 3. Governance -->
                <div class="col-md-4 col-lg-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light text-center">
                        <div class="rounded-circle bg-warning text-dark mx-auto d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;font-size:1.4rem;">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'প্রতিনিধিত্ব ও দায়িত্ব' : 'Governance & Tenures' ?></h6>
                        <small class="text-muted d-block mb-3"><?= ($locale ?? 'bn') === 'bn' ? 'কাউন্সিলর ও দায়িত্বপ্রাপ্ত কর্মকর্তা' : 'Councillors & Officers' ?></small>
                        <a href="/who-is-responsible" class="btn btn-sm btn-outline-warning text-dark mt-auto"><?= ($locale ?? 'bn') === 'bn' ? 'দায়িত্বশীল তালিকা' : 'Governance Directory' ?></a>
                    </div>
                </div>

                <!-- 4. Services / Taxonomies -->
                <div class="col-md-4 col-lg-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light text-center">
                        <div class="rounded-circle bg-info text-white mx-auto d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;font-size:1.4rem;">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক সেবাসমূহ' : 'Civic Services' ?></h6>
                        <small class="text-muted d-block mb-3"><?= ($locale ?? 'bn') === 'bn' ? 'মোট ক্যাটাগরি: ' . to_bn_number((string)($platform['total_service_categories'] ?? 0)) : 'Categories: ' . ($platform['total_service_categories'] ?? 0) ?></small>
                        <button class="btn btn-sm btn-outline-info text-dark mt-auto" onclick="alert('নাগরিক সেবা ও সাবক্যাটেগরি কনফিগারেশন মডিউল প্রস্তুত।')"><?= ($locale ?? 'bn') === 'bn' ? 'সেবা কনফিগারেশন' : 'Manage Services' ?></button>
                    </div>
                </div>

                <!-- 5. Responsibilities / Routing -->
                <div class="col-md-4 col-lg-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light text-center">
                        <div class="rounded-circle bg-primary-subtle text-primary mx-auto d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;font-size:1.4rem;">
                            <i class="bi bi-diagram-3-fill"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'স্বয়ংক্রিয় রাউটিং রুল' : 'Routing Rules' ?></h6>
                        <small class="text-muted d-block mb-3"><?= ($locale ?? 'bn') === 'bn' ? '৪-স্তর বিশিষ্ট রুল ক্যাসকেড' : '4-Tier Rule Cascade' ?></small>
                        <button class="btn btn-sm btn-outline-primary mt-auto" onclick="alert('স্বয়ংক্রিয় রাউটিং রুল মডিউল প্রস্তুত।')"><?= ($locale ?? 'bn') === 'bn' ? 'রাউটিং রুল দেখুন' : 'Routing Cascade' ?></button>
                    </div>
                </div>

                <!-- 6. Notices -->
                <div class="col-md-4 col-lg-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light text-center">
                        <div class="rounded-circle bg-secondary text-white mx-auto d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;font-size:1.4rem;">
                            <i class="bi bi-bell-fill"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'পৌর নোটিশ বোর্ড' : 'City Notices' ?></h6>
                        <small class="text-muted d-block mb-3"><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিকদের জন্য জরুরি বিজ্ঞপ্তি' : 'Public City Broadcasts' ?></small>
                        <a href="/notices" class="btn btn-sm btn-outline-secondary mt-auto"><?= ($locale ?? 'bn') === 'bn' ? 'নোটিশ পরিচালনা' : 'Manage Notices' ?></a>
                    </div>
                </div>

                <!-- 7. Gaps & Alerts -->
                <div class="col-md-4 col-lg-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light text-center">
                        <div class="rounded-circle <?= ($platform['routing_gap_count'] ?? 0) > 0 ? 'bg-danger text-white' : 'bg-success text-white' ?> mx-auto d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;font-size:1.4rem;">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'রাউটিং গ্যাপ অ্যালার্ট' : 'Routing Gap Alerts' ?></h6>
                        <small class="text-muted d-block mb-3"><?= ($platform['routing_gap_count'] ?? 0) > 0 ? to_bn_number((string)$platform['routing_gap_count']) . ' টি গ্যাপ সক্রিয়' : 'কোনো গ্যাপ নেই' ?></small>
                        <button class="btn btn-sm <?= ($platform['routing_gap_count'] ?? 0) > 0 ? 'btn-outline-danger' : 'btn-outline-success' ?> mt-auto" onclick="alert('রাউটিং গ্যাপ বিশ্লেষণ রিপোর্ট প্রস্তুত।')"><?= ($locale ?? 'bn') === 'bn' ? 'গ্যাপ সমাধান করুন' : 'Inspect Gaps' ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
