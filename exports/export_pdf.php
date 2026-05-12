<?php

require_once '../autoload.php';
require_once '../models/Liste.php';

$listeModel = new Liste();
$elements = $listeModel->getAllElements();

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <title>تقرير PDF</title>

    <style>
        body{
            font-family: Arial, Tahoma, sans-serif;
            direction: rtl;
            padding: 20px;
        }

        h1{
            text-align: center;
            margin-bottom: 25px;
        }

        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }

        th{
            background: #2563eb;
            color: white;
        }

        th, td{
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .print-btn{
            margin-bottom: 20px;
            padding: 10px 18px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        @media print{
            .print-btn{
                display: none;
            }
        }
    </style>
</head>

<body>

    <button class="print-btn" onclick="window.print()">
        طباعة / حفظ PDF
    </button>

    <h1>
        تقرير الديمومة والساعات الإضافية
    </h1>

    <table>
        <thead>
            <tr>
                <th>النوع</th>
                <th>الاسم الكامل</th>
                <th>رقم التأجير</th>
                <th>CIN</th>
                <th>الإطار</th>
                <th>المصلحة</th>
                <th>الشهر</th>
                <th>الأيام</th>
                <th>الساعات</th>
                <th>الأشغال</th>
                <th>البداية</th>
                <th>النهاية</th>
                <th>الشطر</th>
                <th>السنة</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($elements)): ?>

                <?php foreach ($elements as $e): ?>

                    <tr>
                        <td>
                            <?= $e['type_liste'] == 'permanence' ? 'ديمومة' : 'ساعات إضافية' ?>
                        </td>

                        <td><?= $e['nom_complet'] ?></td>
                        <td><?= $e['numero_tajir'] ?></td>
                        <td><?= $e['cin'] ?? '' ?></td>
                        <td><?= $e['cadre'] ?? '' ?></td>
                        <td><?= $e['service'] ?? '' ?></td>
                        <td><?= $e['mois'] ?></td>
                        <td><?= $e['nombre_jours'] ?></td>
                        <td><?= $e['nombre_heures'] ?></td>
                        <td><?= $e['travaux'] ?? '' ?></td>
                        <td><?= $e['date_debut'] ?? '' ?></td>
                        <td><?= $e['date_fin'] ?? '' ?></td>
                        <td><?= $e['trimestre'] ?></td>
                        <td><?= $e['annee'] ?></td>
                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td colspan="14">لا توجد بيانات</td>
                </tr>

            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>