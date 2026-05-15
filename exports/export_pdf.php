<?php

require_once '../autoload.php';
require_once '../models/Liste.php';

$listeModel  = new Liste();
$allElements = $listeModel->getAllElements();

$permanences = array_filter($allElements, fn($e) => $e['type_liste'] === 'permanence');
$heures      = array_filter($allElements, fn($e) => $e['type_liste'] === 'heures_supp');

/* helpers */
function pdfClassify($p) {
    $dateDeb = $p['date_debut'] ?? '';
    $jour    = $p['jour']      ?? '';
    $type    = $p['type_jour'] ?? '';
    if (!empty($dateDeb)) {
        $n = (int) date('N', strtotime($dateDeb));
        if ($n === 6 || $n === 7) return 'weekend';
    }
    if ($jour === 'السبت' || $jour === 'الأحد') return 'weekend';
    if (mb_strpos($type, 'نهاية الأسبوع') !== false) return 'weekend';
    if (!empty($type)) return 'ferie';
    return 'normal';
}
function pdfLabel($p) {
    static $mn = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'ماي',6=>'يونيو',7=>'يوليوز',8=>'غشت',9=>'شتنبر',10=>'أكتوبر',11=>'نونبر',12=>'دجنبر'];
    if (!empty($p['date_debut'])) return date('d/m/Y', strtotime($p['date_debut']));
    $m = $mn[intval($p['mois'] ?? 0)] ?? ($p['mois'] ?? '');
    return trim(($p['jour'] ?? '') . ' ' . $m);
}

/* group permanences */
$gperm = [];
foreach ($permanences as $p) {
    $key = ($p['id_liste'] ?? '') . '|' . ($p['numero_tajir'] ?? 'x');
    if (!isset($gperm[$key])) {
        $gperm[$key] = [
            'nom_complet'  => $p['nom_complet']  ?? '',
            'numero_tajir' => $p['numero_tajir']  ?? '',
            'cin'          => $p['cin']           ?? '',
            'cadre'        => $p['cadre']         ?? '',
            'service'      => $p['service']       ?? '',
            'trimestre'    => $p['trimestre']     ?? '',
            'annee'        => $p['annee']         ?? '',
            'records'      => []
        ];
    }
    $gperm[$key]['records'][] = $p;
}

/* group heures_supp */
$moisNames = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'ماي',6=>'يونيو',7=>'يوليوز',8=>'غشت',9=>'شتنبر',10=>'أكتوبر',11=>'نونبر',12=>'دجنبر'];
$gheures = [];
foreach ($heures as $h) {
    $key = ($h['id_liste'] ?? '') . '|' . ($h['numero_tajir'] ?? 'x');
    if (!isset($gheures[$key])) {
        $t  = intval($h['trimestre'] ?? 1);
        $bm = ($t - 1) * 3 + 1;
        $gheures[$key] = [
            'nom_complet'  => $h['nom_complet']  ?? '',
            'numero_tajir' => $h['numero_tajir']  ?? '',
            'cadre'        => $h['cadre']         ?? '',
            'service'      => $h['service']       ?? '',
            'trimestre'    => $h['trimestre']     ?? '',
            'annee'        => $h['annee']         ?? '',
            'base_month'   => $bm,
            'heures'       => [$bm => 0, $bm + 1 => 0, $bm + 2 => 0],
            'travaux'      => ''
        ];
    }
    $bm   = $gheures[$key]['base_month'];
    $mois = intval($h['mois'] ?? 0);
    if (array_key_exists($mois, $gheures[$key]['heures'])) {
        $gheures[$key]['heures'][$mois] += floatval($h['nombre_heures'] ?? 0);
    }
    if (!empty($h['travaux'])) $gheures[$key]['travaux'] = $h['travaux'];
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
            <th>CIN</th>
            <th>الإطار</th>
            <th>المصلحة</th>
            <th>الشطر</th>
            <th>السنة</th>
            <th>أيام الديمومة</th>
            <th>عطل نهاية الأسبوع</th>
            <th>أعياد وطنية</th>
            <th>مجموع</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($gperm)): ?>
        <?php foreach ($gperm as $g):
            $allDates   = []; $weekendDates = []; $ferieDates = []; $total = 0;
            foreach ($g['records'] as $rec) {
                $lbl = pdfLabel($rec); $cat = pdfClassify($rec);
                $allDates[] = ['lbl'=>$lbl,'cat'=>$cat];
                if ($cat === 'weekend') $weekendDates[] = $lbl;
                elseif ($cat === 'ferie') $ferieDates[] = $lbl;
                $total += intval($rec['nombre_jours'] ?? 0);
            }
            if ($total === 0) $total = count($g['records']);
        ?>
        <tr>
            <td><?= htmlspecialchars($g['nom_complet']) ?></td>
            <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
            <td><?= htmlspecialchars($g['cin']) ?></td>
            <td><?= htmlspecialchars($g['cadre']) ?></td>
            <td><?= htmlspecialchars($g['service']) ?></td>
            <td><?= htmlspecialchars($g['trimestre']) ?></td>
            <td><?= htmlspecialchars($g['annee']) ?></td>
            <td style="text-align:right;">
                <?php foreach ($allDates as $d): ?>
                    <span class="pill p-<?= $d['cat'] ?>"><?= htmlspecialchars($d['lbl']) ?></span>
                <?php endforeach; ?>
            </td>
            <td style="text-align:right;">
                <?php if (!empty($weekendDates)): foreach ($weekendDates as $w): ?>
                    <span class="pill p-weekend"><?= htmlspecialchars($w) ?></span>
                <?php endforeach; else: ?>—<?php endif; ?>
            </td>
            <td style="text-align:right;">
                <?php if (!empty($ferieDates)): foreach ($ferieDates as $f): ?>
                    <span class="pill p-ferie"><?= htmlspecialchars($f) ?></span>
                <?php endforeach; else: ?>—<?php endif; ?>
            </td>
            <td><strong><?= $total ?></strong></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="11">لا توجد بيانات</td></tr>
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
            $h1 = $g['heures'][$bm]     ?? 0;
            $h2 = $g['heures'][$bm + 1] ?? 0;
            $h3 = $g['heures'][$bm + 2] ?? 0;
        ?>
        <tr>
            <td><?= htmlspecialchars($g['nom_complet']) ?></td>
            <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
            <td><?= htmlspecialchars($g['cadre']) ?></td>
            <td><?= htmlspecialchars($g['service']) ?></td>
            <td><?= htmlspecialchars($g['trimestre']) ?></td>
            <td><?= htmlspecialchars($g['annee']) ?></td>
            <td>
                <?php if ($h1 > 0): ?>
                    <span class="h-val"><?= $h1 ?></span>
                    <span class="h-month">(<?= $moisNames[$bm] ?? $bm ?>)</span>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <?php if ($h2 > 0): ?>
                    <span class="h-val"><?= $h2 ?></span>
                    <span class="h-month">(<?= $moisNames[$bm + 1] ?? ($bm + 1) ?>)</span>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <?php if ($h3 > 0): ?>
                    <span class="h-val"><?= $h3 ?></span>
                    <span class="h-month">(<?= $moisNames[$bm + 2] ?? ($bm + 2) ?>)</span>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <?php $tot = $h1 + $h2 + $h3; ?>
                <span class="h-val"><?= $tot > 0 ? $tot : '—' ?></span>
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
