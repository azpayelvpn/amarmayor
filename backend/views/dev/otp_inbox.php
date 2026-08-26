<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <div class="badge bg-warning text-dark px-3 py-1 rounded-pill mb-2 fw-semibold">
                <i class="bi bi-tools me-1"></i> Developer Testing Environment Only
            </div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-inbox-fill text-primary me-2"></i>
                Mock OTP Verification Inbox
            </h3>
            <p class="text-muted small mb-0">
                Live temporary log of Mock OTP requests for local citizen authentication testing.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="/dev/otp-inbox" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </a>
            <a href="/dev/testing-access" class="btn btn-civic-primary btn-sm">
                <i class="bi bi-people-fill me-1"></i> Demo Role Accounts
            </a>
        </div>
    </div>

    <?php if (empty($otps)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <div class="rounded-circle bg-light text-muted mx-auto d-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;font-size:2rem;">
                <i class="bi bi-envelope-open"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Recent OTP Requests</h5>
            <p class="text-muted small mb-4">Request an OTP from the login or complaint submission page to see it recorded here.</p>
            <div>
                <a href="/login" class="btn btn-civic-primary px-4 py-2">
                    Go to Citizen Login
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Phone Number</th>
                            <th class="py-3">OTP Code</th>
                            <th class="py-3">Created Time</th>
                            <th class="py-3">Expires At</th>
                            <th class="py-3">Status</th>
                            <th class="text-end pe-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($otps as $item): ?>
                            <?php
                                $isExpired = strtotime($item['expires_at']) < time();
                                $isUsed = !empty($item['used']);
                            ?>
                            <tr class="<?= $isUsed ? 'table-light text-muted' : '' ?>">
                                <td class="ps-4 font-monospace fw-bold text-dark">
                                    <?= e($item['phone']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary fs-6 px-3 py-2 font-monospace tracking-wide">
                                        <?= e($item['otp']) ?>
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <?= e($item['created_at']) ?>
                                </td>
                                <td class="small text-muted">
                                    <?= e($item['expires_at']) ?>
                                </td>
                                <td>
                                    <?php if ($isUsed): ?>
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1">Used</span>
                                    <?php elseif ($isExpired): ?>
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1">Expired</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success px-2 py-1">Active / Ready</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="/login?step=verify&phone=<?= urlencode($item['phone']) ?>&tab=otp" class="btn btn-sm btn-outline-primary">
                                        Fill in Login &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
