<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6 text-center">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-5">
                <div class="rounded-circle bg-light text-warning mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; font-size: 2.5rem;">
                    <i class="bi bi-compass"></i>
                </div>
                <h1 class="display-5 fw-bold text-dark mb-2">৪০৪</h1>
                <h4 class="fw-bold text-secondary mb-3">
                    <?= ($locale ?? 'bn') === 'bn' ? 'পেইজটি পাওয়া যায়নি' : 'Page Not Found' ?>
                </h4>
                <p class="text-muted mb-4">
                    <?= ($locale ?? 'bn') === 'bn' 
                        ? 'আপনি যে পৃষ্ঠাটি খুঁজছেন তা হয়তো সরানো হয়েছে বা ঠিকানাটি সঠিক নয়।' 
                        : 'The page you are looking for might have been removed, renamed, or is temporarily unavailable.' ?>
                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="/" class="btn btn-civic-primary px-4 py-2">
                        <i class="bi bi-house-door me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'নীড় পাতায় ফিরে যান' : 'Back to Home' ?>
                    </a>
                    <a href="/track" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-search me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ ট্র্যাক করুন' : 'Track Complaint' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
