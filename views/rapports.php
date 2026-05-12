<?php

require_once 'autoload.php';

require_once 'models/Liste.php';
require_once 'models/Controle.php';
require_once 'models/Observation.php';

$listeModel = new Liste();
$controleModel = new Controle();
$observationModel = new Observation();

/* VALIDATION */

if(isset($_POST['valider'])){

    $listeModel->changerStatut(

        $_POST['id_liste'],

        'تمت المصادقة',

        null

    );
}

if(isset($_POST['refuser'])){

    $listeModel->changerStatut(

        $_POST['id_liste'],

        'مرفوضة',

        $_POST['commentaire']

    );
}

/* FILTERS */

$annee =
$_GET['annee'] ?? '';

$trimestre =
$_GET['trimestre'] ?? '';

$service =
$_GET['service'] ?? '';

$numero_tajir =
$_GET['numero_tajir'] ?? '';

/* DATA */

$elements =
$listeModel->filtrerElements(

    $annee,
    $trimestre,
    $service,
    $numero_tajir

);

$observations =
$observationModel->getObservations();

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <!-- TOPBAR -->

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
                XLS
            </p>

            <br>

            <a
                href="exports/export_excel.php"
                class="btn-valid"
            >
                تحميل Excel
            </a>

        </div>

        <div class="card">

            <h3>
                تصدير PDF
            </h3>

            <p>
                PDF
            </p>

            <br>

            <a
                href="exports/export_pdf.php"
                class="btn-edit"
            >
                تحميل PDF
            </a>

        </div>

    </div>

    <!-- FILTERS -->

    <div class="box">

        <h2>
            البحث والتصفية
        </h2>

        <form method="GET">

            <input
                type="hidden"
                name="page"
                value="rapports"
            >

            <div class="row">

                <!-- YEAR -->

                <div class="input-group">

                    <label>
                        السنة
                    </label>

                    <input
                        type="number"
                        name="annee"
                        value="<?= $annee ?>"
                    >

                </div>

                <!-- TRIMESTRE -->

                <div class="input-group">

                    <label>
                        الشطر
                    </label>

                    <select name="trimestre">

                        <option value="">
                            الكل
                        </option>

                        <option
                            value="1"
                            <?= $trimestre=="1" ? "selected" : "" ?>
                        >
                            الأول
                        </option>

                        <option
                            value="2"
                            <?= $trimestre=="2" ? "selected" : "" ?>
                        >
                            الثاني
                        </option>

                        <option
                            value="3"
                            <?= $trimestre=="3" ? "selected" : "" ?>
                        >
                            الثالث
                        </option>

                        <option
                            value="4"
                            <?= $trimestre=="4" ? "selected" : "" ?>
                        >
                            الرابع
                        </option>

                    </select>

                </div>

                <!-- SERVICE -->

                <div class="input-group">

                    <label>
                        المصلحة
                    </label>

                    <input
                        type="text"
                        name="service"
                        value="<?= $service ?>"
                    >

                </div>

                <!-- TAJIR -->

                <div class="input-group">

                    <label>
                        رقم التأجير
                    </label>

                    <input
                        type="text"
                        name="numero_tajir"
                        value="<?= $numero_tajir ?>"
                    >

                </div>

            </div>

            <button
                type="submit"
                class="btn-edit"
            >
                بحث
            </button>

        </form>

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
                            عدد الأيام
                        </th>

                        <th>
                            عدد الساعات
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

                        <th>
                            الحالة
                        </th>

                        <th>
                            تعليق
                        </th>

                        <th>
                            الإجراء
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if(!empty($elements)): ?>

                        <?php foreach($elements as $e): ?>

                            <tr>

                                <!-- TYPE -->

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

                                <!-- NOM -->

                                <td>
                                    <?= $e['nom_complet'] ?>
                                </td>

                                <!-- TAJIR -->

                                <td>
                                    <?= $e['numero_tajir'] ?>
                                </td>

                                <!-- CIN -->

                                <td>
                                    <?= $e['cin'] ?>
                                </td>

                                <!-- CADRE -->

                                <td>
                                    <?= $e['cadre'] ?>
                                </td>

                                <!-- SERVICE -->

                                <td>
                                    <?= $e['service'] ?>
                                </td>

                                <!-- MOIS -->

                                <td>
                                    <?= $e['mois'] ?>
                                </td>

                                <!-- DAYS -->

                                <td>
                                    <?= $e['nombre_jours'] ?>
                                </td>

                                <!-- HOURS -->

                                <td>
                                    <?= $e['nombre_heures'] ?>
                                </td>

                                <!-- TRAVAUX -->

                                <td>
                                    <?= $e['travaux'] ?>
                                </td>

                                <!-- DATE D -->

                                <td>
                                    <?= $e['date_debut'] ?>
                                </td>

                                <!-- DATE F -->

                                <td>
                                    <?= $e['date_fin'] ?>
                                </td>

                                <!-- TRIM -->

                                <td>
                                    <?= $e['trimestre'] ?>
                                </td>

                                <!-- YEAR -->

                                <td>
                                    <?= $e['annee'] ?>
                                </td>

                                <!-- STATUS -->

                                <td>

                                    <?php if($e['statut'] == 'تمت المصادقة'): ?>

                                        <span class="badge info">
                                            تمت المصادقة
                                        </span>

                                    <?php elseif($e['statut'] == 'مرفوضة'): ?>

                                        <span class="badge danger">
                                            مرفوضة
                                        </span>

                                    <?php else: ?>

                                        <span class="badge warning">
                                            في الانتظار
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- COMMENT -->

                                <td>

                                    <?= $e['commentaire_validation'] ?>

                                </td>

                                <!-- ACTIONS -->

                                <td>

                                    <div class="actions-box">

                                        <!-- ACCEPT -->

                                        <form method="POST">

                                            <input
                                                type="hidden"
                                                name="id_liste"
                                                value="<?= $e['id_liste'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="valider"
                                                class="btn-valid"
                                            >
                                                قبول
                                            </button>

                                        </form>

                                        <!-- REFUSE -->

                                        <form method="POST">

                                            <input
                                                type="hidden"
                                                name="id_liste"
                                                value="<?= $e['id_liste'] ?>"
                                            >

                                            <input
                                                type="text"
                                                name="commentaire"
                                                class="comment-input"
                                                placeholder="سبب الرفض"
                                                required
                                            >

                                            <button
                                                type="submit"
                                                name="refuser"
                                                class="btn-delete"
                                            >
                                                رفض
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="17">

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

                    <?php if(!empty($observations)): ?>

                        <?php foreach($observations as $o): ?>

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