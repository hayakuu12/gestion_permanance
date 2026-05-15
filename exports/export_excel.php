<?php

require_once '../autoload.php';
require_once '../models/Liste.php';

$listeModel  = new Liste();
$allElements = $listeModel->getAllElements();

$permanences = array_filter($allElements, fn($e) => $e['type_liste'] === 'permanence');
$heures      = array_filter($allElements, fn($e) => $e['type_liste'] === 'heures_supp');

/* helpers */
function xlClassify($p) {
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
function xlLabel($p) {
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

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=rapport.xls");

echo "\xEF\xBB\xBF";
?>
<table border='1'>

<!-- ===== PERMANENCES ===== -->
<tr>
  <th colspan='11' style='background:#2563eb;color:white;font-size:14px;'>الديمومة</th>
</tr>
<tr style='background:#e2e8f0;'>
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
  <th>مجموع الأيام</th>
</tr>
<?php foreach ($gperm as $g):
    $allLabels  = [];
    $weekendLbl = [];
    $ferieLbl   = [];
    $totalJours = 0;
    foreach ($g['records'] as $rec) {
        $lbl = xlLabel($rec);
        $cat = xlClassify($rec);
        $allLabels[] = $lbl;
        if ($cat === 'weekend') $weekendLbl[] = $lbl;
        elseif ($cat === 'ferie') $ferieLbl[] = $lbl;
        $totalJours += intval($rec['nombre_jours'] ?? 0);
    }
    if ($totalJours === 0) $totalJours = count($g['records']);
?>
<tr>
  <td><?= htmlspecialchars($g['nom_complet']) ?></td>
  <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
  <td><?= htmlspecialchars($g['cin']) ?></td>
  <td><?= htmlspecialchars($g['cadre']) ?></td>
  <td><?= htmlspecialchars($g['service']) ?></td>
  <td><?= htmlspecialchars($g['trimestre']) ?></td>
  <td><?= htmlspecialchars($g['annee']) ?></td>
  <td><?= htmlspecialchars(implode(' | ', $allLabels)) ?></td>
  <td><?= htmlspecialchars(!empty($weekendLbl) ? implode(' | ', $weekendLbl) : '—') ?></td>
  <td><?= htmlspecialchars(!empty($ferieLbl)   ? implode(' | ', $ferieLbl)   : '—') ?></td>
  <td><?= $totalJours ?></td>
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
    $bm = $g['base_month'];
    $h1 = $g['heures'][$bm]     ?? 0;
    $h2 = $g['heures'][$bm + 1] ?? 0;
    $h3 = $g['heures'][$bm + 2] ?? 0;
    $fmt = fn($v, $m) => $v > 0 ? $v . ' (' . ($moisNames[$m] ?? $m) . ')' : '—';
?>
<tr>
  <td><?= htmlspecialchars($g['nom_complet']) ?></td>
  <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
  <td><?= htmlspecialchars($g['cadre']) ?></td>
  <td><?= htmlspecialchars($g['service']) ?></td>
  <td><?= htmlspecialchars($g['trimestre']) ?></td>
  <td><?= htmlspecialchars($g['annee']) ?></td>
  <td><?= $fmt($h1, $bm) ?></td>
  <td><?= $fmt($h2, $bm + 1) ?></td>
  <td><?= $fmt($h3, $bm + 2) ?></td>
  <td><?= ($h1 + $h2 + $h3) > 0 ? ($h1 + $h2 + $h3) : '—' ?></td>
  <td><?= htmlspecialchars($g['travaux']) ?></td>
</tr>
<?php endforeach; ?>

</table>
