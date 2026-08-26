<!-- Generic Role Fallback Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-grid-fill text-primary me-2"></i>
                <?= ($locale ?? 'bn') === 'bn' ? 'অভ্যন্তরীণ সেবা ড্যাশবোর্ড' : 'Internal Operational Dashboard' ?>
            </h5>
            <p class="text-muted small mb-4">
                <?= ($locale ?? 'bn') === 'bn' ? 'আপনার বর্তমান রোলের জন্য কর্মক্ষেত্র প্রস্তুত রয়েছে।' : 'Working portal for your current role is active.' ?>
            </p>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-primary"><?= to_bn_number((string)($kpis['total_complaints'] ?? 0)) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'মোট অভিযোগ' : 'Total Complaints' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-warning-emphasis"><?= to_bn_number((string)($kpis['in_progress'] ?? 0)) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'কাজ চলছে' : 'In Progress' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-danger"><?= to_bn_number((string)($kpis['overdue_count'] ?? 0)) ?></div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'সময় পেরিয়েছে' : 'Overdue' ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center">
                        <div class="fs-2 fw-bold text-success"><?= to_bn_number((string)($kpis['citizen_satisfaction_percent'] ?? 100)) ?>%</div>
                        <small class="text-muted"><?= ($locale ?? 'bn') === 'bn' ? 'সমাধান হার' : 'Satisfaction' ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
