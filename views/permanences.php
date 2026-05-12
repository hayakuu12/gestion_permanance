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

        $_FILES['permanence'],

        "permanence",

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

        "permanence",

        $_POST['nom_complet'],

        $_POST['numero_tajir'],

        $_POST['cin'],

        $_POST['cadre'],

        $_POST['mois'],

        $_POST['nombre_jours'],

        "",

        $_POST['date_debut'],

        $_POST['date_fin']

    );

    $success =
    "تمت إضافة معطيات الديمومة يدوياً";
}

if (isset($_POST['modifier'])) {

    $listeModel->updateElement(

        $_POST['id_element'],

        $_POST['nom_complet'],

        $_POST['numero_tajir'],

        $_POST['cin'],

        $_POST['cadre'],

        $_POST['mois'],

        $_POST['nombre_jours'],

        0,

        "",

        $_POST['date_debut'],

        $_POST['date_fin']

    );

    $success =
    "تم تعديل المعطيات بنجاح";
}

$permanences =
$listeModel->getElementsByType(
    "permanence"
);

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <h1>
            الديمومة
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
            رفع ملف الديمومة PDF
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
                        name="permanence"
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
                            اضغط هنا لاختيار ملف الديمومة
                        </div>

                    </div>

                </label>

            </div>

            <button
                type="submit"
                name="importer"
                class="upload-btn"
            >
                استيراد ملف الديمومة
            </button>

        </form>

    </div>

    <!-- MANUAL -->

    <?php if ($showManualForm == true): ?>

        <div class="box">

            <h2>
                إدخال يدوي للديمومة
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

                        <small id="tajir_message"></small>

                    </div>

                    <div class="input-group">

                        <label>
                            رقم البطاقة الوطنية
                        </label>

                        <input
                            type="text"
                            name="cin"
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
                            تاريخ البداية
                        </label>

                        <input
                            type="date"
                            name="date_debut"
                        >

                    </div>

                    <div class="input-group">

                        <label>
                            تاريخ النهاية
                        </label>

                        <input
                            type="date"
                            name="date_fin"
                        >

                    </div>

                    <div class="input-group">

                        <label>
                            عدد أيام الديمومة
                        </label>

                        <input
                            type="number"
                            name="nombre_jours"
                            required
                        >

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
            لائحة الديمومة
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
                            تاريخ البداية
                        </th>

                        <th>
                            تاريخ النهاية
                        </th>

                        <th>
                            عدد الأيام
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

                    <?php if (!empty($permanences)): ?>

                        <?php foreach ($permanences as $p): ?>

                            <tr>

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="id_element"
                                        value="<?= $p['id_element'] ?>"
                                    >

                                    <td>

                                        <input
                                            type="text"
                                            name="nom_complet"
                                            value="<?= $p['nom_complet'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="text"
                                            name="numero_tajir"
                                            value="<?= $p['numero_tajir'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="text"
                                            name="cin"
                                            value="<?= $p['cin'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="text"
                                            name="cadre"
                                            value="<?= $p['cadre'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <?= $p['service'] ?>

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            name="mois"
                                            value="<?= $p['mois'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="date"
                                            name="date_debut"
                                            value="<?= $p['date_debut'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="date"
                                            name="date_fin"
                                            value="<?= $p['date_fin'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            name="nombre_jours"
                                            value="<?= $p['nombre_jours'] ?>"
                                        >

                                    </td>

                                    <td>

                                        <?= $p['trimestre'] ?>

                                    </td>

                                    <td>

                                        <?= $p['annee'] ?>

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

                            <td colspan="12">

                                لا توجد معطيات ديمومة حاليا

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

                const message =
                document.getElementById(
                    'tajir_message'
                );

                if(data && data.nom_complet){

                    nomInput.value =
                    data.nom_complet;

                    message.innerHTML =
                    "✅ الموظف موجود";

                    message.style.color =
                    "green";

                }
                else{

                    nomInput.value = "";

                    message.innerHTML =
                    "❌ رقم التأجير غير موجود";

                    message.style.color =
                    "red";

                }

            });

        }
    );

}

</script>

<?php require 'views/layouts/footer.php'; ?>