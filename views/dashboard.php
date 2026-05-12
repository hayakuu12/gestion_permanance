<?php

require_once 'autoload.php';

require_once 'models/Liste.php';
require_once 'models/Controle.php';

$listeModel = new Liste();
$controleModel = new Controle();

$totalPermanences =
$listeModel->countByType(
    "permanence"
);

$totalHeures =
$listeModel->countByType(
    "heures_supp"
);

$totalControles =
$controleModel->countControles();

$totalValides =
$listeModel->countValides();

$recentes =
$listeModel->getRecentListes();

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <div class="topbar">
        <h1>لوحة القيادة</h1>
    </div>

    <div class="cards">

        <div class="card">

            <h3>
                لوائح الديمومة
            </h3>

            <p>
                <?= $totalPermanences ?>
            </p>

        </div>

        <div class="card">

            <h3>
                لوائح الساعات الإضافية
            </h3>

            <p>
                <?= $totalHeures ?>
            </p>

        </div>

        <div class="card">

            <h3>
                الملاحظات والمخالفات
            </h3>

            <p>
                <?= $totalControles ?>
            </p>

        </div>

        <div class="card">

            <h3>
                اللوائح المصادق عليها
            </h3>

            <p>
                <?= $totalValides ?>
            </p>

        </div>

    </div>

    <div class="box">

        <h2>
            آخر اللوائح المستوردة
        </h2>

        <table>

            <thead>

                <tr>

                    <th>
                        رقم
                    </th>

                    <th>
                        النوع
                    </th>

                    <th>
                        الشطر
                    </th>

                    <th>
                        السنة
                    </th>

                    <th>
                        الملف
                    </th>

                    <th>
                        الحالة
                    </th>

                    <th>
                        التاريخ
                    </th>

                </tr>

            </thead>

            <tbody>

                <?php if(!empty($recentes)): ?>

                    <?php foreach($recentes as $liste): ?>

                        <tr>

                            <td>
                                <?= $liste['id_liste'] ?>
                            </td>

                            <td>

                                <?=
                                $liste['type_liste']
                                == 'permanence'

                                ?

                                'ديمومة'

                                :

                                'ساعات إضافية'
                                ?>

                            </td>

                            <td>
                                <?= $liste['trimestre'] ?>
                            </td>

                            <td>
                                <?= $liste['annee'] ?>
                            </td>

                            <td>
                                <?= $liste['fichier_original'] ?>
                            </td>

                            <td>
                                <?= $liste['statut'] ?>
                            </td>

                            <td>
                                <?= $liste['date_import'] ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="7">

                            لا توجد بيانات

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require 'views/layouts/footer.php'; ?>