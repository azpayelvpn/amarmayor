<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
            <div class="card border shadow-sm rounded-4 p-3 p-sm-4 bg-white">
                <div class="text-center mb-4">
                    <div class="brand-badge mx-auto mb-2">
                        <i class="bi bi-person-check fs-4"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">
                        <?= ($locale ?? 'bn') === 'bn' ? 'লগইন / প্রবেশ' : 'Sign In' ?>
                    </h3>
                    <p class="text-muted small mb-0">
                        <?= ($locale ?? 'bn') === 'bn' ? 'ময়মনসিংহ সিটি কর্পোরেশন সেবা প্ল্যাটফর্ম' : 'Mymensingh City Corporation Service Platform' ?>
                    </p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?= e(__('auth.' . $error, [], null) !== 'auth.' . $error ? __('auth.' . $error) : $error) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <div><?= e($success) ?></div>
                    </div>
                <?php endif; ?>

                <!-- Tab Navigation -->
                <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-pill" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold py-2" id="tab-btn-citizen" type="button" onclick="switchLoginTab('citizen')">
                            <i class="bi bi-person me-1"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'নাগরিক' : 'Citizen' ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2 text-muted" id="tab-btn-staff" type="button" onclick="switchLoginTab('staff')">
                            <i class="bi bi-shield-lock me-1"></i>
                            <?= ($locale ?? 'bn') === 'bn' ? 'কর্মকর্তা ও কর্মচারী' : 'Staff / Officer' ?>
                        </button>
                    </li>
                </ul>

                <!-- 1. Citizen OTP Login Form Container -->
                <div id="citizen-login-container">
                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-3 border-0 bg-info-subtle text-info-emphasis">
                        <i class="bi bi-info-circle me-1"></i>
                        <?= ($locale ?? 'bn') === 'bn' ? 'আপনার মোবাইল নম্বরে একটি যাচাইকরণ কোড পাঠানো হবে।' : 'A verification code will be sent to your mobile number.' ?>
                    </div>

                    <?php if (($step ?? 'request') === 'verify'): ?>
                        <?php if (\AmarMayor\Support\Config::get('app.env') !== 'production'): ?>
                            <div class="alert alert-warning py-2 px-3 small rounded-3 mb-3 border-0 bg-warning-subtle text-warning-emphasis">
                                <i class="bi bi-tools me-1"></i>
                                <strong><?= ($locale ?? 'bn') === 'bn' ? 'পরীক্ষামূলক মোড:' : 'Testing Mode:' ?></strong>
                                <?= ($locale ?? 'bn') === 'bn' ? 'SMS গেটওয়ে সংযুক্ত নয়। যাচাইকরণ কোড' : 'SMS gateway is not connected. The verification code is available in the' ?>
                                <a href="/dev/otp-inbox" target="_blank" class="fw-bold text-decoration-underline text-warning-emphasis">Developer OTP Inbox</a>
                                <?= ($locale ?? 'bn') === 'bn' ? '-এ পাওয়া যাবে।' : '.' ?>
                            </div>
                        <?php endif; ?>

                        <!-- Step 2: Verify OTP -->
                        <form action="/auth/otp/verify" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="phone" value="<?= e($phone ?? '') ?>">

                            <div class="mb-3">
                                <label for="otp_code" class="form-label fw-semibold small text-muted">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'যাচাইকরণ কোড (৬ সংখ্যা)' : 'Verification Code (6 Digits)' ?>
                                </label>
                                <input type="text" id="otp_code" name="otp_code" value="" autocomplete="off" required autofocus maxlength="6" pattern="[0-9]{6}"
                                       placeholder="123456"
                                       class="form-control form-control-lg text-center fs-4 letter-spacing-2">
                            </div>

                            <button type="submit" class="btn btn-civic-primary btn-lg w-100 py-2 mb-2">
                                <?= ($locale ?? 'bn') === 'bn' ? 'যাচাই করে প্রবেশ করুন' : 'Verify & Enter' ?>
                            </button>

                            <div class="text-center mt-2">
                                <a href="/login" class="text-muted small text-decoration-none">
                                    &larr; <?= ($locale ?? 'bn') === 'bn' ? 'নম্বর পরিবর্তন করুন' : 'Change Phone Number' ?>
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <!-- Step 1: Request OTP -->
                        <form action="/auth/otp/request" method="POST">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-semibold small text-muted">
                                    <?= ($locale ?? 'bn') === 'bn' ? 'মোবাইল নম্বর' : 'Mobile Number' ?>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted fw-bold">+88</span>
                                    <input type="tel" id="phone" name="phone" required autofocus
                                           placeholder="017XXXXXXXX"
                                           class="form-control form-control-lg">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-civic-primary btn-lg w-100 py-2">
                                <?= ($locale ?? 'bn') === 'bn' ? 'ওটিপি পাঠান' : 'Send OTP' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- 2. Staff Password Login Form Container -->
                <div id="staff-login-container" class="d-none">
                    <form action="/login/password" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="identifier" class="form-label fw-semibold small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'ইমেইল বা মোবাইল নম্বর' : 'Email or Phone' ?>
                            </label>
                            <input type="text" id="identifier" name="identifier" required
                                   placeholder="staff@mymensinghcity.gov.bd / 017XXXXXXXX"
                                   class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold small text-muted">
                                <?= ($locale ?? 'bn') === 'bn' ? 'পাসওয়ার্ড' : 'Password' ?>
                            </label>
                            <input type="password" id="password" name="password" required
                                   placeholder="••••••••"
                                   class="form-control">
                        </div>

                        <button type="submit" class="btn btn-civic-primary btn-lg w-100 py-2">
                            <?= ($locale ?? 'bn') === 'bn' ? 'লগইন করুন' : 'Sign In' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchLoginTab(tab) {
    const citizenContainer = document.getElementById('citizen-login-container');
    const staffContainer = document.getElementById('staff-login-container');
    const citizenBtn = document.getElementById('tab-btn-citizen');
    const staffBtn = document.getElementById('tab-btn-staff');

    if (tab === 'staff') {
        citizenContainer.classList.add('d-none');
        staffContainer.classList.remove('d-none');
        staffBtn.classList.add('active', 'text-white');
        staffBtn.classList.remove('text-muted');
        citizenBtn.classList.remove('active');
        citizenBtn.classList.add('text-muted');
    } else {
        staffContainer.classList.add('d-none');
        citizenContainer.classList.remove('d-none');
        citizenBtn.classList.add('active');
        citizenBtn.classList.remove('text-muted');
        staffBtn.classList.remove('active', 'text-white');
        staffBtn.classList.add('text-muted');
    }
}

// Auto-switch to staff tab if specified in URL query
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('tab') === 'staff') {
    switchLoginTab('staff');
}
</script>
