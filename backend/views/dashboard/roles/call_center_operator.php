<!-- Call Center Operator Dashboard: 1-Minute Rapid Intake -->
<div class="row g-4 mb-4">
    <!-- 1-Minute Rapid Phone Intake Form -->
    <div class="col-lg-6">
        <div class="card border-primary shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-headset text-primary me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? '১-মিনিট দ্রুত ফোন ইনটেক (Rapid Intake)' : '1-Minute Rapid Phone Intake' ?>
                </h5>
                <span class="badge bg-success rounded-pill px-3 py-1">হটলাইন অপারেটর</span>
            </div>
            <p class="text-muted small mb-3">
                নাগরিক কল চলাকালীন এক পাতায় অতি দ্রুত অভিযোগ নিবন্ধন করুন। তাৎক্ষণিক এসএমএস ট্র্যাকিং নম্বর প্রেরিত হবে।
            </p>

            <form action="/dashboard/call-center/submit" method="POST" id="rapidIntakeForm">
                <?= csrf_field() ?>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark">কলকারী নাগরিকের মোবাইল নম্বর *</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted fw-bold">+88</span>
                            <input type="tel" name="phone" required placeholder="017XXXXXXXX" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark">ওয়ার্ড নম্বর *</label>
                        <select name="ward_id" class="form-select form-select-sm" required>
                            <option value="">-- ওয়ার্ড বেছে নিন --</option>
                            <?php foreach ($zones ?? [] as $z): ?>
                                <optgroup label="<?= e($z['name_bn']) ?>">
                                    <?php foreach ($z['wards'] as $w): ?>
                                        <option value="<?= (int)$w['id'] ?>">
                                            ওয়ার্ড নং <?= to_bn_number((string)$w['ward_number']) ?> — <?= e($w['name_bn']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark">খাত / ক্যাটাগরি *</label>
                        <select name="category_id" id="cc_category_id" class="form-select form-select-sm" required onchange="filterCallCenterSubcategories()">
                            <option value="">-- ক্যাটাগরি --</option>
                            <?php foreach ($categories ?? [] as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name_bn']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark">নির্দিষ্ট সমস্যা *</label>
                        <select name="subcategory_id" id="cc_subcategory_id" class="form-select form-select-sm" required>
                            <option value="">-- প্রথমে ক্যাটাগরি বাছুন --</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">সুনির্দিষ্ট স্থান / ল্যান্ডমার্ক</label>
                    <input type="text" name="landmark" placeholder="উদাঃ জেলা পরিষদ মোড়, বড় মসজিদের পাশে" class="form-control form-control-sm">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">সমস্যার সংক্ষিপ্ত বিবরণ *</label>
                    <textarea name="description" rows="2" required placeholder="নাগরিকের বলা সমস্যার মূল বিবরণ লিখুন..." class="form-control form-control-sm"></textarea>
                </div>

                <div class="card border-warning bg-warning bg-opacity-10 p-2 rounded-3 mb-3">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="cc_is_emergency" name="is_emergency" value="1">
                        <label class="form-check-label small fw-bold text-dark" for="cc_is_emergency">
                            🚨 এটি কি অতি জরুরি / মারাত্মক বিপত্তি? (খোলা ম্যানহোল, ঝুলন্ত তার ইত্যাদি)
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-civic-primary w-100 fw-bold py-2">
                    <i class="bi bi-send-check-fill me-1"></i>
                    তাত্ক্ষণিক অভিযোগ দাখিল ও এসএমএস প্রেরণ (১ মিনিট)
                </button>
            </form>
        </div>
    </div>

    <!-- Recently Registered Intakes & Status Lookup -->
    <div class="col-lg-6">
        <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-telephone-inbound-fill text-success me-2"></i>
                    <?= ($locale ?? 'bn') === 'bn' ? 'সম্প্রতি নিবন্ধিত ফোন ইনটেক' : 'Recently Registered Intakes' ?>
                </h5>
                <span class="small text-muted">সর্বশেষ ১০টি</span>
            </div>

            <div class="mb-3 p-3 bg-light rounded-3 border">
                <label class="form-label fw-semibold small text-muted mb-1"><?= ($locale ?? 'bn') === 'bn' ? 'ফোনে কলকারী নাগরিকের ট্র্যাকিং অনুসন্ধান:' : 'Live Phone Lookup:' ?></label>
                <form action="/track" method="GET" class="d-flex gap-2">
                    <input type="text" name="tracking_number" placeholder="MCC-2608-00001" required class="form-control form-control-sm font-monospace text-uppercase">
                    <button type="submit" class="btn btn-sm btn-primary"><?= ($locale ?? 'bn') === 'bn' ? 'অবস্থা দেখুন' : 'Search' ?></button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-2">ট্র্যাকিং নং</th>
                            <th>ধরন</th>
                            <th>ওয়ার্ড</th>
                            <th class="text-end pe-2">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php if (empty($recentIntakes)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">বর্তমানে কোনো নিবন্ধিত ইনটেক নেই।</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentIntakes as $item): ?>
                                <tr>
                                    <td class="ps-2 font-monospace fw-bold text-dark"><?= e($item['public_complaint_number'] ?? '') ?></td>
                                    <td><?= e($item['subcategory_name_bn'] ?? '') ?></td>
                                    <td>ওয়ার্ড <?= to_bn_number((string)($item['ward_number'] ?? '')) ?></td>
                                    <td class="text-end pe-2">
                                        <a href="/track/<?= urlencode($item['public_complaint_number'] ?? '') ?>" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;">
                                            ট্র্যাক
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const categoriesData = <?= json_encode($categories ?? [], JSON_UNESCAPED_UNICODE) ?>;
function filterCallCenterSubcategories() {
    const catId = parseInt(document.getElementById('cc_category_id').value);
    const subSelect = document.getElementById('cc_subcategory_id');
    subSelect.innerHTML = '<option value="">-- সুনির্দিষ্ট সমস্যা বাছুন --</option>';
    
    if (!catId) return;
    
    const cat = categoriesData.find(c => c.id === catId);
    if (cat && cat.subcategories) {
        cat.subcategories.forEach(sub => {
            const opt = document.createElement('option');
            opt.value = sub.id;
            opt.textContent = sub.name_bn || sub.name_en;
            subSelect.appendChild(opt);
        });
    }
}
</script>
