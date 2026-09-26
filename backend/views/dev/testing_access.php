<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <div class="badge bg-warning text-dark px-3 py-1 rounded-pill mb-2 fw-semibold">
                <i class="bi bi-tools me-1"></i> Developer Testing Environment Only
            </div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i>
                Development Demo Accounts (All 22 Canonical Roles)
            </h3>
            <p class="text-muted small mb-0">
                Pre-configured fictional testing identities for every system role. Standard password: <code>Demo@12345</code>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="/dev/otp-inbox" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-inbox-fill me-1"></i> Developer OTP Inbox
            </a>
            <a href="/login" class="btn btn-civic-primary btn-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i> Go to Login
            </a>
        </div>
    </div>

    <!-- Citizen Demo Identities -->
    <div class="card border shadow-sm rounded-4 bg-white p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3">
            <i class="bi bi-person-circle text-success me-2"></i>
            Citizen Demo Identities (OTP Authentication)
        </h5>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light">
                    <span class="badge bg-success mb-2">Citizen Demo A</span>
                    <h6 class="fw-bold text-dark mb-1">Demo Citizen A (নাগরিক ক)</h6>
                    <div class="small text-muted mb-2">Phone: <strong class="text-dark font-monospace">01711000001</strong></div>
                    <form action="/auth/otp/request" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="phone" value="01711000001">
                        <button type="submit" class="btn btn-sm btn-outline-success">
                            Request OTP for Demo A &rarr;
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light">
                    <span class="badge bg-success mb-2">Citizen Demo B</span>
                    <h6 class="fw-bold text-dark mb-1">Demo Citizen B (নাগরিক খ)</h6>
                    <div class="small text-muted mb-2">Phone: <strong class="text-dark font-monospace">01711000002</strong></div>
                    <form action="/auth/otp/request" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="phone" value="01711000002">
                        <button type="submit" class="btn btn-sm btn-outline-success">
                            Request OTP for Demo B &rarr;
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- All 22 Canonical Roles Table -->
    <div class="card border shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-light border-0 py-3 px-4">
            <h5 class="fw-bold text-dark mb-0">Staff & Officer Demo Accounts (Password: <code>Demo@12345</code>)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Role & Scope</th>
                        <th class="py-3">Demo Name</th>
                        <th class="py-3">Login Email</th>
                        <th class="py-3">Password</th>
                        <th class="text-end pe-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demoUsers)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Demo accounts not seeded yet. Run <code>php backend/scripts/seed_demo.php</code> or reload.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($demoUsers as $u): ?>
                            <?php if ($u['user_type'] === 'citizen') continue; ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1">
                                        <?= e($u['role_name_en'] ?? $u['role_slug']) ?>
                                    </span>
                                    <small class="text-muted d-block"><?= e($u['role_name_bn'] ?? '') ?></small>
                                </td>
                                <td>
                                    <strong><?= e($u['full_name_en'] ?? 'Demo User') ?></strong>
                                    <small class="text-muted d-block"><?= e($u['designation_en'] ?? '') ?></small>
                                </td>
                                <td class="font-monospace text-dark">
                                    <?= e($u['email']) ?>
                                </td>
                                <td class="font-monospace text-muted">
                                    <code>Demo@12345</code>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <?php if (($u['role_slug'] ?? '') === 'field_worker'): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 small">
                                                <i class="bi bi-person-x me-1"></i>ম্যানুয়াল কর্মী — সরাসরি লগইন নেই (মাঠ সম্পদ)
                                            </span>
                                        <?php else: ?>
                                            <form action="/login/password" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="identifier" value="<?= e($u['email']) ?>">
                                                <input type="hidden" name="password" value="Demo@12345">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    Login &rarr;
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="/dashboard" class="btn btn-sm btn-outline-secondary">
                                            Dashboard
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
