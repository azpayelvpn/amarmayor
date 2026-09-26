<!-- Platform Super Admin Non-Technical Governance Dashboard -->
<div class="row g-4 mb-4">
    <!-- Non-Technical Administration Modules Overview -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-sliders text-primary me-2"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'প্ল্যাটফর্ম সুপার অ্যাডমিন — প্রতিষ্ঠান, জনবল ও পরিচালনা কনফিগারেশন' : 'Platform Super Admin — Institutional Governance & Operations' ?>
                    </h5>
                    <p class="text-muted small mb-0">
                        <?= ($locale ?? 'bn') === 'bn' ? 'সিটি কর্পোরেশনের জনবল, ওয়ার্ড/অঞ্চল কাঠামো, নাগরিক সেবাসমূহ ও এসএলএ ডেডলাইন ব্যবস্থাপনা' : 'Manage city workforce, areas, civic services, responsibilities, notices and SLA routing rules' ?>
                    </p>
                </div>
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-building-gear me-1"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'প্রাতিষ্ঠানিক নিয়ন্ত্রণ কক্ষ' : 'Governance Command' ?>
                </span>
            </div>

            <!-- Quick Metrics Grid -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white p-3 me-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'কর্মকর্তা ও জনবল' : 'People & Staff' ?></small>
                                <strong class="fs-5 text-dark"><?= to_bn_number((string)($platform['total_active_staff'] ?? 0)) ?> জন</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success text-white p-3 me-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                <i class="bi bi-map-fill"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'অঞ্চল ও ওয়ার্ড এলাকা' : 'Areas & Wards' ?></small>
                                <strong class="fs-5 text-dark">৩৩ ওয়ার্ড | ৩ জোন</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info text-white p-3 me-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                <i class="bi bi-grid-3x3-gap-fill"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক সেবাসমূহ' : 'Civic Services' ?></small>
                                <strong class="fs-5 text-dark"><?= to_bn_number((string)($platform['total_service_categories'] ?? 0)) ?> টি</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle <?= ($platform['routing_gap_count'] ?? 0) > 0 ? 'bg-danger text-white' : 'bg-success text-white' ?> p-3 me-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block"><?= ($locale ?? 'bn') === 'bn' ? 'স্বয়ংক্রিয় রাউটিং' : 'Routing Cascade' ?></small>
                                <strong class="fs-5 <?= ($platform['routing_gap_count'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= ($platform['routing_gap_count'] ?? 0) > 0 ? to_bn_number((string)$platform['routing_gap_count']) . ' টি সক্রিয়' : 'সব রুল সক্রিয়' ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Navigation Tabs -->
            <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" id="adminConfigTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-semibold" id="tab-depts-btn" data-bs-toggle="pill" data-bs-target="#tab-depts" type="button" role="tab">
                        <i class="bi bi-buildings me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'বিভাগ ও জনবল বণ্টন' : 'Departments & Staff' ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold" id="tab-services-btn" data-bs-toggle="pill" data-bs-target="#tab-services" type="button" role="tab">
                        <i class="bi bi-list-task me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক সেবা তালিকা (Taxonomies)' : 'Civic Services' ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold" id="tab-sla-btn" data-bs-toggle="pill" data-bs-target="#tab-sla" type="button" role="tab">
                        <i class="bi bi-clock-history me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'এসএলএ সময়সীমা রুলস (SLA Matrix)' : 'SLA Rules' ?>
                    </button>
                </li>
                <li class="nav-item ms-auto" role="presentation">
                    <div class="d-flex gap-2">
                        <a href="/who-is-responsible" class="btn btn-sm btn-outline-warning text-dark">
                            <i class="bi bi-person-badge me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'দায়িত্বপ্রাপ্ত কর্মকর্তা ডিরেক্টরি' : 'Responsibility Directory' ?>
                        </a>
                        <a href="/wards" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-map me-1"></i><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ডিরেক্টরি' : 'Ward Directory' ?>
                        </a>
                    </div>
                </li>
            </ul>

            <div class="tab-content" id="adminConfigTabsContent">
                <!-- 1. Departments & Staffing -->
                <div class="tab-pane fade show active" id="tab-depts" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'বিভাগের নাম' : 'Department Name' ?></th>
                                    <th><?= ($locale ?? 'bn') === 'bn' ? 'কোড' : 'Code' ?></th>
                                    <th><?= ($locale ?? 'bn') === 'bn' ? 'নির্ধারিত জনবল' : 'Allocated Staff' ?></th>
                                    <th><?= ($locale ?? 'bn') === 'bn' ? 'অবস্থা' : 'Status' ?></th>
                                    <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'ব্যবস্থাপনা' : 'Operations' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allDepartments)): ?>
                                    <tr><td colspan="5" class="text-center py-3 text-muted">No department data.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($allDepartments as $dept): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <strong class="text-dark d-block"><?= e($dept['name_bn']) ?></strong>
                                                <small class="text-muted"><?= e($dept['name_en'] ?? '') ?></small>
                                            </td>
                                            <td><span class="font-monospace text-muted"><?= e($dept['code'] ?? 'DEPT-' . $dept['id']) ?></span></td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary px-3 py-1 fw-semibold">
                                                    <i class="bi bi-person me-1"></i><?= to_bn_number((string)($dept['staff_count'] ?? 0)) ?> জন কর্মী
                                                </span>
                                            </td>
                                            <td><span class="badge bg-success-subtle text-success"><?= e($dept['status'] ?? 'active') ?></span></td>
                                            <td class="text-end pe-3">
                                                <span class="text-muted small"><?= ($locale ?? 'bn') === 'bn' ? 'সক্রিয় পরিচালন' : 'Active Operational' ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Services / Taxonomies -->
                <div class="tab-pane fade" id="tab-services" role="tabpanel">
                    <div class="row g-3">
                        <?php if (empty($allCategories)): ?>
                            <div class="col-12 text-center py-4 text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'কোন ক্যাটাগরি কনফিগার করা নেই।' : 'No categories configured.' ?></div>
                        <?php else: ?>
                            <?php foreach ($allCategories as $cat): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border rounded-3 p-3 h-100 bg-light">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold text-dark mb-0"><?= ($locale ?? 'bn') === 'bn' ? e($cat['name_bn']) : e($cat['name_en']) ?></h6>
                                            <span class="badge bg-secondary-subtle text-dark"><?= count($cat['subcategories'] ?? []) ?> সাবক্যাটেগরি</span>
                                        </div>
                                        <p class="text-muted small mb-2"><?= e($cat['description_bn'] ?? 'নাগরিক সেবা খাত') ?></p>
                                        <ul class="list-unstyled small mb-0">
                                            <?php foreach (array_slice($cat['subcategories'] ?? [], 0, 4) as $sub): ?>
                                                <li class="py-1 border-top text-muted d-flex justify-content-between">
                                                    <span>• <?= ($locale ?? 'bn') === 'bn' ? e($sub['name_bn']) : e($sub['name_en']) ?></span>
                                                    <span class="badge bg-light text-muted border"><?= e($sub['code'] ?? '') ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 3. SLA Rules Matrix -->
                <div class="tab-pane fade" id="tab-sla" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3"><?= ($locale ?? 'bn') === 'bn' ? 'সেবার উপ-বিভাগ' : 'Subcategory' ?></th>
                                    <th><?= ($locale ?? 'bn') === 'bn' ? 'অগ্রাধিকার' : 'Priority' ?></th>
                                    <th><?= ($locale ?? 'bn') === 'bn' ? 'রেসপন্স সময়সীমা (First Response)' : 'First Response SLA' ?></th>
                                    <th><?= ($locale ?? 'bn') === 'bn' ? 'সমাধান সময়সীমা (Resolution SLA)' : 'Resolution SLA' ?></th>
                                    <th class="text-end pe-3"><?= ($locale ?? 'bn') === 'bn' ? 'এসকেলেশন ট্রিগার' : 'Escalation' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($slaRules)): ?>
                                    <tr><td colspan="5" class="text-center py-3 text-muted">No SLA rules defined.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($slaRules as $rule): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-dark"><?= e($rule['subcategory_name_bn'] ?? 'সার্বিক নাগরিক সেবা') ?></td>
                                            <td>
                                                <span class="badge bg-warning-subtle text-dark"><?= e($rule['priority'] ?? 'standard') ?></span>
                                            </td>
                                            <td><?= to_bn_number((string)($rule['expected_hours'] ?? 24)) ?> ঘণ্টা</td>
                                            <td><strong class="text-primary"><?= to_bn_number((string)($rule['expected_hours'] ?? 72)) ?> ঘণ্টা</strong></td>
                                            <td class="text-end pe-3">
                                                <span class="badge bg-danger-subtle text-danger">ডেডলাইন অতিক্রমে মেয়রের ড্যাশবোর্ডে এসকেলেশন</span>
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
    </div>
</div>
