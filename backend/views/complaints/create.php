<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm rounded-4 p-4 bg-white">
                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <div>
                        <h3 class="fw-bold text-dark mb-1">
                            <i class="bi bi-megaphone-fill text-success me-2"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক অভিযোগ দাখিল' : 'Submit Citizen Complaint' ?>
                        </h3>
                        <p class="text-muted small mb-0">
                            <?= ($locale ?? 'bn') === 'bn' ? 'আপনার এলাকার সমস্যা সহজে জানান। ৪টি ধাপে সম্পন্ন করুন।' : 'Report local municipal issues in 4 simple steps.' ?>
                        </p>
                    </div>
                    <a href="/" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 px-3 small rounded-3 mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?= e($error) ?></div>
                    </div>
                <?php endif; ?>

                <!-- 4 Step Indicator -->
                <div class="step-indicator">
                    <div class="step-item active" id="step-ind-1">
                        <div class="step-circle">১</div>
                        <div class="step-label"><?= ($locale ?? 'bn') === 'bn' ? 'কী সমস্যা?' : 'Category' ?></div>
                    </div>
                    <div class="step-item" id="step-ind-2">
                        <div class="step-circle">২</div>
                        <div class="step-label"><?= ($locale ?? 'bn') === 'bn' ? 'কোথায়?' : 'Location' ?></div>
                    </div>
                    <div class="step-item" id="step-ind-3">
                        <div class="step-circle">৩</div>
                        <div class="step-label"><?= ($locale ?? 'bn') === 'bn' ? 'বিবরণ' : 'Details' ?></div>
                    </div>
                    <div class="step-item" id="step-ind-4">
                        <div class="step-circle">৪</div>
                        <div class="step-label"><?= ($locale ?? 'bn') === 'bn' ? 'জমা দিন' : 'Submit' ?></div>
                    </div>
                </div>

                <!-- Complaint Submission Form -->
                <form action="/complaints/create" method="POST" id="complaintForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- STEP 1: কী সমস্যা? -->
                    <div class="step-section" id="step-1">
                        <h5 class="fw-bold mb-3 text-dark"><?= ($locale ?? 'bn') === 'bn' ? '১. সমস্যার ধরন নির্বাচন করুন' : '1. Select Problem Category' ?></h5>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label fw-semibold text-muted small">
                                <?= ($locale ?? 'bn') === 'bn' ? 'মূল খাত / ক্যাটাগরি *' : 'Main Category *' ?>
                            </label>
                            <select id="category_id" name="category_id" class="form-select form-select-lg" required onchange="filterSubcategories()">
                                <option value=""><?= ($locale ?? 'bn') === 'bn' ? '-- ক্যাটাগরি বেছে নিন --' : '-- Choose Category --' ?></option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>">
                                        <?= ($locale ?? 'bn') === 'bn' ? e($cat['name_bn']) : e($cat['name_en']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="subcategory_id" class="form-label fw-semibold text-muted small">
                                <?= ($locale ?? 'bn') === 'bn' ? 'নির্দিষ্ট সমস্যা *' : 'Specific Subcategory *' ?>
                            </label>
                            <select id="subcategory_id" name="subcategory_id" class="form-select form-select-lg" required>
                                <option value=""><?= ($locale ?? 'bn') === 'bn' ? '-- প্রথমে ক্যাটাগরি বেছে নিন --' : '-- Select Category First --' ?></option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-civic-primary btn-lg px-4" onclick="goToStep(2)">
                                <?= ($locale ?? 'bn') === 'bn' ? 'পরবর্তী &rarr;' : 'Next &rarr;' ?>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: কোথায়? -->
                    <div class="step-section d-none" id="step-2">
                        <h5 class="fw-bold mb-3 text-dark"><?= ($locale ?? 'bn') === 'bn' ? '২. সমস্যার স্থান ও ওয়ার্ড' : '2. Problem Location & Ward' ?></h5>

                        <div class="mb-3">
                            <label for="ward_id" class="form-label fw-semibold text-muted small">
                                <?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড নম্বর *' : 'Ward Number *' ?>
                            </label>
                            <select id="ward_id" name="ward_id" class="form-select form-select-lg" required>
                                <option value=""><?= ($locale ?? 'bn') === 'bn' ? '-- ওয়ার্ড নির্বাচন করুন --' : '-- Choose Ward --' ?></option>
                                <?php foreach ($zones as $z): ?>
                                    <optgroup label="<?= ($locale ?? 'bn') === 'bn' ? e($z['name_bn']) : e($z['name_en']) ?>">
                                        <?php foreach ($z['wards'] as $w): ?>
                                            <option value="<?= (int)$w['id'] ?>">
                                                <?= ($locale ?? 'bn') === 'bn' ? "ওয়ার্ড নং " . to_bn_number((string)$w['ward_number']) . " — " . e($w['name_bn']) : "Ward " . $w['ward_number'] . " — " . e($w['name_en']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="landmark" class="form-label fw-semibold text-muted small">
                                <?= ($locale ?? 'bn') === 'bn' ? 'নিকটবর্তী ল্যান্ডমার্ক / চেনার সহজ স্থান' : 'Nearby Landmark' ?>
                            </label>
                            <input type="text" id="landmark" name="landmark" class="form-control"
                                   placeholder="<?= ($locale ?? 'bn') === 'bn' ? 'উদাঃ টাউন হল মোড়, জেলা পরিষদ সংলগ্ন' : 'e.g. Near Town Hall junction' ?>">
                        </div>

                        <div class="mb-4">
                            <label for="approximate_address" class="form-label fw-semibold text-muted small">
                                <?= ($locale ?? 'bn') === 'bn' ? 'রাস্তা / মহল্লার নাম' : 'Street / Area Name' ?>
                            </label>
                            <input type="text" id="approximate_address" name="approximate_address" class="form-control"
                                   placeholder="<?= ($locale ?? 'bn') === 'bn' ? 'উদাঃ স্টেশন রোড, সেনবাড়ি' : 'e.g. Station Road, Senbari' ?>">
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="goToStep(1)">
                                <?= ($locale ?? 'bn') === 'bn' ? '&larr; পূর্ববর্তী' : '&larr; Back' ?>
                            </button>
                            <button type="button" class="btn btn-civic-primary btn-lg px-4" onclick="goToStep(3)">
                                <?= ($locale ?? 'bn') === 'bn' ? 'পরবর্তী &rarr;' : 'Next &rarr;' ?>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: বিবরণ ও ছবি -->
                    <div class="step-section d-none" id="step-3">
                        <h5 class="fw-bold mb-3 text-dark"><?= ($locale ?? 'bn') === 'bn' ? '৩. সমস্যার বিস্তারিত বিবরণ' : '3. Problem Description' ?></h5>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold text-muted small">
                                <?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার বিস্তারিত বিবরণ লিখুন *' : 'Describe the problem in detail *' ?>
                            </label>
                            <textarea id="description" name="description" rows="4" class="form-control" required
                                      placeholder="<?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার প্রকৃতি ও বিস্তারিত অবস্থা লিখুন...' : 'Describe the issue...' ?>"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="photo" class="form-label fw-semibold text-muted small">
                                <?= ($locale ?? 'bn') === 'bn' ? 'সমস্যার ছবি সংযুক্ত করুন (ঐচ্ছিক)' : 'Attach Photo (Optional)' ?>
                            </label>
                            <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                            <div class="form-text small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'ছবি দিলে সমস্যা দ্রুত চিহ্নিত ও সমাধান করা সহজ হয়।' : 'Photos help field teams locate and resolve issues faster.' ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="goToStep(2)">
                                <?= ($locale ?? 'bn') === 'bn' ? '&larr; পূর্ববর্তী' : '&larr; Back' ?>
                            </button>
                            <button type="button" class="btn btn-civic-primary btn-lg px-4" onclick="goToStep(4)">
                                <?= ($locale ?? 'bn') === 'bn' ? 'পরবর্তী &rarr;' : 'Next &rarr;' ?>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 4: দেখে পাঠান -->
                    <div class="step-section d-none" id="step-4">
                        <h5 class="fw-bold mb-3 text-dark"><?= ($locale ?? 'bn') === 'bn' ? '৪. তথ্য যাচাই ও দাখিল' : '4. Review & Submit' ?></h5>

                        <?php if (empty($user)): ?>
                            <div class="alert alert-light border p-3 rounded-3 mb-3">
                                <label for="phone" class="form-label fw-semibold text-muted small">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'আপনার মোবাইল নম্বর (এসএমএস ও ট্র্যাকিং আপডেটের জন্য) *' : 'Your Mobile Number *' ?>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted fw-bold">+88</span>
                                    <input type="tel" id="phone" name="phone" required
                                           placeholder="017XXXXXXXX"
                                           class="form-control">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card bg-light border-0 p-3 rounded-3 mb-4">
                            <h6 class="fw-bold mb-2 text-dark"><?= ($locale ?? 'bn') === 'bn' ? 'সারসংক্ষেপ' : 'Summary' ?></h6>
                            <div class="row g-2 small text-muted">
                                <div class="col-6"><strong><?= ($locale ?? 'bn') === 'bn' ? 'ক্যাটাগরি:' : 'Category:' ?></strong> <span id="rev-cat">-</span></div>
                                <div class="col-6"><strong><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড:' : 'Ward:' ?></strong> <span id="rev-ward">-</span></div>
                                <div class="col-12"><strong><?= ($locale ?? 'bn') === 'bn' ? 'স্থান:' : 'Location:' ?></strong> <span id="rev-loc">-</span></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="goToStep(3)">
                                <?= ($locale ?? 'bn') === 'bn' ? '&larr; পূর্ববর্তী' : '&larr; Back' ?>
                            </button>
                            <button type="submit" class="btn btn-civic-primary btn-lg px-5">
                                <i class="bi bi-send-fill me-1"></i>
                                <?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ জমা দিন' : 'Submit Complaint' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const categoryData = <?= json_encode($categories, JSON_UNESCAPED_UNICODE) ?>;
const isBn = <?= ($locale ?? 'bn') === 'bn' ? 'true' : 'false' ?>;

function filterSubcategories() {
    const catSelect = document.getElementById('category_id');
    const subSelect = document.getElementById('subcategory_id');
    const catId = parseInt(catSelect.value);

    subSelect.innerHTML = '<option value="">' + (isBn ? '-- নির্দিষ্ট সমস্যা বেছে নিন --' : '-- Choose Subcategory --') + '</option>';
    
    const cat = categoryData.find(c => parseInt(c.id) === catId);
    if (cat && cat.subcategories) {
        cat.subcategories.forEach(sub => {
            const opt = document.createElement('option');
            opt.value = sub.id;
            opt.textContent = isBn ? sub.name_bn : sub.name_en;
            subSelect.appendChild(opt);
        });
    }
}

function goToStep(step) {
    if (step === 2) {
        const sub = document.getElementById('subcategory_id').value;
        if (!sub) {
            alert(isBn ? 'অনুগ্রহ করে সমস্যার ধরন ও নির্দিষ্ট সমস্যা নির্বাচন করুন।' : 'Please select category and subcategory.');
            return;
        }
    }
    if (step === 3) {
        const ward = document.getElementById('ward_id').value;
        if (!ward) {
            alert(isBn ? 'অনুগ্রহ করে ওয়ার্ড নির্বাচন করুন।' : 'Please select ward.');
            return;
        }
    }
    if (step === 4) {
        const desc = document.getElementById('description').value.trim();
        if (!desc) {
            alert(isBn ? 'অনুগ্রহ করে সমস্যার বিবরণ লিখুন।' : 'Please describe the problem.');
            return;
        }

        // Update preview
        const catSelect = document.getElementById('category_id');
        const subSelect = document.getElementById('subcategory_id');
        const wardSelect = document.getElementById('ward_id');
        const landmark = document.getElementById('landmark').value;
        const addr = document.getElementById('approximate_address').value;

        document.getElementById('rev-cat').textContent = subSelect.options[subSelect.selectedIndex]?.text || '-';
        document.getElementById('rev-ward').textContent = wardSelect.options[wardSelect.selectedIndex]?.text || '-';
        document.getElementById('rev-loc').textContent = (landmark ? landmark + ', ' : '') + (addr || '');
    }

    // Toggle steps
    for (let i = 1; i <= 4; i++) {
        const section = document.getElementById('step-' + i);
        const ind = document.getElementById('step-ind-' + i);
        if (i === step) {
            section.classList.remove('d-none');
            ind.classList.add('active');
        } else {
            section.classList.add('d-none');
            ind.classList.remove('active');
            if (i < step) {
                ind.classList.add('completed');
            } else {
                ind.classList.remove('completed');
            }
        }
    }
}
</script>
