<?php

require_once 'autoload.php';

require_once 'controllers/ImportController.php';
require_once 'models/Liste.php';

$success = "";
$showManualForm = false;
$manualListeId = "";

$listeModel = new Liste();

if (isset($_POST['importer'])) {

    $importController =
    new ImportController();

    $result =
    $importController->importerPDF(

        $_FILES['heures_supp'],

        "heures_supp",

        $_POST['trimestre'],

        $_POST['annee'],

        $_POST['service']

    );

    $success = $result['message'];

    if (
        isset($result['manual'])
        &&
        $result['manual'] == true
    ) {

        $showManualForm = true;

        $manualListeId =
        $result['id_liste'];

    }
}

if (isset($_POST['ajouter_manuel'])) {

    $importController =
    new ImportController();

    $importController->ajouterManuel(

        $_POST['id_liste'],

        "heures_supp",

        $_POST['nom_complet'],

        $_POST['numero_tajir'],

        "",

        $_POST['cadre'],

        $_POST['mois'],

        $_POST['nombre_heures'],

        $_POST['travaux'],

        null,

        null

    );

    $success =
    "تمت إضافة الساعات الإضافية يدوياً";
}

if (isset($_POST['modifier'])) {

    $listeModel->updateElement(

        $_POST['id_element'],

        $_POST['nom_complet'],

        $_POST['numero_tajir'],

        "",

        $_POST['cadre'],

        $_POST['mois'],

        0,

        $_POST['nombre_heures'],

        $_POST['travaux'],

        null,

        null

    );

    $success =
    "تم تعديل المعطيات بنجاح";
}

$heures =
$listeModel->getElementsByType(
    "heures_supp"
);

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <h1>
            الساعات الإضافية
        </h1>

    </div>

    <!-- SUCCESS -->

    <?php if ($success != ""): ?>

        <div class="success">
            <?= $success ?>
        </div>

    <?php endif; ?>

    <!-- UPLOAD -->

    <div class="box">

        <h2>
            رفع ملف الساعات الإضافية PDF
        </h2>

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="upload-wrapper">

                <div class="upload-infos">

                    <div class="input-group">

                        <label>
                            المصلحة
                        </label>

                        <select
                            name="service"
                            required
                        >

                            <option value="">
                                اختيار المصلحة
                            </option>

                            <option value="مصلحة الموارد البشرية">
                                مصلحة الموارد البشرية
                            </option>

                            <option value="مصلحة الشؤون الإدارية">
                                مصلحة الشؤون الإدارية
                            </option>

                            <option value="مصلحة المالية">
                                مصلحة المالية
                            </option>

                            <option value="مصلحة التعمير">
                                مصلحة التعمير
                            </option>

                            <option value="مصلحة السكنى">
                                مصلحة السكنى
                            </option>

                        </select>

                    </div>

                    <div class="input-group">

                        <label>
                            الشطر
                        </label>

                        <select
                            name="trimestre"
                            required
                        >

                            <option value="1">
                                الشطر الأول
                            </option>

                            <option value="2">
                                الشطر الثاني
                            </option>

                            <option value="3">
                                الشطر الثالث
                            </option>

                            <option value="4">
                                الشطر الرابع
                            </option>

                        </select>

                    </div>

                    <div class="input-group">

                        <label>
                            السنة
                        </label>

                        <input
                            type="number"
                            name="annee"
                            value="2026"
                            required
                        >

                    </div>

                </div>

                <label class="upload-card">

                    <input
                        type="file"
                        name="heures_supp"
                        accept=".pdf"
                        required
                    >

                    <div class="upload-content">

                        <div class="upload-icon">
                            ⬆️
                        </div>

                        <div class="upload-title">
                            رفع ملف PDF
                        </div>

                        <div class="upload-desc">
                            اضغط هنا لاختيار ملف الساعات الإضافية
                        </div>

                    </div>

                </label>

            </div>

            <button
                type="submit"
                name="importer"
                class="upload-btn"
            >
                استيراد ملف الساعات الإضافية
            </button>

        </form>

    </div>

    <!-- MANUAL -->

    <?php if ($showManualForm == true): ?>

        <div class="box">

            <h2>
                إدخال يدوي للساعات الإضافية
            </h2>

            <form method="POST">

                <input
                    type="hidden"
                    name="id_liste"
                    value="<?= $manualListeId ?>"
                >

                <div class="row">

                    <div class="input-group">

                        <label>
                            رقم التأجير
                        </label>

                        <input
                            type="text"
                            name="numero_tajir"
                            id="numero_tajir"
                            required
                        >

                    </div>

                    <div class="input-group">

                        <label>
                            الإطار
                        </label>

                        <input
                            type="text"
                            name="cadre"
                        >

                    </div>

                    <div class="input-group">

                        <label>
                            الاسم الكامل
                        </label>

                        <input
                            type="text"
                            name="nom_complet"
                            id="nom_complet"
                            readonly
                            required
                        >

                    </div>

                </div>

                <div class="row">

                    <div class="input-group">

                        <label>
                            الشهر
                        </label>

                        <input
                            type="number"
                            name="mois"
                            min="1"
                            max="12"
                            required
                        >

                    </div>

                    <div class="input-group">

                        <label>
                            عدد الساعات
                        </label>

                        <input
                            type="number"
                            step="0.5"
                            name="nombre_heures"
                            required
                        >

                    </div>

                </div>

                <div class="row">

                    <div class="input-group">

                        <label>
                            الأشغال المنجزة
                        </label>

                        <textarea
                            name="travaux"
                        ></textarea>

                    </div>

                </div>

                <button
                    type="submit"
                    name="ajouter_manuel"
                >
                    إضافة
                </button>

            </form>

        </div>

    <?php endif; ?>

    <!-- TABLE -->

    <div class="box">

        <h2>
            لائحة الساعات الإضافية
        </h2>

        <div class="table-scroll">

            <table>

                <thead>

                    <tr>

                        <th>
                            الاسم الكامل
                        </th>

                        <th>
                            رقم التأجير
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
                            عدد الساعات
                        </th>

                        <th>
                            الأشغال المنجزة
                        </th>

                        <th>
                            الشطر
                        </th>

                        <th>
                            السنة
                        </th>

                        <th>
                            تعديل
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (!empty($heures)): ?>

                        <?php foreach ($heures as $h): ?>

                            <tr>

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="id_element"
                                        value="<?= $h['id_element'] ?>"
                                    >

                                    <td>

                                        <input
                                            type="text"
                                            name="nom_complet"
                                            value="<?= $h['nom_complet'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="text"
                                            name="numero_tajir"
                                            value="<?= $h['numero_tajir'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="text"
                                            name="cadre"
                                            value="<?= $h['cadre'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <?= $h['service'] ?>

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            name="mois"
                                            value="<?= $h['mois'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            step="0.5"
                                            name="nombre_heures"
                                            value="<?= $h['nombre_heures'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <textarea
                                            name="travaux"
                                        ><?= $h['travaux'] ?></textarea>

                                    </td>

                                    <td>

                                        <?= $h['trimestre'] ?>

                                    </td>

                                    <td>

                                        <?= $h['annee'] ?>

                                    </td>

                                    <td>

                                        <button
                                            type="submit"
                                            name="modifier"
                                            class="btn-edit"
                                        >
                                            حفظ
                                        </button>

                                    </td>

                                </form>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="10">

                                لا توجد معطيات حاليا

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script>

const numeroTajirInput =
document.getElementById(
    'numero_tajir'
);

if(numeroTajirInput){

    numeroTajirInput.addEventListener(
        'keyup',
        function(){

            let numero =
            this.value.trim();

            if(numero.length < 1){
                return;
            }

            fetch(
                'ajax/get_employe.php?numero_tajir='
                + encodeURIComponent(numero)
            )

            .then(response => response.json())

            .then(data => {

                const nomInput =
                document.getElementById(
                    'nom_complet'
                );

                if(data && data.nom_complet){

                    nomInput.value =
                    data.nom_complet;

                }
                else{

                    nomInput.value = "";

                }

            });

        }
    );

}

</script>

<?php require 'views/layouts/footer.php'; ?>