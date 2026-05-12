<?php

require_once 'autoload.php';

require_once 'models/Liste.php';
require_once 'models/Observation.php';

$listeModel = new Liste();
$observationModel = new Observation();

/* COUNTS */

$totalPermanences =
$listeModel->countPermanences();

$totalHeuresSupp =
$listeModel->countHeuresSupp();

$totalRefusees =
$listeModel->countRefusees();

$totalObservations =
$observationModel->countObservations();

/* RECENT */

$recentListes =
$listeModel->getRecentListes();

$recentObservations =
$observationModel->getObservations();

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <h1>
            لوحة التحكم
        </h1>

    </div>

    <!-- STATS -->

    <div class="cards">

        <!-- Permanences -->

        <div class="card">

            <h3>
                لوائح الديمومة
            </h3>

            <p>
                <?= $totalPermanences ?>
            </p>

        </div>

        <!-- HS -->

        <div class="card">

            <h3>
                الساعات الإضافية
            </h3>

            <p>
                <?= $totalHeuresSupp ?>
            </p>

        </div>

        <!-- Refused -->

        <div class="card">

            <h3>
                اللوائح المرفوضة
            </h3>

            <p>
                <?= $totalRefusees ?>
            </p>

        </div>

        <!-- Obs -->

        <div class="card">

            <h3>
                الملاحظات الإدارية
            </h3>

            <p>
                <?= $totalObservations ?>
            </p>

        </div>

    </div>

    <!-- RECENT LISTES -->

    <div class="box">

        <h2>
            آخر اللوائح
        </h2>

        <div class="table-scroll">

            <table>

                <thead>

                    <tr>

                        <th>
                            النوع
                        </th>

                        <th>
                            المصلحة
                        </th>

                        <th>
                            الشطر
                        </th>

                        <th>
                            السنة
                        </th>

                        <th>
                            الحالة
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if(!empty($recentListes)): ?>

                        <?php foreach($recentListes as $l): ?>

                            <tr>

                                <td>

                                    <?=
                                    $l['type_liste']
                                    ==
                                    'permanence'

                                    ?

                                    'ديمومة'

                                    :

                                    'ساعات إضافية'
                                    ?>

                                </td>

                                <td>
                                    <?= $l['service'] ?>
                                </td>

                                <td>
                                    <?= $l['trimestre'] ?>
                                </td>

                                <td>
                                    <?= $l['annee'] ?>
                                </td>

                                <td>

                                    <?php if($l['statut'] == 'تمت المصادقة'): ?>

                                        <span class="badge info">
                                            تمت المصادقة
                                        </span>

                                    <?php elseif($l['statut'] == 'مرفوضة'): ?>

                                        <span class="badge danger">
                                            مرفوضة
                                        </span>

                                    <?php else: ?>

                                        <span class="badge warning">
                                            في الانتظار
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="5">

                                لا توجد لوائح حاليا

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
            آخر الملاحظات
        </h2>

        <div class="table-scroll">

            <table>

                <thead>

                    <tr>

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

                    <?php if(!empty($recentObservations)): ?>

                        <?php foreach($recentObservations as $o): ?>

                            <tr>

                                <td>
                                    <?= $o['type_observation'] ?>
                                </td>

                                <td>
                                    <?= $o['message'] ?>
                                </td>

                                <td>

                                    <?php if($o['niveau']=="grave"): ?>

                                        <span class="badge danger">
                                            خطير
                                        </span>

                                    <?php elseif($o['niveau']=="attention"): ?>

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

                            <td colspan="4">

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