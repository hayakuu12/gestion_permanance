<?php

require_once 'autoload.php';

require_once 'controllers/ImportController.php';
require_once 'models/Liste.php';

$success = "";
$showManualForm = false;
$manualListeId = "";

$listeModel = new Liste();

if (isset($_POST['importer'])) {

    $importController = new ImportController();

    $result = $importController->importerPDF(
        $_FILES['heures_supp'],
        "heures_supp",
        $_POST['trimestre'],
        $_POST['annee'],
        $_POST['service']
    );

    $success = $result['message'];

    if (isset($result['manual']) && $result['manual'] == true) {
        $showManualForm = true;
        $manualListeId = $result['id_liste'];
    }
}

if (isset($_POST['ajouter_manuel'])) {

    $importController = new ImportController();

    $importController->ajouterManuel(
        $_POST['id_liste'],
        "heures_supp",
        $_POST['nom_complet'],
        $_POST['numero_tajir'],
        "", $_POST['cadre'],
        $_POST['mois'],
        "", "", $_POST['nombre_heures'],
        0, $_POST['travaux'],
        null, null
    );

    $success = "تمت إضافة الساعات الإضافية يدوياً";
}

if (isset($_POST['modifier'])) {

    $listeModel->updateElement(
        $_POST['id_element'],
        $_POST['nom_complet'] ?? '',
        $_POST['numero_tajir'] ?? '',
        '', $_POST['cadre'] ?? '',
        0, '', '', 0,
        [floatval($_POST['h1'] ?? 0), floatval($_POST['h2'] ?? 0), floatval($_POST['h3'] ?? 0)],
        $_POST['travaux'] ?? '',
        null, null
    );

    $success = "تم تعديل المعطيات بنجاح";
}

/* FILTERS */
$f_annee        = trim($_GET['annee']        ?? '');
$f_trimestre    = trim($_GET['trimestre']    ?? '');
$f_service      = trim($_GET['service']      ?? '');
$f_numero_tajir = trim($_GET['numero_tajir'] ?? '');

/* Each row = one overtime_record with month_1_hours, month_2_hours, month_3_hours */
$heures = $listeModel->filtrerElements($f_annee, $f_trimestre, $f_service, $f_numero_tajir, 'heures_supp');

$moisNames = [
    1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'ماي',6=>'يونيو',
    7=>'يوليوز',8=>'غشت',9=>'شتنبر',10=>'أكتوبر',11=>'نونبر',12=>'دجنبر'
];

$grand_total_heures = array_sum(array_column($heures, 'nombre_heures'));

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <div class="topbar">
        <h1>الساعات الإضافية</h1>
    </div>

    <?php if ($success != ""): ?>
        <div class="success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="box">

        <h2>رفع ملف الساعات الإضافية PDF</h2>

        <form method="POST" enctype="multipart/form-data">

            <div class="upload-wrapper">

                <div class="upload-infos">

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

                <label class="upload-card" id="uploadCard">
                    <input type="file" name="heures_supp" accept=".pdf" required id="pdfFileInput">
                    <div class="upload-content">
                        <div class="upload-icon">📄</div>
                        <div class="upload-title" id="uploadTitle">رفع ملف PDF</div>
                        <div class="upload-desc" id="uploadDesc">اضغط هنا لاختيار ملف الساعات الإضافية</div>
                    </div>
                </label>

            </div>

            <button type="submit" name="importer" class="upload-btn">
                استيراد ملف الساعات الإضافية
            </button>

        </form>

    </div>

    <?php if ($showManualForm == true): ?>

        <div class="box">

            <h2>إدخال يدوي للساعات الإضافية</h2>

            <form method="POST">

                <input type="hidden" name="id_liste" value="<?= htmlspecialchars($manualListeId) ?>">

                <div class="row">

                    <div class="input-group">
                        <label>رقم التأجير</label>
                        <input type="text" name="numero_tajir" id="numero_tajir" required>
                        <small id="tajir_message"></small>
                    </div>

                    <div class="input-group">
                        <label>الإطار</label>
                        <input type="text" name="cadre">
                    </div>

                    <div class="input-group">
                        <label>الاسم الكامل</label>
                        <input type="text" name="nom_complet" id="nom_complet" readonly required>
                    </div>

                </div>

                <div class="row">

                    <div class="input-group">
                        <label>الشهر</label>
                        <input type="number" name="mois" min="1" max="12" required>
                    </div>

                    <div class="input-group">
                        <label>عدد الساعات</label>
                        <input type="number" step="0.5" name="nombre_heures" required>
                    </div>

                </div>

                <div class="row">
                    <div class="input-group">
                        <label>الأشغال المنجزة</label>
                        <textarea name="travaux"></textarea>
                    </div>
                </div>

                <button type="submit" name="ajouter_manuel">إضافة</button>

            </form>

        </div>

    <?php endif; ?>

    <!-- FILTER FORM -->
    <div class="box">

        <h2>البحث والتصفية</h2>

        <form method="GET">
            <input type="hidden" name="page" value="heures_supp">
            <div class="row">

                <div class="input-group">
                    <label>السنة</label>
                    <input type="number" name="annee" value="<?= htmlspecialchars($f_annee) ?>" placeholder="مثال: 2026">
                </div>

                <div class="input-group">
                    <label>الشطر</label>
                    <select name="trimestre">
                        <option value="">الكل</option>
                        <option value="1" <?= $f_trimestre=="1" ? "selected" : "" ?>>الأول (يناير–مارس)</option>
                        <option value="2" <?= $f_trimestre=="2" ? "selected" : "" ?>>الثاني (أبريل–يونيو)</option>
                        <option value="3" <?= $f_trimestre=="3" ? "selected" : "" ?>>الثالث (يوليوز–شتنبر)</option>
                        <option value="4" <?= $f_trimestre=="4" ? "selected" : "" ?>>الرابع (أكتوبر–دجنبر)</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>المصلحة</label>
                    <select name="service">
                        <option value="">الكل</option>
                        <option value="المديرية الإقليمية للعدل بمكناس" <?= $f_service=="المديرية الإقليمية للعدل بمكناس" ? "selected" : "" ?>>المديرية الإقليمية للعدل بمكناس</option>
                        <option value="كتابة الضبط بالمحكمة الابتدائية بأزرو" <?= $f_service=="كتابة الضبط بالمحكمة الابتدائية بأزرو" ? "selected" : "" ?>>كتابة الضبط بالمحكمة الابتدائية بأزرو</option>
                        <option value="كتابة الضبط بالمحكمة الابتدائية بالحاجب" <?= $f_service=="كتابة الضبط بالمحكمة الابتدائية بالحاجب" ? "selected" : "" ?>>كتابة الضبط بالمحكمة الابتدائية بالحاجب</option>
                        <option value="كتابة الضبط بالمحكمة الابتدائية بمكناس" <?= $f_service=="كتابة الضبط بالمحكمة الابتدائية بمكناس" ? "selected" : "" ?>>كتابة الضبط بالمحكمة الابتدائية بمكناس</option>
                        <option value="كتابة الضبط بمحكمة الاستئناف بمكناس" <?= $f_service=="كتابة الضبط بمحكمة الاستئناف بمكناس" ? "selected" : "" ?>>كتابة الضبط بمحكمة الاستئناف بمكناس</option>
                        <option value="كتابة النيابة العامة بالمحكمة الابتدائية بأزرو" <?= $f_service=="كتابة النيابة العامة بالمحكمة الابتدائية بأزرو" ? "selected" : "" ?>>كتابة النيابة العامة بالمحكمة الابتدائية بأزرو</option>
                        <option value="كتابة النيابة العامة بالمحكمة الابتدائية بالحاجب" <?= $f_service=="كتابة النيابة العامة بالمحكمة الابتدائية بالحاجب" ? "selected" : "" ?>>كتابة النيابة العامة بالمحكمة الابتدائية بالحاجب</option>
                        <option value="كتابة النيابة العامة بالمحكمة الابتدائية بمكناس" <?= $f_service=="كتابة النيابة العامة بالمحكمة الابتدائية بمكناس" ? "selected" : "" ?>>كتابة النيابة العامة بالمحكمة الابتدائية بمكناس</option>
                        <option value="كتابة النيابة العامة بمحكمة الاستئناف بمكناس" <?= $f_service=="كتابة النيابة العامة بمحكمة الاستئناف بمكناس" ? "selected" : "" ?>>كتابة النيابة العامة بمحكمة الاستئناف بمكناس</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>رقم التأجير</label>
                    <input type="text" name="numero_tajir" value="<?= htmlspecialchars($f_numero_tajir) ?>">
                </div>

            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit" class="btn-edit">بحث</button>
                <a href="?page=heures_supp" class="btn-delete" style="padding:12px 20px;">إعادة تعيين</a>
            </div>
        </form>

    </div>

    <!-- TABLE -->
    <div class="box">

        <h2>
            لائحة الساعات الإضافية
            <?php if (!empty($heures)): ?>
                <span style="font-size:14px;font-weight:normal;color:#64748b;margin-right:12px;">
                    (<?= count($heures) ?> موظف — المجموع الكلي:
                    <strong style="color:#1e40af;"><?= number_format($grand_total_heures, 1) ?> ساعة</strong>)
                </span>
            <?php endif; ?>
        </h2>

        <div class="table-scroll">

            <table>

                <thead>
                    <tr>
                        <th rowspan="2">الاسم الكامل</th>
                        <th rowspan="2">رقم التأجير</th>
                        <th rowspan="2">الإطار</th>
                        <th rowspan="2">المصلحة</th>
                        <th rowspan="2">الشطر</th>
                        <th rowspan="2">السنة</th>
                        <th colspan="3" class="heures-header">الساعات الإضافية</th>
                        <th rowspan="2" class="heures-header">المجموع</th>
                        <th rowspan="2">الأشغال المنجزة</th>
                        <th rowspan="2">تعديل</th>
                    </tr>
                    <tr>
                        <th class="sub-header">الشهر الأول</th>
                        <th class="sub-header">الشهر الثاني</th>
                        <th class="sub-header">الشهر الثالث</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($heures)): ?>

                        <?php foreach ($heures as $h): ?>

                            <?php
                            $bm    = intval($h['base_month'] ?? 1);
                            $h1    = floatval($h['month_1_hours'] ?? 0);
                            $h2    = floatval($h['month_2_hours'] ?? 0);
                            $h3    = floatval($h['month_3_hours'] ?? 0);
                            $total = floatval($h['nombre_heures'] ?? ($h1 + $h2 + $h3));
                            ?>

                            <tr>
                                <td><?= htmlspecialchars($h['nom_complet']) ?></td>
                                <td><?= htmlspecialchars($h['numero_tajir']) ?></td>
                                <td><?= htmlspecialchars($h['cadre']) ?></td>
                                <td><?= htmlspecialchars($h['service']) ?></td>
                                <td><?= htmlspecialchars($h['trimestre']) ?></td>
                                <td><?= htmlspecialchars($h['annee']) ?></td>

                                <td class="heures-cell">
                                    <?php if ($h1 > 0): ?>
                                        <strong><?= number_format($h1, 1) ?></strong>
                                        <small class="month-label">(<?= $moisNames[$bm] ?? $bm ?>)</small>
                                    <?php else: ?><span class="no-date">—</span><?php endif; ?>
                                </td>
                                <td class="heures-cell">
                                    <?php if ($h2 > 0): ?>
                                        <strong><?= number_format($h2, 1) ?></strong>
                                        <small class="month-label">(<?= $moisNames[$bm + 1] ?? ($bm + 1) ?>)</small>
                                    <?php else: ?><span class="no-date">—</span><?php endif; ?>
                                </td>
                                <td class="heures-cell">
                                    <?php if ($h3 > 0): ?>
                                        <strong><?= number_format($h3, 1) ?></strong>
                                        <small class="month-label">(<?= $moisNames[$bm + 2] ?? ($bm + 2) ?>)</small>
                                    <?php else: ?><span class="no-date">—</span><?php endif; ?>
                                </td>

                                <td class="heures-cell">
                                    <?php if ($total > 0): ?>
                                        <strong style="font-size:18px;color:#0f172a;"><?= number_format($total, 1) ?></strong>
                                    <?php else: ?><span class="no-date">—</span><?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($h['travaux'] ?? '') ?></td>

                                <td class="edit-btns-cell">
                                    <button type="button" class="btn-edit btn-sm"
                                        onclick="ouvrirModification(<?= htmlspecialchars(json_encode($h), ENT_QUOTES) ?>)">
                                        ✏ تعديل
                                    </button>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                        <!-- TOTALS ROW -->
                        <tr style="background:#f0f9ff;font-weight:bold;border-top:2px solid #93c5fd;">
                            <td colspan="9" style="text-align:right;color:#1e40af;">المجموع الكلي</td>
                            <td class="heures-cell">
                                <strong style="font-size:18px;color:#1e40af;"><?= number_format($grand_total_heures, 1) ?></strong>
                            </td>
                            <td colspan="2"></td>
                        </tr>

                    <?php else: ?>

                        <tr>
                            <td colspan="12">لا توجد معطيات حاليا</td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- Modal تعديل -->
<div id="modalEdit" class="modal-overlay" onclick="if(event.target===this) fermerModal()">
    <div class="modal-box">

        <div class="modal-header">
            <h3>تعديل معطيات الساعات الإضافية</h3>
            <button type="button" class="modal-close" onclick="fermerModal()">✕</button>
        </div>

        <form method="POST">

            <input type="hidden" name="id_element" id="m_id_element">

            <div class="row">

                <div class="input-group">
                    <label>رقم التأجير</label>
                    <input type="text" name="numero_tajir" id="m_numero_tajir">
                </div>

                <div class="input-group">
                    <label>الإطار</label>
                    <input type="text" name="cadre" id="m_cadre">
                </div>

                <div class="input-group">
                    <label>الاسم الكامل</label>
                    <input type="text" name="nom_complet" id="m_nom_complet">
                </div>

            </div>

            <div class="row">

                <div class="input-group">
                    <label id="lbl_h1">الشهر الأول</label>
                    <input type="number" step="0.5" min="0" name="h1" id="m_h1" value="0">
                </div>

                <div class="input-group">
                    <label id="lbl_h2">الشهر الثاني</label>
                    <input type="number" step="0.5" min="0" name="h2" id="m_h2" value="0">
                </div>

                <div class="input-group">
                    <label id="lbl_h3">الشهر الثالث</label>
                    <input type="number" step="0.5" min="0" name="h3" id="m_h3" value="0">
                </div>

            </div>

            <div class="row">
                <div class="input-group">
                    <label>الأشغال المنجزة</label>
                    <textarea name="travaux" id="m_travaux"></textarea>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" name="modifier">حفظ التعديلات</button>
                <button type="button" class="btn-delete" onclick="fermerModal()">إلغاء</button>
            </div>

        </form>

    </div>
</div>

<script>
const moisAr = ['','يناير','فبراير','مارس','أبريل','ماي','يونيو','يوليوز','غشت','شتنبر','أكتوبر','نونبر','دجنبر'];

/* File selection notification */
const pdfInput = document.getElementById('pdfFileInput');
if (pdfInput) {
    pdfInput.addEventListener('change', function () {
        const title = document.getElementById('uploadTitle');
        const desc  = document.getElementById('uploadDesc');
        const card  = document.getElementById('uploadCard');
        if (this.files && this.files[0]) {
            title.textContent = '✅ ' + this.files[0].name;
            desc.textContent  = 'تم اختيار الملف — اضغط على زر الاستيراد للمتابعة';
            card.style.borderColor = '#16a34a';
        }
    });
}

/* AJAX employee lookup */
const numeroTajirInput = document.getElementById('numero_tajir');
if (numeroTajirInput) {
    numeroTajirInput.addEventListener('keyup', function () {
        let numero = this.value.trim();
        if (numero.length < 1) return;
        fetch('ajax/get_employe.php?numero_tajir=' + encodeURIComponent(numero))
            .then(response => response.json())
            .then(data => {
                const nomInput = document.getElementById('nom_complet');
                const message  = document.getElementById('tajir_message');
                const name     = data && (data.full_name || data.nom_complet);
                if (name) {
                    nomInput.value      = name;
                    message.innerHTML   = "✅ الموظف موجود";
                    message.style.color = "green";
                } else {
                    nomInput.value      = "";
                    message.innerHTML   = "❌ رقم التأجير غير موجود";
                    message.style.color = "red";
                }
            });
    });
}

function ouvrirModification(data) {
    document.getElementById('m_id_element').value   = data.id_element   ?? '';
    document.getElementById('m_nom_complet').value  = data.nom_complet  ?? '';
    document.getElementById('m_numero_tajir').value = data.numero_tajir ?? '';
    document.getElementById('m_cadre').value        = data.cadre        ?? '';
    document.getElementById('m_h1').value           = data.month_1_hours ?? '0';
    document.getElementById('m_h2').value           = data.month_2_hours ?? '0';
    document.getElementById('m_h3').value           = data.month_3_hours ?? '0';
    document.getElementById('m_travaux').value      = data.travaux ?? '';

    /* Update month labels */
    const bm = parseInt(data.base_month ?? 1);
    document.getElementById('lbl_h1').textContent = moisAr[bm]   || ('الشهر ' + bm);
    document.getElementById('lbl_h2').textContent = moisAr[bm+1] || ('الشهر ' + (bm+1));
    document.getElementById('lbl_h3').textContent = moisAr[bm+2] || ('الشهر ' + (bm+2));

    document.getElementById('modalEdit').classList.add('active');
}

function fermerModal() {
    document.getElementById('modalEdit').classList.remove('active');
}
</script>

<?php require 'views/layouts/footer.php'; ?>
