<!-- Public Viewer Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-eye-fill text-primary me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'পাবলিক অবজারভার পোর্টাল — নাগরিক জবাবদিহিতা ও স্বচ্ছতা' : 'Public Viewer Portal — Civic Accountability & Transparency' ?>
            </h5>
            <p class="text-muted small mb-4">
                <?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশনের উন্মুক্ত নাগরিক সেবা পরিসংখ্যান ও ওয়ার্ড তথ্যাবলি।' : 'Open civic statistics and public ward governance directory of Mymensingh City Corporation.' ?>
            </p>
            <div class="d-flex gap-2">
                <a href="/wards" class="btn btn-civic-primary"><?= ($locale ?? 'bn') === 'bn' ? 'ওয়ার্ড ডিরেক্টরি ব্রাউজ করুন' : 'Browse Wards Directory' ?></a>
                <a href="/who-is-responsible" class="btn btn-outline-primary"><?= ($locale ?? 'bn') === 'bn' ? 'দায়িত্বশীল ব্যক্তিবর্গ' : 'Who is Responsible?' ?></a>
                <a href="/notices" class="btn btn-outline-secondary"><?= ($locale ?? 'bn') === 'bn' ? 'পৌর নোটিশ' : 'City Notices' ?></a>
            </div>
        </div>
    </div>
</div>
