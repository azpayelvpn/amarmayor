<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 text-center">
            <div class="card border shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="rounded-circle bg-success-subtle text-success mx-auto d-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px;font-size:2.2rem;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>

                <h3 class="fw-bold text-dark mb-2">
                    <?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ সফলভাবে গ্রহণ করা হয়েছে' : 'Complaint Submitted Successfully' ?>
                </h3>
                <p class="text-muted mb-4">
                    <?= ($locale ?? 'bn') === 'bn' ? 'আপনার অভিযোগটি সংশ্লিষ্ট ওয়ার্ড ও দায়িত্বপ্রাপ্ত বিভাগে প্রেরণ করা হয়েছে।' : 'Your complaint has been received and routed to the responsible department.' ?>
                </p>

                <!-- Tracking Card -->
                <div class="p-3 bg-light rounded-3 border mb-4">
                    <span class="text-muted small fw-semibold d-block mb-1">
                        <?= ($locale ?? 'bn') === 'bn' ? 'আপনার ট্র্যাকিং নম্বর (সংরক্ষণ করুন)' : 'Tracking Number (Save for updates)' ?>
                    </span>
                    <div class="fs-2 fw-bold text-primary tracking-wide">
                        <?= e($trackingNumber ?? '') ?>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="/track/<?= e($trackingNumber ?? '') ?>" class="btn btn-civic-primary px-4 py-2">
                        <i class="bi bi-search me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'অভিযোগ দেখুন / ট্র্যাক করুন' : 'Track Complaint' ?>
                    </a>
                    <a href="/" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-house-door me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'হোমে ফিরুন' : 'Back to Home' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
