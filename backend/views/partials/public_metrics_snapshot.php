<div class="row g-3">
    <!-- Received -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="metric-box">
            <div class="metric-number text-primary"><?= to_bn_number((string)($metrics['total_received'] ?? 0)) ?></div>
            <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'মোট অভিযোগ' : 'Total Received' ?></div>
        </div>
    </div>

    <!-- In Progress -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="metric-box">
            <div class="metric-number text-warning"><?= to_bn_number((string)($metrics['in_progress'] ?? 0)) ?></div>
            <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'কাজ চলছে' : 'In Progress' ?></div>
        </div>
    </div>

    <!-- Work Completed -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="metric-box">
            <div class="metric-number text-info"><?= to_bn_number((string)($metrics['work_completed'] ?? 0)) ?></div>
            <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'কাজ সম্পন্ন' : 'Work Completed' ?></div>
        </div>
    </div>

    <!-- Citizen Confirmed Resolved -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="metric-box">
            <div class="metric-number text-success"><?= to_bn_number((string)($metrics['citizen_confirmed_resolved'] ?? 0)) ?></div>
            <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'সমাধান হয়েছে' : 'Resolved' ?></div>
        </div>
    </div>

    <!-- Currently Overdue -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="metric-box">
            <div class="metric-number text-danger"><?= to_bn_number((string)($metrics['currently_overdue'] ?? 0)) ?></div>
            <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'সময় পেরিয়েছে' : 'Overdue' ?></div>
        </div>
    </div>

    <!-- Needs More Work -->
    <div class="col-6 col-md-4 col-lg-2">
        <div class="metric-box">
            <div class="metric-number text-secondary"><?= to_bn_number((string)($metrics['needs_more_work'] ?? 0)) ?></div>
            <div class="metric-label"><?= ($locale ?? 'bn') === 'bn' ? 'আবার কাজ প্রয়োজন' : 'Needs More Work' ?></div>
        </div>
    </div>
</div>
