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
        $_FILES['permanence'],
        "permanence",
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

    $date_debut = $_POST['date_debut'] ?? null;
    $date_fin   = !empty($_POST['date_fin']) ? $_POST['date_fin'] : $date_debut;

    $importController->ajouterManuel(
        $_POST['id_liste'],
        "permanence",
        $_POST['nom_complet'],
        $_POST['numero_tajir'],
        $_POST['cin'] ?? '',
        $_POST['cadre'] ?? '',
        0, '',
        $_POST['type_jour'],
        intval($_POST['nombre_jours'] ?? 1),
        0, '',
        $date_debut, $date_fin
    );

    $success = "تمت إضافة معطيات الديمومة يدوياً";
}

if (isset($_POST['modifier'])) {

    $listeModel->updateElement(
        $_POST['id_element'],
        $_POST['nom_complet'] ?? '',
        $_POST['numero_tajir'] ?? '',
        '',
        $_POST['cadre'] ?? '',
        0, '',
        $_POST['type_jour'] ?? '',
        intval($_POST['nombre_jours'] ?? 0),
        0, '',
        $_POST['date_debut'] ?: null,
        $_POST['date_fin'] ?: null
    );

    $success = "تم تعديل المعطيات بنجاح";
}

$permanences = $listeModel->getElementsByType("permanence");

function classifyPermanenceDate($p) {
    $dt = $p['date_type'] ?? '';
    if ($dt === 'HOLIDAY') return 'ferie';
    if ($dt === 'WEEKEND') return 'weekend';
    $jour = $p['jour'] ?? '';
    if ($jour === 'السبت' || $jour === 'الأحد') return 'weekend';
    return 'normal';
}

$grouped_perm = [];
foreach ($permanences as $p) {
    $key = ($p['id_liste'] ?? '') . '|' . ($p['numero_tajir'] ?? 'x');
    if (!isset($grouped_perm[$key])) {
        $grouped_perm[$key] = [
            'nom_complet'  => $p['nom_complet']  ?? '',
            'numero_tajir' => $p['numero_tajir']  ?? '',
            'cin'          => $p['cin']           ?? '',
            'cadre'        => $p['cadre']         ?? '',
            'service'      => $p['service']       ?? '',
            'statut'       => $p['statut']        ?? '',
            'records'      => []
        ];
    }
    $grouped_perm[$key]['records'][] = $p;
}

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <div class="topbar">
        <h1>الديمومة</h1>
    </div>

    <?php if ($success != ""): ?>
        <div class="success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="box">

        <h2>رفع ملف الديمومة PDF</h2>

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

                    <input type="file" name="permanence" accept=".pdf" required id="pdfFileInput">

                    <div class="upload-content" id="uploadContent">
                        <div class="upload-icon">📄</div>
                        <div class="upload-title" id="uploadTitle">رفع ملف PDF</div>
                        <div class="upload-desc" id="uploadDesc">اضغط هنا لاختيار ملف الديمومة</div>
                    </div>

                </label>

            </div>

            <button type="submit" name="importer" class="upload-btn">
                استيراد ملف الديمومة
            </button>

        </form>

    </div>

    <?php if ($showManualForm == true): ?>

        <div class="box">

            <h2>إدخال يدوي للديمومة</h2>

            <form method="POST">

                <input type="hidden" name="id_liste" value="<?= htmlspecialchars($manualListeId) ?>">

                <div class="row">

                    <div class="input-group">
                        <label>رقم التأجير</label>
                        <input type="text" name="numero_tajir" id="numero_tajir" required>
                        <small id="tajir_message"></small>
                    </div>

                    <div class="input-group">
                        <label>رقم البطاقة الوطنية</label>
                        <input type="text" name="cin">
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
                        <label>تاريخ البداية</label>
                        <input type="date" name="date_debut" required>
                    </div>

                    <div class="input-group">
                        <label>تاريخ النهاية</label>
                        <input type="date" name="date_fin">
                    </div>

                    <div class="input-group">
                        <label>نوع العطلة</label>
                        <select name="type_jour" required>
                            <option value="عطلة أسبوعية">عطلة أسبوعية</option>
                            <option value="عيد وطني أو ديني">عيد وطني أو ديني</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>عدد الأيام</label>
                        <input type="number" name="nombre_jours" value="1" min="1" required>
                    </div>

                </div>

                <button type="submit" name="ajouter_manuel">
                    إضافة
                </button>

            </form>

        </div>

    <?php endif; ?>

    <div class="box">

        <h2>لائحة الديمومة</h2>

        <div class="table-scroll">

            <table>

                <thead>
                    <tr>
                        <th>الاسم الكامل</th>
                        <th>رقم التأجير</th>
                        <th>CIN</th>
                        <th>الإطار</th>
                        <th>المصلحة</th>
                        <th>أيام الديمومة</th>
                        <th>عطل نهاية الأسبوع</th>
                        <th>أعياد وطنية</th>
                        <th>مجموع الأيام</th>
                        <th>تعديل</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($grouped_perm)): ?>

                        <?php foreach ($grouped_perm as $g): ?>

                            <?php
                            $weekendDates = [];
                            $ferieDates   = [];
                            $allDates     = [];
                            $totalJours   = 0;

                            foreach ($g['records'] as $rec) {
                                $cat     = classifyPermanenceDate($rec);
                                $jour    = $rec['jour'] ?? '';
                                $type    = $rec['type_jour'] ?? '';
                                $raw     = $rec['date_debut'] ?? '';
                                $dateFmt = $raw ? date('d/m/Y', strtotime($raw)) : '';
                                $lbl     = $dateFmt ?: ($jour ?: $type ?: '—');

                                $allDates[] = ['lbl' => $lbl, 'jour' => $jour, 'type' => $type, 'cat' => $cat];

                                if ($cat === 'weekend') $weekendDates[] = $dateFmt ?: ($jour ?: 'عطلة أسبوعية');
                                elseif ($cat === 'ferie') $ferieDates[] = $dateFmt ?: ($jour ?: 'عيد');

                                $totalJours++;
                            }
                            ?>

                            <tr>
                                <td><?= htmlspecialchars($g['nom_complet']) ?></td>
                                <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
                                <td><?= htmlspecialchars($g['cin']) ?></td>
                                <td><?= htmlspecialchars($g['cadre']) ?></td>
                                <td><?= htmlspecialchars($g['service']) ?></td>

                                <td class="dates-cell">
                                    <?php foreach ($allDates as $d): ?>
                                        <span class="date-pill date-<?= $d['cat'] ?>"
                                              title="<?= htmlspecialchars(trim($d['type'] . ($d['jour'] ? ' - ' . $d['jour'] : ''), ' -')) ?>">
                                            <?= htmlspecialchars($d['lbl']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </td>

                                <td class="dates-cell">
                                    <?php if (!empty($weekendDates)): ?>
                                        <?php foreach (array_unique($weekendDates) as $wd): ?>
                                            <span class="date-pill date-weekend"><?= htmlspecialchars($wd) ?></span>
                                        <?php endforeach; ?>
                                        <small class="month-label">(<?= count($weekendDates) ?> يوم)</small>
                                    <?php else: ?>
                                        <span class="no-date">—</span>
                                    <?php endif; ?>
                                </td>

                                <td class="dates-cell">
                                    <?php if (!empty($ferieDates)): ?>
                                        <?php foreach (array_unique($ferieDates) as $fd): ?>
                                            <span class="date-pill date-ferie"><?= htmlspecialchars($fd) ?></span>
                                        <?php endforeach; ?>
                                        <small class="month-label">(<?= count($ferieDates) ?> يوم)</small>
                                    <?php else: ?>
                                        <span class="no-date">—</span>
                                    <?php endif; ?>
                                </td>

                                <td><strong><?= $totalJours ?></strong></td>

                                <td class="edit-btns-cell">
                                    <?php foreach ($g['records'] as $rec): ?>
                                        <button type="button" class="btn-edit btn-sm"
                                            onclick="ouvrirModification(<?= htmlspecialchars(json_encode($rec), ENT_QUOTES) ?>)">
                                            ✏ <?= htmlspecialchars(($rec['date_debut'] ?? '') ? date('d/m/Y', strtotime($rec['date_debut'])) : ($rec['jour'] ?? $rec['type_jour'] ?? '—')) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="10">لا توجد معطيات ديمومة حاليا</td>
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
            <h3>تعديل معطيات الديمومة</h3>
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
                    <label>نوع العطلة</label>
                    <select name="type_jour" id="m_type_jour">
                        <option value="عطلة أسبوعية">عطلة أسبوعية</option>
                        <option value="عيد وطني أو ديني">عيد وطني أو ديني</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>عدد الأيام</label>
                    <input type="number" name="nombre_jours" id="m_nombre_jours" min="1">
                </div>

            </div>

            <div class="row">

                <div class="input-group">
                    <label>تاريخ البداية</label>
                    <input type="date" name="date_debut" id="m_date_debut">
                </div>

                <div class="input-group">
                    <label>تاريخ النهاية</label>
                    <input type="date" name="date_fin" id="m_date_fin">
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
    document.getElementById('m_id_element').value    = data.id_element   ?? '';
    document.getElementById('m_nom_complet').value   = data.nom_complet  ?? '';
    document.getElementById('m_numero_tajir').value  = data.numero_tajir ?? '';
    document.getElementById('m_cadre').value         = data.cadre        ?? '';
    document.getElementById('m_nombre_jours').value  = data.nombre_jours ?? '1';
    document.getElementById('m_date_debut').value    = data.date_debut   ?? '';
    document.getElementById('m_date_fin').value      = data.date_fin     ?? '';

    const tj = document.getElementById('m_type_jour');
    const tv = data.type_jour ?? '';
    for (let i = 0; i < tj.options.length; i++) {
        tj.options[i].selected = (tj.options[i].value === tv);
    }

    document.getElementById('modalEdit').classList.add('active');
}

function fermerModal() {
    document.getElementById('modalEdit').classList.remove('active');
}
</script>

<?php require 'views/layouts/footer.php'; ?>
