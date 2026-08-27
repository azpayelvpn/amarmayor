<?php

declare(strict_types=1);

$baseUrl = 'http://amarmayor.test:8010';
$cookieFile = sys_get_temp_dir() . '/amarmayor_test_cookies.txt';
if (file_exists($cookieFile)) {
    @unlink($cookieFile);
}

function httpReq(string $url, string $method = 'GET', array $data = [], array $headers = []): array {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $headerStr = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    $loc = '';
    if (preg_match('/Location:\s*([^\r\n]+)/i', $headerStr, $m)) {
        $loc = trim($m[1]);
    }

    return [
        'code' => $httpCode,
        'headers' => $headerStr,
        'body' => $body,
        'location' => $loc,
    ];
}

echo "========================================================\n";
echo " Live Laragon Site Manual Verification Flow\n";
echo " Target: {$baseUrl}\n";
echo "========================================================\n\n";

// Step 1: GET /login to retrieve CSRF token
echo "1. GET /login ...\n";
$res1 = httpReq("{$baseUrl}/login");
echo "   Status Code: {$res1['code']}\n";
if (!preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $res1['body'], $m)) {
    echo "   [FAIL] Could not find CSRF token on /login\n";
    exit(1);
}
$csrfToken = $m[1];
echo "   Found CSRF Token: {$csrfToken}\n\n";

// Step 2: Request OTP for 01711000001
$phone = '01711000001';
echo "2. POST /auth/otp/request (phone={$phone}) ...\n";
$res2 = httpReq("{$baseUrl}/auth/otp/request", 'POST', [
    'phone' => $phone,
    '_csrf_token' => $csrfToken,
]);
echo "   Status Code: {$res2['code']}\n";
echo "   Redirect Location: {$res2['location']}\n";
if ($res2['code'] !== 302 || !str_contains($res2['location'], '/login?step=verify')) {
    echo "   [FAIL] OTP Request did not redirect to verify step!\n";
    exit(1);
}
echo "   [PASS] Successfully redirected to verify step.\n\n";

// Step 3: Read latest OTP from /dev/otp-inbox
echo "3. GET /dev/otp-inbox ...\n";
$res3 = httpReq("{$baseUrl}/dev/otp-inbox");
echo "   Status Code: {$res3['code']}\n";
// Parse OTP code from table
if (!preg_match('/<span class="badge bg-primary fs-6 px-3 py-2 font-monospace tracking-wide">\s*([0-9]{6})\s*<\/span>/', $res3['body'], $mOtp)) {
    echo "   [FAIL] Could not extract active OTP from /dev/otp-inbox!\n";
    exit(1);
}
$activeOtp = $mOtp[1];
echo "   [PASS] Found Active OTP in Inbox: {$activeOtp}\n\n";

// Step 3.5: GET the verify page to update CSRF token if needed
$resVerifyPage = httpReq("{$baseUrl}" . $res2['location']);
if (preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $resVerifyPage['body'], $mCsrf2)) {
    $csrfToken = $mCsrf2[1];
}

// Step 4: Test Invalid OTP to confirm error handling and translation
echo "4. Testing Invalid OTP '000000' ...\n";
$resWrong = httpReq("{$baseUrl}/auth/otp/verify", 'POST', [
    'phone' => $phone,
    'otp_code' => '000000',
    '_csrf_token' => $csrfToken,
]);
echo "   Status Code: {$resWrong['code']}\n";
echo "   Redirect Location: {$resWrong['location']}\n";
if (!str_contains($resWrong['location'], 'error=invalid_otp')) {
    echo "   [FAIL] Expected error=invalid_otp on wrong OTP!\n";
    exit(1);
}
$resWrongPage = httpReq("{$baseUrl}" . $resWrong['location']);
if (!str_contains($resWrongPage['body'], 'প্রদত্ত ওটিপি কোডটি সঠিক নয়')) {
    echo "   [FAIL] Bangla translation for invalid_otp not found in page!\n";
    exit(1);
}
echo "   [PASS] Invalid OTP cleanly rejected and human Bangla error displayed.\n\n";

if (preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $resWrongPage['body'], $mCsrf3)) {
    $csrfToken = $mCsrf3[1];
}

// Step 5: Submit exact correct OTP
echo "5. POST /auth/otp/verify with correct OTP '{$activeOtp}' ...\n";
$res5 = httpReq("{$baseUrl}/auth/otp/verify", 'POST', [
    'phone' => $phone,
    'otp_code' => $activeOtp,
    '_csrf_token' => $csrfToken,
]);
echo "   Status Code: {$res5['code']}\n";
echo "   Redirect Location: {$res5['location']}\n";
if ($res5['code'] !== 302 || $res5['location'] !== '/dashboard') {
    echo "   [FAIL] Expected 302 Redirect to /dashboard!\n";
    exit(1);
}
echo "   [PASS] Authenticated and redirected to /dashboard.\n\n";

// Step 6: Follow redirect to /dashboard -> /my-complaints
echo "6. GET /dashboard (with session cookie) ...\n";
$res6 = httpReq("{$baseUrl}/dashboard");
echo "   Status Code: {$res6['code']}\n";
echo "   Redirect Location: {$res6['location']}\n";
if ($res6['code'] !== 302 || $res6['location'] !== '/my-complaints') {
    echo "   [FAIL] Pure citizen dashboard should redirect to /my-complaints!\n";
    exit(1);
}

echo "7. GET /my-complaints ...\n";
$res7 = httpReq("{$baseUrl}/my-complaints");
echo "   Status Code: {$res7['code']}\n";
if ($res7['code'] !== 200 || !str_contains($res7['body'], 'অভিযোগ')) {
    echo "   [FAIL] Could not access citizen complaints page!\n";
    exit(1);
}
echo "   [PASS] Citizen portfolio loaded successfully for {$phone}!\n\n";

echo "========================================================\n";
echo " ✅ ALL LIVE LARAGON SITE VERIFICATION STEPS PASSED!\n";
echo "========================================================\n";
