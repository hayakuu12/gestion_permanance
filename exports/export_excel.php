<?php

require_once '../autoload.php';
require_once '../models/Liste.php';

$listeModel = new Liste();
$elements = $listeModel->getAllElements();

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=rapport.xls");

echo "\xEF\xBB\xBF";

echo "
<table border='1'>
<tr>
<th>النوع</th>
<th>الاسم الكامل</th>
<th>رقم التأجير</th>
<th>CIN</th>
<th>الإطار</th>
<th>المصلحة</th>
<th>الشهر</th>
<th>تاريخ البداية</th>
<th>تاريخ النهاية</th>
<th>عدد الأيام</th>
<th>عدد الساعات</th>
<th>الأشغال المنجزة</th>
<th>الشطر</th>
<th>السنة</th>
</tr>
";

foreach ($elements as $el) {

    echo "<tr>";

    echo "<td>" . ($el['type_liste'] == 'permanence' ? 'ديمومة' : 'ساعات إضافية') . "</td>";
    echo "<td>" . $el['nom_complet'] . "</td>";
    echo "<td>" . $el['numero_tajir'] . "</td>";
    echo "<td>" . ($el['cin'] ?? '') . "</td>";
    echo "<td>" . ($el['cadre'] ?? '') . "</td>";
    echo "<td>" . ($el['service'] ?? '') . "</td>";
    echo "<td>" . $el['mois'] . "</td>";
    echo "<td>" . ($el['date_debut'] ?? '') . "</td>";
    echo "<td>" . ($el['date_fin'] ?? '') . "</td>";
    echo "<td>" . $el['nombre_jours'] . "</td>";
    echo "<td>" . $el['nombre_heures'] . "</td>";
    echo "<td>" . ($el['travaux'] ?? '') . "</td>";
    echo "<td>" . $el['trimestre'] . "</td>";
    echo "<td>" . $el['annee'] . "</td>";

    echo "</tr>";
}

echo "</table>";