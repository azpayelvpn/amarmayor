<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>দৈনিক কাজের রুট-স্লিপ — ময়মনসিংহ সিটি কর্পোরেশন</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Bengali Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Hind Siliguri', 'SolaimanLipi', system-ui, sans-serif;
            color: #111;
            background: #fff;
            padding: 15px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            @page {
                size: A4 portrait;
                margin: 10mm 12mm;
            }
        }
        .sheet-header {
            border-bottom: 2px solid #006a4e;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .table-tasks th, .table-tasks td {
            border: 1px solid #333 !important;
            padding: 8px 6px;
            font-size: 0.88rem;
            vertical-align: middle;
        }
        .checkbox-box {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #333;
            margin-right: 4px;
            vertical-align: middle;
        }
        .signature-box {
            border-top: 1px dashed #333;
            width: 70%;
            margin: 30px auto 4px auto;
            text-align: center;
            font-size: 0.85rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Print Action Bar (Hidden on Print) -->
    <div class="no-print d-flex justify-content-between align-items-center bg-light p-3 rounded mb-3 border">
        <div>
            <strong>🖨️ মাঠপর্যায়ের কাজের রুট-স্লিপ প্রস্তুত:</strong> পরিচ্ছন্নতা টিম/গ্যাং-এর কর্মীদের সাথে দেওয়ার জন্য এই কাগজটি প্রিন্ট করুন। স্মার্টফোন ছাড়াই কর্মীরা এই তালিকা দেখে কাজ সম্পন্ন করবেন।
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-success fw-bold">
                🖨️ স্লিপ প্রিন্ট করুন
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary">
                বন্ধ করুন
            </button>
        </div>
    </div>

    <!-- Official Header -->
    <div class="text-center sheet-header">
        <h3 class="fw-bold mb-0 text-success" style="color: #006a4e !important;">ময়মনসিংহ সিটি কর্পোরেশন</h3>
        <h5 class="fw-bold mb-1 text-dark">দৈনিক পরিচ্ছন্নতা ও মাঠপর্যায়ের কাজের রুট-স্লিপ</h5>
        <div class="small text-muted">‘আমার ময়মনসিংহ’ নাগরিক সেবা ও অভিযোগ প্রতিকার সেল</div>
    </div>

    <!-- Meta Info Box -->
    <div class="row g-2 mb-3 small border p-2 rounded bg-light">
        <div class="col-3">
            <strong>তারিখ:</strong> <?= to_bn_number(date('d/m/Y')) ?> (<?= to_bn_number(date('l')) ?>)
        </div>
        <div class="col-3">
            <strong>ওয়ার্ড নং:</strong> <?= to_bn_number((string)($wardNumber ?? '১')) ?>
        </div>
        <div class="col-3">
            <strong>তত্ত্বাবধায়ক/সুপারভাইজার:</strong> <?= e($supervisorName ?? 'ওয়ার্ড সুপারভাইজার') ?>
        </div>
        <div class="col-3">
            <strong>মোট কাজ বরাদ্দ:</strong> <?= to_bn_number((string)count($tasks ?? [])) ?> টি
        </div>
    </div>

    <!-- Tasks Table -->
    <table class="table table-tasks mb-4">
        <thead class="table-light text-center">
            <tr>
                <th style="width: 5%;">নং</th>
                <th style="width: 14%;">ট্র্যাকিং কোড</th>
                <th style="width: 25%;">সমস্যার বিবরণ ও ধরন</th>
                <th style="width: 30%;">সুনির্দিষ্ট অবস্থান ও ঠিকানা</th>
                <th style="width: 13%;">নির্ধারিত দল / জমাদার</th>
                <th style="width: 13%;">মাঠের অবস্থা</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tasks)): ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">আজকের জন্য কোনো সক্রিয় কাজ নির্ধারিত নেই।</td>
                </tr>
            <?php else: ?>
                <?php $i = 1; foreach ($tasks as $t): ?>
                    <tr>
                        <td class="text-center fw-bold"><?= to_bn_number((string)$i++) ?></td>
                        <td class="font-monospace fw-bold text-center"><?= e($t['task_code'] ?? '') ?></td>
                        <td>
                            <strong><?= e($t['subcategory_name_bn'] ?? 'পৌর সমস্যা') ?></strong>
                            <?php if (!empty($t['description'])): ?>
                                <div class="text-muted small"><?= e(mb_strimwidth($t['description'], 0, 70, '...')) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($t['instructions'])): ?>
                                <div class="text-primary small fw-semibold">নির্দেশ: <?= e($t['instructions']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong class="text-dark"><?= e($t['landmark'] ? $t['landmark'] . ', ' : '') ?></strong>
                            <div class="text-muted small"><?= e($t['approximate_address'] ?? '') ?></div>
                        </td>
                        <td class="text-center small">
                            <?= e($t['assigned_team_name'] ?? 'ওয়ার্ড পরিচ্ছন্নতা গ্যাং') ?>
                        </td>
                        <td>
                            <div class="mb-1"><span class="checkbox-box"></span> সম্পন্ন</div>
                            <div><span class="checkbox-box"></span> বাকি</div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="small text-muted mb-4 fst-italic">
        * জমাদার/টিম লিডারের প্রতি নির্দেশ: কাজ শেষ করে দুপুরে ওয়ার্ড অফিসে রিপোর্ট জমা দিন। সুপারভাইজার যাচাই করে সিস্টেমে এন্ট্রি দেবেন।
    </div>

    <!-- Signatures -->
    <div class="row pt-3">
        <div class="col-4 text-center">
            <div class="signature-box">জমাদার / টিম লিডারের স্বাক্ষর</div>
        </div>
        <div class="col-4 text-center">
            <div class="signature-box">ওয়ার্ড সুপারভাইজারের স্বাক্ষর</div>
        </div>
        <div class="col-4 text-center">
            <div class="signature-box">ওয়ার্ড সচিব / কর্মকর্তা</div>
        </div>
    </div>

</body>
</html>
