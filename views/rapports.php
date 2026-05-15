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

                    <select name="service">
                        <option value="">الكل</option>
                        <option value="المديرية الإقليمية للعدل بمكناس" <?= $service=="المديرية الإقليمية للعدل بمكناس" ? "selected" : "" ?>>المديرية الإقليمية للعدل بمكناس</option>
                        <option value="كتابة الضبط بالمحكمة الابتدائية بأزرو" <?= $service=="كتابة الضبط بالمحكمة الابتدائية بأزرو" ? "selected" : "" ?>>كتابة الضبط بالمحكمة الابتدائية بأزرو</option>
                        <option value="كتابة الضبط بالمحكمة الابتدائية بالحاجب" <?= $service=="كتابة الضبط بالمحكمة الابتدائية بالحاجب" ? "selected" : "" ?>>كتابة الضبط بالمحكمة الابتدائية بالحاجب</option>
                        <option value="كتابة الضبط بالمحكمة الابتدائية بمكناس" <?= $service=="كتابة الضبط بالمحكمة الابتدائية بمكناس" ? "selected" : "" ?>>كتابة الضبط بالمحكمة الابتدائية بمكناس</option>
                        <option value="كتابة الضبط بمحكمة الاستئناف بمكناس" <?= $service=="كتابة الضبط بمحكمة الاستئناف بمكناس" ? "selected" : "" ?>>كتابة الضبط بمحكمة الاستئناف بمكناس</option>
                        <option value="كتابة النيابة العامة بالمحكمة الابتدائية بأزرو" <?= $service=="كتابة النيابة العامة بالمحكمة الابتدائية بأزرو" ? "selected" : "" ?>>كتابة النيابة العامة بالمحكمة الابتدائية بأزرو</option>
                        <option value="كتابة النيابة العامة بالمحكمة الابتدائية بالحاجب" <?= $service=="كتابة النيابة العامة بالمحكمة الابتدائية بالحاجب" ? "selected" : "" ?>>كتابة النيابة العامة بالمحكمة الابتدائية بالحاجب</option>
                        <option value="كتابة النيابة العامة بالمحكمة الابتدائية بمكناس" <?= $service=="كتابة النيابة العامة بالمحكمة الابتدائية بمكناس" ? "selected" : "" ?>>كتابة النيابة العامة بالمحكمة الابتدائية بمكناس</option>
                        <option value="كتابة النيابة العامة بمحكمة الاستئناف بمكناس" <?= $service=="كتابة النيابة العامة بمحكمة الاستئناف بمكناس" ? "selected" : "" ?>>كتابة النيابة العامة بمحكمة الاستئناف بمكناس</option>
                    </select>

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

                                <td><?= $e['type_liste'] == 'permanence' ? 'ديمومة' : 'ساعات إضافية' ?></td>
                                <td><?= htmlspecialchars($e['nom_complet'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['numero_tajir'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['cin'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['cadre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['service'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['mois'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['nombre_jours'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['nombre_heures'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['travaux'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['date_debut'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['date_fin'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['trimestre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['annee'] ?? '') ?></td>

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

                                <td><?= htmlspecialchars($e['commentaire_validation'] ?? '') ?></td>

                                <!-- ACTIONS -->

                                <td>

                                    <div class="actions-box">

                                        <!-- ACCEPT -->

                                        <form method="POST">

                                            <input
                                                type="hidden"
                                                name="id_liste"
                                                value="<?= htmlspecialchars($e['id_liste']) ?>"
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
                                                value="<?= htmlspecialchars($e['id_liste']) ?>"
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

                                <td><?= htmlspecialchars($o['id_liste'] ?? '') ?></td>
                                <td><?= $o['type_liste'] == 'permanence' ? 'ديمومة' : 'ساعات إضافية' ?></td>
                                <td><?= htmlspecialchars($o['service'] ?? '') ?></td>
                                <td><?= htmlspecialchars($o['type_observation'] ?? '') ?></td>
                                <td><?= htmlspecialchars($o['message'] ?? '') ?></td>

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

                                <td><?= htmlspecialchars($o['date_observation'] ?? '') ?></td>

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