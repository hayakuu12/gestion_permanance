<?php

require_once '../autoload.php';
require_once '../models/Liste.php';

$listeModel = new Liste();

$annee        = $_GET['annee']        ?? null;
$trimestre    = $_GET['trimestre']    ?? null;
$service      = $_GET['service']      ?? null;
$numero_tajir = $_GET['numero_tajir'] ?? null;

$allElements = $listeModel->filtrerElements($annee, $trimestre, $service, $numero_tajir, null);

$permanences = array_filter($allElements, fn($e) => $e['type_liste'] === 'permanence');
$heures      = array_filter($allElements, fn($e) => $e['type_liste'] === 'heures_supp');

$moisNames = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'ماي',6=>'يونيو',
              7=>'يوليوز',8=>'غشت',9=>'شتنبر',10=>'أكتوبر',11=>'نونبر',12=>'دجنبر'];

/* group permanences */
$gperm = [];
foreach ($permanences as $p) {
    $key = ($p['id_liste'] ?? '') . '|' . ($p['numero_tajir'] ?? 'x');
    if (!isset($gperm[$key])) {
        $gperm[$key] = [
            'nom_complet'  => $p['nom_complet']  ?? '',
            'numero_tajir' => $p['numero_tajir']  ?? '',
            'cadre'        => $p['cadre']         ?? '',
            'service'      => $p['service']       ?? '',
            'records'      => []
        ];
    }
    $gperm[$key]['records'][] = $p;
}

/* each heures row is already a complete overtime_record */
$gheures = [];
foreach ($heures as $h) {
    $key = ($h['id_liste'] ?? '') . '|' . ($h['numero_tajir'] ?? 'x');
    if (!isset($gheures[$key])) {
        $gheures[$key] = [
            'nom_complet'  => $h['nom_complet']      ?? '',
            'numero_tajir' => $h['numero_tajir']      ?? '',
            'cadre'        => $h['cadre']             ?? '',
            'service'      => $h['service']           ?? '',
            'trimestre'    => $h['trimestre']         ?? '',
            'annee'        => $h['annee']             ?? '',
            'base_month'   => intval($h['base_month'] ?? 1),
            'h1'           => floatval($h['month_1_hours'] ?? 0),
            'h2'           => floatval($h['month_2_hours'] ?? 0),
            'h3'           => floatval($h['month_3_hours'] ?? 0),
            'total'        => floatval($h['nombre_heures'] ?? 0),
            'travaux'      => $h['travaux']           ?? '',
        ];
    }
}

?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير PDF</title>
    <style>
        body { font-family: Arial, Tahoma, sans-serif; direction: rtl; padding: 20px; font-size: 12px; }
        h1   { text-align: center; margin-bottom: 20px; }
        h2   { margin: 25px 0 10px; padding: 8px 14px; border-radius: 8px; font-size: 14px; }
        h2.perm { background: #2563eb; color: white; }
        h2.hs   { background: #16a34a; color: white; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 11px; }
        th { background: #e2e8f0; color: #0f172a; border: 1px solid #94a3b8; padding: 7px 5px; }
        td { border: 1px solid #cbd5e1; padding: 6px 5px; text-align: center; vertical-align: middle; }
        .th-blue  { background: #dbeafe !important; color: #1e40af !important; }
        .th-sub   { background: #eff6ff !important; color: #1d4ed8 !important; font-size: 10px; font-style: italic; }
        .pill     { display: inline-block; padding: 2px 7px; border-radius: 12px; font-size: 10px; margin: 1px; white-space: nowrap; }
        .p-normal  { background: #f1f5f9; color: #475569; }
        .p-weekend { background: #fef3c7; color: #92400e; }
        .p-ferie   { background: #fce7f3; color: #9d174d; }
        .h-val    { font-size: 15px; font-weight: bold; color: #1e40af; display: block; }
        .h-month  { font-size: 10px; color: #64748b; }
        .btn-print { margin-bottom: 20px; padding: 10px 18px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; }
        @media print { .btn-print { display: none; } }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">طباعة / حفظ PDF</button>

<h1>تقرير الديمومة والساعات الإضافية</h1>

<!-- ===== PERMANENCES ===== -->
<h2 class="perm">الديمومة</h2>
<table>
    <thead>
        <tr>
            <th>الاسم الكامل</th>
            <th>رقم التأجير</th>
            <th>الإطار</th>
            <th>المصلحة</th>
            <th>أيام الديمومة</th>
            <th>عطل نهاية الأسبوع</th>
            <th>أعياد وطنية</th>
            <th>مجموع</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($gperm)): ?>
        <?php foreach ($gperm as $g):
            $allDates = []; $weekendDates = []; $ferieDates = [];
            foreach ($g['records'] as $rec) {
                $dt      = $rec['date_type'] ?? '';
                $jour    = $rec['jour'] ?? '';
                $type    = $rec['type_jour'] ?? '';
                $raw     = $rec['date_debut'] ?? '';
                $dateFmt = $raw ? date('d/m/Y', strtotime($raw)) : '';
                $lbl     = $dateFmt ?: ($jour ?: $type ?: '—');
                $cat  = ($dt === 'HOLIDAY') ? 'ferie' : (($dt === 'WEEKEND' || $jour === 'السبت' || $jour === 'الأحد') ? 'weekend' : 'normal');
                $allDates[] = ['lbl' => $lbl, 'cat' => $cat];
                if ($cat === 'weekend') $weekendDates[] = $lbl;
                elseif ($cat === 'ferie') $ferieDates[] = $lbl;
            }
        ?>
        <tr>
            <td><?= htmlspecialchars($g['nom_complet']) ?></td>
            <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
            <td><?= htmlspecialchars($g['cadre']) ?></td>
            <td><?= htmlspecialchars($g['service']) ?></td>
            <td style="text-align:right;">
                <?php foreach ($allDates as $d): ?>
                    <span class="pill p-<?= $d['cat'] ?>"><?= htmlspecialchars($d['lbl']) ?></span>
                <?php endforeach; ?>
            </td>
            <td style="text-align:right;">
                <?php if (!empty($weekendDates)): foreach (array_unique($weekendDates) as $w): ?>
                    <span class="pill p-weekend"><?= htmlspecialchars($w) ?></span>
                <?php endforeach; else: ?>—<?php endif; ?>
            </td>
            <td style="text-align:right;">
                <?php if (!empty($ferieDates)): foreach (array_unique($ferieDates) as $f): ?>
                    <span class="pill p-ferie"><?= htmlspecialchars($f) ?></span>
                <?php endforeach; else: ?>—<?php endif; ?>
            </td>
            <td><strong><?= count($g['records']) ?></strong></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="8">لا توجد بيانات</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<!-- ===== HEURES SUPPLEMENTAIRES ===== -->
<h2 class="hs">الساعات الإضافية</h2>
<table>
    <thead>
        <tr>
            <th rowspan="2">الاسم الكامل</th>
            <th rowspan="2">رقم التأجير</th>
            <th rowspan="2">الإطار</th>
            <th rowspan="2">المصلحة</th>
            <th rowspan="2">الشطر</th>
            <th rowspan="2">السنة</th>
            <th colspan="3" class="th-blue">الساعات الإضافية</th>
            <th rowspan="2" class="th-blue">المجموع</th>
            <th rowspan="2">الأشغال المنجزة</th>
        </tr>
        <tr>
            <th class="th-sub">الشهر الأول</th>
            <th class="th-sub">الشهر الثاني</th>
            <th class="th-sub">الشهر الثالث</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($gheures)): ?>
        <?php foreach ($gheures as $g):
            $bm = $g['base_month'];
        ?>
        <tr>
            <td><?= htmlspecialchars($g['nom_complet']) ?></td>
            <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
            <td><?= htmlspecialchars($g['cadre']) ?></td>
            <td><?= htmlspecialchars($g['service']) ?></td>
            <td><?= htmlspecialchars($g['trimestre']) ?></td>
            <td><?= htmlspecialchars($g['annee']) ?></td>
            <td>
                <?php if ($g['h1'] > 0): ?>
                    <span class="h-val"><?= number_format($g['h1'], 1) ?></span>
                    <span class="h-month">(<?= $moisNames[$bm] ?? $bm ?>)</span>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <?php if ($g['h2'] > 0): ?>
                    <span class="h-val"><?= number_format($g['h2'], 1) ?></span>
                    <span class="h-month">(<?= $moisNames[$bm+1] ?? ($bm+1) ?>)</span>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <?php if ($g['h3'] > 0): ?>
                    <span class="h-val"><?= number_format($g['h3'], 1) ?></span>
                    <span class="h-month">(<?= $moisNames[$bm+2] ?? ($bm+2) ?>)</span>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <span class="h-val"><?= $g['total'] > 0 ? number_format($g['total'], 1) : '—' ?></span>
            </td>
            <td><?= htmlspecialchars($g['travaux']) ?></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="11">لا توجد بيانات</td></tr>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>
