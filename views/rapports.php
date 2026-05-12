<?php

require_once 'autoload.php';

require_once 'models/Liste.php';
require_once 'models/Controle.php';
require_once 'models/Observation.php';

$listeModel = new Liste();
$controleModel = new Controle();
$observationModel = new Observation();

$elements =
    $listeModel->getAllElements();

$observations =
    $observationModel->getObservations();

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <div class="topbar">

        <h1>
            التقارير والمراقبة
        </h1>

    </div>

    <!-- EXPORTS -->

    <div class="cards">

        <div class="card">

            <h3>
                تصدير Excel
            </h3>

            <p>
                XLSX
            </p>

            <br>
            
            <a href="exports/export_excel.php" class="btn-valid">تحميل Excel</a>


        </div>

        <div class="card">

            <h3>
                تصدير PDF
            </h3>

            <p>
                PDF
            </p>

            <br>


            <a href="exports/export_pdf.php" class="btn-edit">تحميل PDF</a>

        </div>

    </div>

    <!-- ELEMENTS -->

    <div class="box">

        <h2>
            جميع المعطيات
        </h2>

        <div class="table-scroll">

            <table>

                <thead>

                    <tr>

                        <th>
                            النوع
                        </th>

                        <th>
                            الاسم الكامل
                        </th>

                        <th>
                            رقم التأجير
                        </th>

                        <th>
                            CIN
                        </th>

                        <th>
                            الإطار
                        </th>

                        <th>
                            المصلحة
                        </th>

                        <th>
                            الشهر
                        </th>

                        <th>
                            الأيام
                        </th>

                        <th>
                            الساعات
                        </th>

                        <th>
                            الأشغال
                        </th>

                        <th>
                            البداية
                        </th>

                        <th>
                            النهاية
                        </th>

                        <th>
                            الشطر
                        </th>

                        <th>
                            السنة
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (!empty($elements)): ?>

                        <?php foreach ($elements as $e): ?>

                            <tr>

                                <td>

                                    <?=
                                        $e['type_liste']
                                        ==
                                        'permanence'

                                        ?

                                        'ديمومة'

                                        :

                                        'ساعات إضافية'
                                        ?>

                                </td>

                                <td>
                                    <?= $e['nom_complet'] ?>
                                </td>

                                <td>
                                    <?= $e['numero_tajir'] ?>
                                </td>

                                <td>
                                    <?= $e['cin'] ?>
                                </td>

                                <td>
                                    <?= $e['cadre'] ?>
                                </td>

                                <td>
                                    <?= $e['service'] ?>
                                </td>

                                <td>
                                    <?= $e['mois'] ?>
                                </td>

                                <td>
                                    <?= $e['nombre_jours'] ?>
                                </td>

                                <td>
                                    <?= $e['nombre_heures'] ?>
                                </td>

                                <td>
                                    <?= $e['travaux'] ?>
                                </td>

                                <td>
                                    <?= $e['date_debut'] ?>
                                </td>

                                <td>
                                    <?= $e['date_fin'] ?>
                                </td>

                                <td>
                                    <?= $e['trimestre'] ?>
                                </td>

                                <td>
                                    <?= $e['annee'] ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="14">

                                لا توجد بيانات حاليا

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- OBSERVATIONS -->

    <div class="box">

        <h2>
            الملاحظات الإدارية
        </h2>

        <div class="table-scroll">

            <table>

                <thead>

                    <tr>

                        <th>
                            رقم اللائحة
                        </th>

                        <th>
                            النوع
                        </th>

                        <th>
                            المصلحة
                        </th>

                        <th>
                            نوع الملاحظة
                        </th>

                        <th>
                            التفاصيل
                        </th>

                        <th>
                            المستوى
                        </th>

                        <th>
                            التاريخ
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (!empty($observations)): ?>

                        <?php foreach ($observations as $o): ?>

                            <tr>

                                <td>
                                    <?= $o['id_liste'] ?>
                                </td>

                                <td>

                                    <?=
                                        $o['type_liste']
                                        ==
                                        'permanence'

                                        ?

                                        'ديمومة'

                                        :

                                        'ساعات إضافية'
                                        ?>

                                </td>

                                <td>
                                    <?= $o['service'] ?>
                                </td>

                                <td>
                                    <?= $o['type_observation'] ?>
                                </td>

                                <td>
                                    <?= $o['message'] ?>
                                </td>

                                <td>

                                    <?php if ($o['niveau'] == "grave"): ?>

                                        <span class="badge danger">
                                            خطير
                                        </span>

                                    <?php elseif ($o['niveau'] == "attention"): ?>

                                        <span class="badge warning">
                                            تنبيه
                                        </span>

                                    <?php else: ?>

                                        <span class="badge info">
                                            عادي
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= $o['date_observation'] ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="7">

                                لا توجد ملاحظات حاليا

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php require 'views/layouts/footer.php'; ?>