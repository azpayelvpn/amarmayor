<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6 text-center">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-5">
                <div class="rounded-circle bg-danger-subtle text-danger mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; font-size: 2.5rem;">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h1 class="display-5 fw-bold text-dark mb-2">৪০৩</h1>
                <h4 class="fw-bold text-danger mb-3">
                    <?= ($locale ?? 'bn') === 'bn' ? 'অনুমতি নেই (Forbidden)' : 'Access Forbidden' ?>
                </h4>
                <p class="text-muted mb-4">
                    <?= ($locale ?? 'bn') === 'bn' 
                        ? 'আপনার বর্তমান পদমর্যাদা বা এলাকা অনুযায়ী এই তথ্য দেখার অনুমতি নেই।' 
                        : 'You do not have the required permissions or assigned jurisdictional scope to access this page.' ?>
                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="/dashboard" class="btn btn-civic-primary px-4 py-2">
                        <i class="bi bi-speedometer2 me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'ড্যাশবোর্ডে ফিরে যান' : 'Go to Dashboard' ?>
                    </a>
                    <a href="/" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-house-door me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'নীড় পাতা' : 'Home' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
