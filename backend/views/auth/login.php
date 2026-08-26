<div class="container mx-auto px-4 py-12 max-w-md">
    <div class="bg-white dark:bg-slate-800 shadow-xl rounded-2xl p-8 border border-slate-200 dark:border-slate-700">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white"><?= e(__('auth.login')) ?></h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1"><?= e(__('app.tagline')) ?></p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
                <?= e(__('auth.' . $error, [], null) !== 'auth.' . $error ? __('auth.' . $error) : $error) ?>
            </div>
        <?php endif; ?>

        <!-- Tab Controls -->
        <div class="flex border-b border-slate-200 dark:border-slate-700 mb-6">
            <button type="button" id="tab-otp" class="flex-1 py-3 text-center font-medium text-emerald-600 border-b-2 border-emerald-600 focus:outline-none" onclick="switchTab('otp')">
                <?= e(__('auth.citizen_login')) ?>
            </button>
            <button type="button" id="tab-password" class="flex-1 py-3 text-center font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none" onclick="switchTab('password')">
                <?= e(__('auth.staff_login')) ?>
            </button>
        </div>

        <!-- Citizen OTP Login Form -->
        <div id="form-otp-container" class="space-y-6">
            <?php if (($step ?? 'request') === 'verify'): ?>
                <form action="/auth/otp/verify" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="phone" value="<?= e($phone ?? '') ?>">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            <?= e(__('auth.otp_code')) ?>
                        </label>
                        <input type="text" name="otp_code" required autofocus maxlength="6" pattern="[0-9]{6}"
                               placeholder="<?= e(__('auth.otp_placeholder')) ?>"
                               class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-center text-xl tracking-widest focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium shadow transition">
                        <?= e(__('auth.verify_otp')) ?>
                    </button>

                    <div class="text-center mt-4">
                        <a href="/login?tab=otp" class="text-sm text-emerald-600 hover:underline">
                            <?= e(__('app.actions.back')) ?>
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <form action="/auth/otp/request" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            <?= e(__('auth.phone_number')) ?>
                        </label>
                        <input type="tel" name="phone" required autofocus
                               placeholder="<?= e(__('auth.phone_placeholder')) ?>"
                               class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium shadow transition">
                        <?= e(__('auth.request_otp')) ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Staff Password Login Form -->
        <div id="form-password-container" class="space-y-6 hidden">
            <form action="/login/password" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        <?= e(__('auth.email_or_phone')) ?>
                    </label>
                    <input type="text" name="identifier" required
                           placeholder="user@example.com / 017XXXXXXXX"
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        <?= e(__('auth.password')) ?>
                    </label>
                    <input type="password" name="password" required
                           placeholder="<?= e(__('auth.password_placeholder')) ?>"
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium shadow transition">
                    <?= e(__('auth.login')) ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    const otpContainer = document.getElementById('form-otp-container');
    const passwordContainer = document.getElementById('form-password-container');
    const otpTab = document.getElementById('tab-otp');
    const passwordTab = document.getElementById('tab-password');

    if (tab === 'password') {
        otpContainer.classList.add('hidden');
        passwordContainer.classList.remove('hidden');
        passwordTab.className = 'flex-1 py-3 text-center font-medium text-emerald-600 border-b-2 border-emerald-600 focus:outline-none';
        otpTab.className = 'flex-1 py-3 text-center font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none';
    } else {
        passwordContainer.classList.add('hidden');
        otpContainer.classList.remove('hidden');
        otpTab.className = 'flex-1 py-3 text-center font-medium text-emerald-600 border-b-2 border-emerald-600 focus:outline-none';
        passwordTab.className = 'flex-1 py-3 text-center font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none';
    }
}

// Auto-select tab based on URL param
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('tab') === 'password') {
    switchTab('password');
}
</script>
