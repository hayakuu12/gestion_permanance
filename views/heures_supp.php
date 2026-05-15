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
        "",
        $_POST['cadre'],
        $_POST['mois'],
        "",
        "",
        $_POST['nombre_heures'],
        0,
        $_POST['travaux'],
        null,
        null
    );

    $success = "تمت إضافة الساعات الإضافية يدوياً";
}

if (isset($_POST['modifier'])) {

    $listeModel->updateElement(
        $_POST['id_element'],
        $_POST['nom_complet'],
        $_POST['numero_tajir'],
        "",
        $_POST['cadre'],
        $_POST['mois'],
        "",
        "",
        0,
        $_POST['nombre_heures'],
        $_POST['travaux'],
        null,
        null
    );

    $success = "تم تعديل المعطيات بنجاح";
}

$heures = $listeModel->getElementsByType("heures_supp");

$moisNames = [
    1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'ماي',6=>'يونيو',
    7=>'يوليوز',8=>'غشت',9=>'شتنبر',10=>'أكتوبر',11=>'نونبر',12=>'دجنبر'
];

$grouped_heures = [];
foreach ($heures as $h) {
    $key = ($h['id_liste'] ?? '') . '|' . ($h['numero_tajir'] ?? 'x');
    if (!isset($grouped_heures[$key])) {
        $t  = intval($h['trimestre'] ?? 1);
        $bm = ($t - 1) * 3 + 1;
        $grouped_heures[$key] = [
            'nom_complet'  => $h['nom_complet']  ?? '',
            'numero_tajir' => $h['numero_tajir']  ?? '',
            'cadre'        => $h['cadre']         ?? '',
            'service'      => $h['service']       ?? '',
            'trimestre'    => $h['trimestre']     ?? '',
            'annee'        => $h['annee']         ?? '',
            'base_month'   => $bm,
            'heures'       => [$bm => 0, $bm + 1 => 0, $bm + 2 => 0],
            'travaux'      => '',
            'records'      => []
        ];
    }
    $bm   = $grouped_heures[$key]['base_month'];
    $mois = intval($h['mois'] ?? 0);
    if (array_key_exists($mois, $grouped_heures[$key]['heures'])) {
        $grouped_heures[$key]['heures'][$mois] += floatval($h['nombre_heures'] ?? 0);
    }
    if (!empty($h['travaux'])) $grouped_heures[$key]['travaux'] = $h['travaux'];
    $grouped_heures[$key]['records'][] = $h;
}

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

                <label class="upload-card">

                    <input type="file" name="heures_supp" accept=".pdf" required>

                    <div class="upload-content">
                        <div class="upload-icon">⬆️</div>
                        <div class="upload-title">رفع ملف PDF</div>
                        <div class="upload-desc">اضغط هنا لاختيار ملف الساعات الإضافية</div>
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

                <button type="submit" name="ajouter_manuel">
                    إضافة
                </button>

            </form>

        </div>

    <?php endif; ?>

    <div class="box">

        <h2>لائحة الساعات الإضافية</h2>

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

                    <?php if (!empty($grouped_heures)): ?>

                        <?php foreach ($grouped_heures as $g): ?>

                            <?php
                            $bm = $g['base_month'];
                            $h1 = $g['heures'][$bm]     ?? 0;
                            $h2 = $g['heures'][$bm + 1] ?? 0;
                            $h3 = $g['heures'][$bm + 2] ?? 0;
                            ?>

                            <tr>
                                <td><?= htmlspecialchars($g['nom_complet']) ?></td>
                                <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
                                <td><?= htmlspecialchars($g['cadre']) ?></td>
                                <td><?= htmlspecialchars($g['service']) ?></td>
                                <td><?= htmlspecialchars($g['trimestre']) ?></td>
                                <td><?= htmlspecialchars($g['annee']) ?></td>

                                <td class="heures-cell">
                                    <?php if ($h1 > 0): ?>
                                        <strong><?= $h1 ?></strong>
                                        <small class="month-label">(<?= $moisNames[$bm] ?? $bm ?>)</small>
                                    <?php else: ?><span class="no-date">—</span><?php endif; ?>
                                </td>
                                <td class="heures-cell">
                                    <?php if ($h2 > 0): ?>
                                        <strong><?= $h2 ?></strong>
                                        <small class="month-label">(<?= $moisNames[$bm + 1] ?? ($bm + 1) ?>)</small>
                                    <?php else: ?><span class="no-date">—</span><?php endif; ?>
                                </td>
                                <td class="heures-cell">
                                    <?php if ($h3 > 0): ?>
                                        <strong><?= $h3 ?></strong>
                                        <small class="month-label">(<?= $moisNames[$bm + 2] ?? ($bm + 2) ?>)</small>
                                    <?php else: ?><span class="no-date">—</span><?php endif; ?>
                                </td>

                                <td class="heures-cell">
                                    <?php $total = $h1 + $h2 + $h3; ?>
                                    <?php if ($total > 0): ?>
                                        <strong style="font-size:18px;color:#0f172a;"><?= $total ?></strong>
                                    <?php else: ?><span class="no-date">—</span><?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($g['travaux']) ?></td>

                                <td class="edit-btns-cell">
                                    <?php foreach ($g['records'] as $rec): ?>
                                        <button type="button" class="btn-edit btn-sm" onclick="ouvrirModification(<?= htmlspecialchars(json_encode($rec), ENT_QUOTES) ?>)">
                                            ✏ م<?= htmlspecialchars($rec['mois'] ?? '') ?>
                                        </button>
                                    <?php endforeach; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

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
                    <label>الشهر</label>
                    <input type="number" name="mois" id="m_mois" min="1" max="12">
                </div>

                <div class="input-group">
                    <label>عدد الساعات</label>
                    <input type="number" step="0.5" name="nombre_heures" id="m_nombre_heures">
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
const numeroTajirInput = document.getElementById('numero_tajir');

if (numeroTajirInput) {

    numeroTajirInput.addEventListener('keyup', function () {

        let numero = this.value.trim();

        if (numero.length < 1) return;

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

            });

    });

}

function ouvrirModification(data) {
    document.getElementById('m_id_element').value    = data.id_element    ?? '';
    document.getElementById('m_nom_complet').value   = data.nom_complet   ?? '';
    document.getElementById('m_numero_tajir').value  = data.numero_tajir  ?? '';
    document.getElementById('m_cadre').value         = data.cadre         ?? '';
    document.getElementById('m_mois').value          = data.mois          ?? '';
    document.getElementById('m_nombre_heures').value = data.nombre_heures ?? '';
    document.getElementById('m_travaux').value       = data.travaux       ?? '';

    document.getElementById('modalEdit').classList.add('active');
}

function fermerModal() {
    document.getElementById('modalEdit').classList.remove('active');
}
</script>

<?php require 'views/layouts/footer.php'; ?>
