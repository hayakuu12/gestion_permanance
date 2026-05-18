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

/* group permanences by employee+list */
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
            'nom_complet'  => $h['nom_complet']     ?? '',
            'numero_tajir' => $h['numero_tajir']     ?? '',
            'cadre'        => $h['cadre']            ?? '',
            'service'      => $h['service']          ?? '',
            'trimestre'    => $h['trimestre']        ?? '',
            'annee'        => $h['annee']            ?? '',
            'base_month'   => intval($h['base_month'] ?? 1),
            'h1'           => floatval($h['month_1_hours'] ?? 0),
            'h2'           => floatval($h['month_2_hours'] ?? 0),
            'h3'           => floatval($h['month_3_hours'] ?? 0),
            'total'        => floatval($h['nombre_heures'] ?? 0),
            'travaux'      => $h['travaux']          ?? '',
        ];
    }
}

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=rapport.xls");

echo "\xEF\xBB\xBF";
?>
<table border='1'>

<!-- ===== PERMANENCES ===== -->
<tr>
  <th colspan='9' style='background:#2563eb;color:white;font-size:14px;'>الديمومة</th>
</tr>
<tr style='background:#e2e8f0;'>
  <th>الاسم الكامل</th>
  <th>رقم التأجير</th>
  <th>الإطار</th>
  <th>المصلحة</th>
  <th>أيام الديمومة</th>
  <th>عطل نهاية الأسبوع</th>
  <th>أعياد وطنية</th>
  <th>مجموع الأيام</th>
  <th>نوع العطلة</th>
</tr>
<?php foreach ($gperm as $g):
    $allLabels = $weekendLbl = $ferieLbl = [];
    $types = [];
    foreach ($g['records'] as $rec) {
        $dt      = $rec['date_type'] ?? '';
        $jour    = $rec['jour'] ?? '';
        $type    = $rec['type_jour'] ?? '';
        $raw     = $rec['date_debut'] ?? '';
        $dateFmt = $raw ? date('d/m/Y', strtotime($raw)) : '';
        $lbl     = $dateFmt ?: ($jour ?: $type ?: '—');
        $allLabels[] = $lbl;
        $types[] = $type;
        if ($dt === 'WEEKEND' || $jour === 'السبت' || $jour === 'الأحد') $weekendLbl[] = $lbl;
        elseif ($dt === 'HOLIDAY') $ferieLbl[] = $lbl;
    }
?>
<tr>
  <td><?= htmlspecialchars($g['nom_complet']) ?></td>
  <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
  <td><?= htmlspecialchars($g['cadre']) ?></td>
  <td><?= htmlspecialchars($g['service']) ?></td>
  <td><?= htmlspecialchars(implode(' | ', $allLabels)) ?></td>
  <td><?= htmlspecialchars(!empty($weekendLbl) ? implode(' | ', $weekendLbl) : '—') ?></td>
  <td><?= htmlspecialchars(!empty($ferieLbl)   ? implode(' | ', $ferieLbl)   : '—') ?></td>
  <td><?= count($g['records']) ?></td>
  <td><?= htmlspecialchars(implode(' / ', array_unique($types))) ?></td>
</tr>
<?php endforeach; ?>

<!-- ===== HEURES SUPPLEMENTAIRES ===== -->
<tr><td colspan='11'>&nbsp;</td></tr>
<tr>
  <th colspan='11' style='background:#16a34a;color:white;font-size:14px;'>الساعات الإضافية</th>
</tr>
<tr style='background:#e2e8f0;'>
  <th>الاسم الكامل</th>
  <th>رقم التأجير</th>
  <th>الإطار</th>
  <th>المصلحة</th>
  <th>الشطر</th>
  <th>السنة</th>
  <th>الشهر الأول</th>
  <th>الشهر الثاني</th>
  <th>الشهر الثالث</th>
  <th>المجموع</th>
  <th>الأشغال المنجزة</th>
</tr>
<?php foreach ($gheures as $g):
    $bm  = $g['base_month'];
    $fmt = fn($v, $m) => $v > 0 ? number_format($v, 1) . ' (' . ($moisNames[$m] ?? $m) . ')' : '—';
?>
<tr>
  <td><?= htmlspecialchars($g['nom_complet']) ?></td>
  <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
  <td><?= htmlspecialchars($g['cadre']) ?></td>
  <td><?= htmlspecialchars($g['service']) ?></td>
  <td><?= htmlspecialchars($g['trimestre']) ?></td>
  <td><?= htmlspecialchars($g['annee']) ?></td>
  <td><?= $fmt($g['h1'], $bm) ?></td>
  <td><?= $fmt($g['h2'], $bm + 1) ?></td>
  <td><?= $fmt($g['h3'], $bm + 2) ?></td>
  <td><?= $g['total'] > 0 ? number_format($g['total'], 1) : '—' ?></td>
  <td><?= htmlspecialchars($g['travaux']) ?></td>
</tr>
<?php endforeach; ?>

</table>
