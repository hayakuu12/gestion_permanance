<?php

require_once 'autoload.php';

require_once 'controllers/ImportController.php';
require_once 'models/Controle.php';
require_once 'models/Validation.php';
require_once 'models/Liste.php';

$success = "";
$showManualForm = false;
$manualListeId = "";
$manualType = "";

$controleModel = new Controle();
$validationModel = new Validation();
$listeModel = new Liste();

if (isset($_POST['importer'])) {

    $importController = new ImportController();

    $trimestre = $_POST['trimestre'];
    $annee = $_POST['annee'];

    $service = $_POST['service'] ?? '';

    if (isset($_FILES['permanence']) && $_FILES['permanence']['error'] == 0) {

        $result = $importController->importerPDF(
            $_FILES['permanence'],
            "permanence",
            $trimestre,
            $annee,
            $service
        );

        $success = $result['message'];

        if (isset($result['manual']) && $result['manual'] == true) {
            $showManualForm = true;
            $manualListeId = $result['id_liste'];
            $manualType = "permanence";
        }
    }

    if (isset($_FILES['heures_supp']) && $_FILES['heures_supp']['error'] == 0) {

        $result = $importController->importerPDF(
            $_FILES['heures_supp'],
            "heures_supp",
            $trimestre,
            $annee,
            $service
        );

        $success = $result['message'];

        if (isset($result['manual']) && $result['manual'] == true) {
            $showManualForm = true;
            $manualListeId = $result['id_liste'];
            $manualType = "heures_supp";
        }
    }
}

if (isset($_POST['ajouter_manuel'])) {

    $importController = new ImportController();

    $importController->ajouterManuel(
        $_POST['id_liste'],
        $_POST['type_liste'],
        $_POST['nom_complet'],
        $_POST['numero_tajir'],
        "",
        "",
        $_POST['mois'],
        "",
        "",
        $_POST['valeur'],
        0,
        "",
        $_POST['date_debut'] ?: null,
        $_POST['date_fin'] ?: null
    );

    $success = "تمت إضافة المعطيات يدوياً بنجاح";
}

if (isset($_POST['valider'])) {

    $validationModel->validerListe(
        $_POST['id_liste'],
        "تمت المصادقة"
    );

    $success = "تمت المصادقة على اللائحة";
}

if (isset($_POST['refuser'])) {

    $validationModel->refuserListe(
        $_POST['id_liste'],
        $_POST['commentaire']
    );

    $success = "تم رفض اللائحة";
}

if (isset($_POST['corriger'])) {

    $validationModel->demanderCorrection(
        $_POST['id_liste'],
        $_POST['commentaire']
    );

    $success = "تم طلب تصحيح اللائحة";
}

$controles = $controleModel->getControles();
$elements = $listeModel->getAllElements();

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <div class="topbar">
        <h1>معالجة اللوائح</h1>
    </div>

    <?php if ($success != ""): ?>
        <div class="success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="box">

        <h2>رفع ملفات PDF</h2>

        <form method="POST" enctype="multipart/form-data">

            <div class="row">

                <div class="input-group">
                    <label>ملف الديمومة PDF</label>
                    <input type="file" name="permanence" accept=".pdf">
                </div>

                <div class="input-group">
                    <label>ملف الساعات الإضافية PDF</label>
                    <input type="file" name="heures_supp" accept=".pdf">
                </div>

            </div>

            <div class="row">

                <div class="input-group">
                    <label>المصلحة</label>
                    <select name="service" required>
                        <option value="">اختيار المصلحة</option>
                        <option value="المديرية الإقليمية للعدل بمكناس">المديرية الإقليمية للعدل بمكناس</option>
                        <option value="كتابة الضبط بالمحكمة الابتدائية بأزرو">كتابة الضبط بالمحكمة الابتدائية بأزرو</option>
                        <option value="كتابة الضبط بالمحكمة الابتدائية بالحاجب">كتابة الضبط بالمحكمة الابتدائية بالحاجب</option>
                        <option value="كتابة الضبط بالمحكمة الابتدائية بمكناس">كتابة الضبط بالمحكمة الابتدائية بمكناس</option>
                        <option value="كتابة الضبط بمحكمة الاستئناف بمكناس">كتابة الضبط بمحكمة الاستئناف بمكناس</option>
                        <option value="كتابة النيابة العامة بالمحكمة الابتدائية بأزرو">كتابة النيابة العامة بالمحكمة الابتدائية بأزرو</option>
                        <option value="كتابة النيابة العامة بالمحكمة الابتدائية بالحاجب">كتابة النيابة العامة بالمحكمة الابتدائية بالحاجب</option>
                        <option value="كتابة النيابة العامة بالمحكمة الابتدائية بمكناس">كتابة النيابة العامة بالمحكمة الابتدائية بمكناس</option>
                        <option value="كتابة النيابة العامة بمحكمة الاستئناف بمكناس">كتابة النيابة العامة بمحكمة الاستئناف بمكناس</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>الشطر</label>
                    <select name="trimestre" required>
                        <option value="1">الشطر الأول</option>
                        <option value="2">الشطر الثاني</option>
                        <option value="3">الشطر الثالث</option>
                        <option value="4">الشطر الرابع</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>السنة</label>
                    <input type="number" name="annee" value="2026" required>
                </div>

            </div>

            <button type="submit" name="importer">
                استيراد الملفات
            </button>

        </form>

    </div>

    <?php if ($showManualForm == true): ?>

        <div class="box">

            <h2>إدخال يدوي للمعطيات</h2>

            <p class="note">
                تعذر استخراج المعطيات تلقائياً من ملف PDF، يرجى إدخال المعلومات يدوياً.
            </p>

            <form method="POST">

                <input type="hidden" name="id_liste" value="<?= htmlspecialchars($manualListeId) ?>">
                <input type="hidden" name="type_liste" value="<?= htmlspecialchars($manualType) ?>">

                <div class="row">

                    <div class="input-group">
                        <label>رقم التأجير</label>

                        <input
                            type="text"
                            name="numero_tajir"
                            id="numero_tajir"
                            required
                        >

                        <small id="tajir_message"></small>
                    </div>

                    <div class="input-group">
                        <label>الاسم الكامل</label>

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
                        <label>المصلحة</label>
                        <input type="text" name="service" required>
                    </div>

                    <div class="input-group">
                        <label>الشهر</label>
                        <input type="number" name="mois" min="1" max="12" required>
                    </div>

                </div>

                <div class="row">

                    <div class="input-group">
                        <label>تاريخ البداية</label>
                        <input type="date" name="date_debut">
                    </div>

                    <div class="input-group">
                        <label>تاريخ النهاية</label>
                        <input type="date" name="date_fin">
                    </div>

                </div>

                <div class="row">

                    <div class="input-group">
                        <label>عدد الأيام / الساعات</label>
                        <input type="number" name="valeur" step="0.5" required>
                    </div>

                </div>

                <button type="submit" name="ajouter_manuel">
                    إضافة المعطيات
                </button>

            </form>

        </div>

    <?php endif; ?>

    <div class="box">

        <h2>المعطيات المستوردة</h2>

        <table>

            <thead>
                <tr>
                    <th>رقم اللائحة</th>
                    <th>الاسم الكامل</th>
                    <th>رقم التأجير</th>
                    <th>المصلحة</th>
                    <th>نوع اللائحة</th>
                    <th>الشهر</th>
                    <th>تاريخ البداية</th>
                    <th>تاريخ النهاية</th>
                    <th>عدد الأيام</th>
                    <th>عدد الساعات</th>
                    <th>الشطر</th>
                    <th>السنة</th>
                    <th>الحالة</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($elements)): ?>

                    <?php foreach ($elements as $el): ?>

                        <tr>
                            <td><?= htmlspecialchars($el['id_liste'] ?? '') ?></td>
                            <td><?= htmlspecialchars($el['nom_complet'] ?? '') ?></td>
                            <td><?= htmlspecialchars($el['numero_tajir'] ?? '') ?></td>
                            <td><?= htmlspecialchars($el['service'] ?? '') ?></td>
                            <td><?= $el['type_liste'] == 'permanence' ? 'ديمومة' : 'ساعات إضافية' ?></td>
                            <td><?= htmlspecialchars($el['mois'] ?? '') ?></td>
                            <td><?= htmlspecialchars($el['date_debut'] ?? '') ?></td>
                            <td><?= htmlspecialchars($el['date_fin'] ?? '') ?></td>
                            <td><?= htmlspecialchars($el['nombre_jours'] ?? '') ?></td>
                            <td><?= htmlspecialchars($el['nombre_heures'] ?? '') ?></td>
                            <td><?= htmlspecialchars($el['trimestre'] ?? '') ?></td>
                            <td><?= htmlspecialchars($el['annee'] ?? '') ?></td>
                            <td><?= htmlspecialchars($el['statut'] ?? '') ?></td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="13">لا توجد معطيات مستوردة حاليا</td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <div class="box">

        <h2>نتائج المراقبة</h2>

        <table>

            <thead>
                <tr>
                    <th>الاسم الكامل</th>
                    <th>رقم التأجير</th>
                    <th>نوع المراقبة</th>
                    <th>الملاحظة</th>
                    <th>المستوى</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($controles)): ?>

                    <?php foreach ($controles as $controle): ?>

                        <tr>
                            <td><?= htmlspecialchars($controle['nom_complet'] ?? '') ?></td>
                            <td><?= htmlspecialchars($controle['numero_tajir'] ?? '') ?></td>
                            <td><?= htmlspecialchars($controle['type_controle'] ?? '') ?></td>
                            <td><?= htmlspecialchars($controle['message'] ?? '') ?></td>
                            <td>
                                <?php if ($controle['niveau'] == 'grave'): ?>
                                    <span class="badge danger">خطير</span>
                                <?php elseif ($controle['niveau'] == 'attention'): ?>
                                    <span class="badge warning">تنبيه</span>
                                <?php else: ?>
                                    <span class="badge info">معلومة</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5">لا توجد ملاحظات حاليا</td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <div class="box">

        <h2>المصادقة على اللائحة</h2>

        <form method="POST">

            <div class="row">

                <div class="input-group">
                    <label>رقم اللائحة</label>
                    <input type="number" name="id_liste" required>
                </div>

            </div>

            <div class="input-group">
                <label>ملاحظة المسؤول</label>
                <textarea name="commentaire" placeholder="اكتب الملاحظة هنا..."></textarea>
            </div>

            <div class="actions">

                <button type="submit" name="valider" class="btn-valid">
                    مصادقة
                </button>

                <button type="submit" name="refuser" class="btn-delete">
                    رفض
                </button>

                <button type="submit" name="corriger" class="btn-edit">
                    طلب تصحيح
                </button>

            </div>

        </form>

    </div>

</div>

<script>
const numeroTajirInput = document.getElementById('numero_tajir');

if (numeroTajirInput) {

    numeroTajirInput.addEventListener('keyup', function () {

        let numero = this.value.trim();

        if (numero.length < 1) {
            return;
        }

        fetch('ajax/get_employe.php?numero_tajir=' + encodeURIComponent(numero))
            .then(response => response.json())
            .then(data => {

                const nomInput = document.getElementById('nom_complet');
                const message = document.getElementById('tajir_message');

                if (data && data.nom_complet) {

                    nomInput.value = data.nom_complet;

                    message.innerHTML = "✅ الموظف موجود";
                    message.style.color = "green";

                } else {

                    nomInput.value = "";

                    message.innerHTML = "❌ رقم التأجير غير موجود";
                    message.style.color = "red";

                }

            })
            .catch(() => {
                const message = document.getElementById('tajir_message');
                message.innerHTML = "❌ خطأ في البحث";
                message.style.color = "red";
            });

    });

}
</script>

<?php require 'views/layouts/footer.php'; ?>