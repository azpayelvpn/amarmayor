<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পৌর সেবা ও অভিযোগ প্রতিবেদন — ময়মনসিংহ সিটি কর্পোরেশন</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Bengali Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Hind Siliguri', 'SolaimanLipi', system-ui, sans-serif;
            color: #1a1a1a;
            background-color: #fff;
            padding: 20px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            @page {
                size: A4;
                margin: 12mm 15mm;
            }
        }
        .header-title {
            border-bottom: 2px solid #006a4e;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            font-size: 0.85rem;
        }
        .stat-box {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px dashed #333;
            width: 80%;
            margin: 40px auto 5px auto;
            text-align: center;
            font-size: 0.85rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Action Bar (Hidden on Print) -->
    <div class="no-print d-flex justify-content-between align-items-center bg-light p-3 rounded mb-4 border">
        <div>
            <strong>🖨️ প্রিন্ট প্রিভিউ প্রস্তুত:</strong> আপনার ব্রাউজারের প্রিন্ট ডায়ালগ ব্যবহার করে সরাসরি এ৪ (A4) কাগজে প্রিন্ট করুন অথবা PDF হিসেবে সংরক্ষণ করুন।
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary fw-bold">
                🖨️ এখনই প্রিন্ট করুন
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary">
                বন্ধ করুন
            </button>
        </div>
    </div>

    <!-- Official Header -->
    <div class="text-center header-title">
        <div class="fw-bold fs-6 text-uppercase text-muted">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</div>
        <h2 class="fw-bold mb-1" style="color: #006a4e;">ময়মনসিংহ সিটি কর্পোরেশন</h2>
        <div class="fs-5 fw-bold text-dark">মেয়র ও প্রধান নির্বাহী কর্মকর্তার কার্যালয়</div>
        <div class="text-muted small">‘আমার ময়মনসিংহ’ — পৌর নাগরিক সেবা ও অভিযোগ নিষ্পত্তির সার্বিক প্রতিবেদন</div>
        <div class="mt-2 text-dark small fw-semibold">
            প্রতিবেদন সময়কাল: <span class="text-primary"><?= e($periodLabelBn) ?></span> | 
            প্রতিবেদন তৈরির তারিখ ও সময়: <?= to_bn_number(date('d/m/Y, h:i A')) ?>
        </div>
    </div>

    <!-- Summary KPI Boxes -->
    <div class="row g-2 mb-4">
        <div class="col">
            <div class="stat-box">
                <small class="text-muted d-block">মোট অভিযোগ</small>
                <div class="fs-4 fw-bold text-dark"><?= to_bn_number((string)$metrics['total_complaints']) ?></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-box">
                <small class="text-muted d-block">সমাধানকৃত</small>
                <div class="fs-4 fw-bold text-success"><?= to_bn_number((string)$metrics['resolved_count']) ?></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-box">
                <small class="text-muted d-block">মাঠে চলমান</small>
                <div class="fs-4 fw-bold text-warning"><?= to_bn_number((string)$metrics['in_progress_count']) ?></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-box">
                <small class="text-muted d-block">সময় পেরিয়ে গেছে (Overdue)</small>
                <div class="fs-4 fw-bold text-danger"><?= to_bn_number((string)$metrics['overdue_count']) ?></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-box">
                <small class="text-muted d-block">নিষ্পত্তির হার</small>
                <div class="fs-4 fw-bold text-primary"><?= to_bn_number((string)$metrics['resolution_rate']) ?>%</div>
            </div>
        </div>
        <div class="col">
            <div class="stat-box">
                <small class="text-muted d-block">নাগরিক সন্তুষ্টি</small>
                <div class="fs-4 fw-bold text-info">
                    <?= $metrics['satisfaction_rate'] !== null ? to_bn_number((string)$metrics['satisfaction_rate']) . '%' : 'তথ্য নেই' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Zonal Performance Summary -->
    <h6 class="fw-bold text-dark mb-2">১. অঞ্চলভিত্তিক সেবার সারসংক্ষেপ (অঞ্চল ০১, ০২ ও ০৩):</h6>
    <table class="table table-bordered mb-4">
        <thead class="table-light">
            <tr>
                <th>অঞ্চল</th>
                <th class="text-center">মোট প্রাপ্ত অভিযোগ</th>
                <th class="text-center">মাঠে চলমান</th>
                <th class="text-center">সমাধান সম্পন্ন</th>
                <th class="text-center">সময় পেরিয়ে গেছে</th>
                <th class="text-center">সাফল্যের শতকরা হার</th>
                <th class="text-center">গড় নিষ্পত্তির সময়</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($zoneBreakdown as $zb): ?>
                <?php
                    $zTotal = (int)$zb['total_complaints'];
                    $zResolved = (int)$zb['resolved_count'];
                    $zInProgress = (int)$zb['in_progress_count'];
                    $zOverdue = (int)$zb['overdue_count'];
                    $zRate = $zTotal > 0 ? round(($zResolved / $zTotal) * 100, 1) : 0;
                    $zAvg = round((float)($zb['avg_resolution_hours'] ?? 0), 1);
                ?>
                <tr>
                    <td class="fw-bold"><?= e($zb['name_bn']) ?></td>
                    <td class="text-center"><?= to_bn_number((string)$zTotal) ?></td>
                    <td class="text-center"><?= to_bn_number((string)$zInProgress) ?></td>
                    <td class="text-center text-success fw-bold"><?= to_bn_number((string)$zResolved) ?></td>
                    <td class="text-center text-danger"><?= to_bn_number((string)$zOverdue) ?></td>
                    <td class="text-center fw-bold"><?= to_bn_number((string)$zRate) ?>%</td>
                    <td class="text-center"><?= $zAvg > 0 ? to_bn_number((string)$zAvg) . ' ঘণ্টা' : '-' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Section 2: Department Breakdown -->
    <h6 class="fw-bold text-dark mb-2">২. পৌর সেবা ও বিভাগভিত্তিক অগ্রগতির সারসংক্ষেপ:</h6>
    <table class="table table-bordered mb-4">
        <thead class="table-light">
            <tr>
                <th>সেবা ও বিভাগ</th>
                <th class="text-center">মোট প্রাপ্ত অভিযোগ</th>
                <th class="text-center">মাঠে চলমান</th>
                <th class="text-center">সমাধান সম্পন্ন</th>
                <th class="text-center">সময় অতিক্রান্ত (বাকি)</th>
                <th class="text-center">নিষ্পত্তির শতকরা হার</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categoryBreakdown as $cat): ?>
                <?php
                    $cTotal = (int)$cat['total'];
                    $cResolved = (int)$cat['resolved'];
                    $cInProgress = (int)($cat['in_progress'] ?? 0);
                    $cOverdue = (int)$cat['overdue'];
                    $cRate = $cTotal > 0 ? round(($cResolved / $cTotal) * 100, 1) : 0;
                ?>
                <tr>
                    <td class="fw-bold"><?= e($cat['name_bn']) ?></td>
                    <td class="text-center"><?= to_bn_number((string)$cTotal) ?></td>
                    <td class="text-center"><?= to_bn_number((string)$cInProgress) ?></td>
                    <td class="text-center text-success"><?= to_bn_number((string)$cResolved) ?></td>
                    <td class="text-center text-danger"><?= to_bn_number((string)$cOverdue) ?></td>
                    <td class="text-center fw-bold"><?= to_bn_number((string)$cRate) ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Section 3: 33 Ward Supervisors Accountability Scorecard -->
    <div style="page-break-before: auto;">
        <h6 class="fw-bold text-dark mb-2">৩. ৩৩ জন ওয়ার্ড সুপারভাইজারের জবাবদিহিতা ও পারফরম্যান্স স্কোরকার্ড:</h6>
        <table class="table table-bordered mb-4">
            <thead class="table-light">
                <tr>
                    <th>ওয়ার্ড ও অঞ্চল</th>
                    <th>সুপারভাইজারের নাম</th>
                    <th class="text-center">মোবাইল নম্বর</th>
                    <th class="text-center">মোট কাজ</th>
                    <th class="text-center">চলমান</th>
                    <th class="text-center">সমাধান</th>
                    <th class="text-center">সময় অতিক্রান্ত</th>
                    <th class="text-center">সাফল্যের হার</th>
                    <th class="text-center">মূল্যায়ন</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($supervisorBreakdown as $sb): ?>
                    <?php
                        $sTotal = (int)$sb['total_complaints'];
                        $sResolved = (int)$sb['resolved_count'];
                        $sInProgress = (int)$sb['in_progress_count'];
                        $sOverdue = (int)$sb['overdue_count'];
                        $sRate = $sTotal > 0 ? round(($sResolved / $sTotal) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td>ওয়ার্ড নং <?= to_bn_number((string)$sb['ward_number']) ?> (<?= e($sb['zone_name_bn']) ?>)</td>
                        <td class="fw-semibold"><?= e($sb['supervisor_name_bn']) ?></td>
                        <td class="text-center font-monospace"><?= to_bn_number((string)$sb['official_phone']) ?></td>
                        <td class="text-center"><?= to_bn_number((string)$sTotal) ?></td>
                        <td class="text-center"><?= to_bn_number((string)$sInProgress) ?></td>
                        <td class="text-center text-success fw-bold"><?= to_bn_number((string)$sResolved) ?></td>
                        <td class="text-center <?= $sOverdue > 0 ? 'text-danger fw-bold' : '' ?>"><?= to_bn_number((string)$sOverdue) ?></td>
                        <td class="text-center fw-bold"><?= to_bn_number((string)$sRate) ?>%</td>
                        <td class="text-center">
                            <?= $sOverdue > 5 ? 'জরুরি নজর' : ($sOverdue > 0 ? 'সতর্কবার্তা' : 'সন্তোষজনক') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Official Signatures Block -->
    <div class="row mt-5 pt-4" style="page-break-inside: avoid;">
        <div class="col-4 text-center">
            <div class="signature-line">প্রতিবেদন প্রস্তুতকারক</div>
            <small class="text-muted">আইটি ও মনিটরিং সেল<br>ময়মনসিংহ সিটি কর্পোরেশন</small>
        </div>
        <div class="col-4 text-center">
            <div class="signature-line">সচিব / প্রধান নির্বাহী কর্মকর্তা</div>
            <small class="text-muted">ময়মনসিংহ সিটি কর্পোরেশন</small>
        </div>
        <div class="col-4 text-center">
            <div class="signature-line">মাননীয় মেয়র / প্রশাসক</div>
            <small class="text-muted">ময়মনসিংহ সিটি কর্পোরেশন</small>
        </div>
    </div>

</body>
</html>
