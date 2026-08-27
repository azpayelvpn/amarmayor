<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

use AmarMayor\Auth\Auth;
use AmarMayor\Auth\User;
use AmarMayor\Controllers\Web\CivicDirectoryWebController;
use AmarMayor\Http\Request;

$controller = new CivicDirectoryWebController();

echo "==================================================\n";
echo "MANUAL HTTP VERIFICATION: WHO IS RESPONSIBLE & MY AREA\n";
echo "==================================================\n\n";

// 1. Who Is Responsible Page
$resp1 = $controller->whoIsResponsible(new Request('GET', '/who-is-responsible'));
echo "[1] /who-is-responsible (Status: " . $resp1->getStatusCode() . ")\n";
$c1 = $resp1->getContent();

$checks1 = [
    'Document Date (১৮.০৩.২০২৬)' => '১৮.০৩.২০২৬',
    'Ward 1 Officer (নাজিয়া উদ্দিন)' => 'নাজিয়া উদ্দিন',
    'Ward 1 Mobile (01723-089233)' => '01723-089233',
    'Ward 19 Officer (এস এম ইকবাল)' => 'এস এম ইকবাল',
    'Ward 19 Operational (জসিম উদ্দিন)' => 'জসিম উদ্দিন',
    'Ward 33 Officer (মোহাম্মদ মহসিন মিয়া)' => 'মোহাম্মদ মহসিন মিয়া',
    'Ward 33 Operational (মুহাম্মদ আযহারুল হক)' => 'মুহাম্মদ আযহারুল হক',
    'Leave Substitute Header (ছুটিকালীন প্রতিস্থাপক)' => 'ছুটিকালীন প্রতিস্থাপক',
];

foreach ($checks1 as $label => $needle) {
    $ok = str_contains($c1, $needle);
    echo "  " . ($ok ? "[PASS]" : "[FAIL]") . " {$label}\n";
}

$hasFake1 = str_contains($c1, '+8809166666');
echo "  " . (!$hasFake1 ? "[PASS]" : "[FAIL]") . " No placeholder '+8809166666' in officer contacts\n\n";

// 2. Personalized My Area Page (Citizen 01711000001 with Home Ward 1)
$citizen = User::findByPhone('01711000001');
Auth::login($citizen);
$resp2 = $controller->wards(new Request('GET', '/my-area'));
echo "[2] /my-area for Citizen 01711000001 (Status: " . $resp2->getStatusCode() . ")\n";
$c2 = $resp2->getContent();

$checks2 = [
    'Personalized Hero Card (আমার নির্ধারিত ওয়ার্ড)' => 'আমার নির্ধারিত ওয়ার্ড',
    'Ward 1 Heading (ওয়ার্ড নং ১)' => 'ওয়ার্ড নং ১',
    'Ward 1 Governance Officer (নাজিয়া উদ্দিন)' => 'নাজিয়া উদ্দিন',
    'Ward 1 Contact (01723-089233)' => '01723-089233',
    'Ward 1 Representation Label (জনপ্রতিনিধিত্ব / শাসনভার)' => 'জনপ্রতিনিধিত্ব / শাসনভার',
];

foreach ($checks2 as $label => $needle) {
    $ok = str_contains($c2, $needle);
    echo "  " . ($ok ? "[PASS]" : "[FAIL]") . " {$label}\n";
}

$hasFake2 = str_contains($c2, '+8809166666');
echo "  " . (!$hasFake2 ? "[PASS]" : "[FAIL]") . " No placeholder '+8809166666' in My Area\n";

Auth::logout();
echo "\n==================================================\n";
