<?php

require_once 'autoload.php';

require_once 'models/Liste.php';
require_once 'models/Observation.php';

$listeModel = new Liste();
$observationModel = new Observation();

$totalPermanences  = $listeModel->countPermanences();
$totalHeuresSupp   = $listeModel->countHeuresSupp();
$totalRefusees     = $listeModel->countRefusees();
$totalObservations = $observationModel->countObservations();

$recentListes       = $listeModel->getRecentListes();
$recentObservations = $observationModel->getObservations();

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <div class="topbar">
        <h1>لوحة التحكم</h1>
    </div>

    <div class="cards">

        <div class="card">
            <h3>لوائح الديمومة</h3>
            <p><?= intval($totalPermanences) ?></p>
        </div>

        <div class="card">
            <h3>الساعات الإضافية</h3>
            <p><?= intval($totalHeuresSupp) ?></p>
        </div>

        <div class="card">
            <h3>اللوائح المرفوضة</h3>
            <p><?= intval($totalRefusees) ?></p>
        </div>

        <div class="card">
            <h3>الملاحظات الإدارية</h3>
            <p><?= intval($totalObservations) ?></p>
        </div>

    </div>

    <div class="box">

        <h2>آخر اللوائح</h2>

        <div class="table-scroll">

            <table>

                <thead>
                    <tr>
                        <th>النوع</th>
                        <th>المصلحة</th>
                        <th>الشطر</th>
                        <th>السنة</th>
                        <th>الحالة</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($recentListes)): ?>

                        <?php foreach ($recentListes as $l): ?>
                            <?php
                            $typeLabel = $l['type_liste'] == 'permanence' ? 'ديمومة' : 'ساعات إضافية';
                            $rowTitle  = htmlspecialchars($typeLabel . ' - ' . ($l['service'] ?? '') . ' - الشطر ' . ($l['trimestre'] ?? '') . ' - ' . ($l['annee'] ?? ''), ENT_QUOTES);
                            ?>
                            <tr class="liste-row" style="cursor:pointer;"
                                onclick="ouvrirListe('<?= htmlspecialchars($l['id'], ENT_QUOTES) ?>', '<?= $rowTitle ?>')"
                                title="انقر لعرض أعضاء هذه اللائحة">
                                <td><?= $typeLabel ?></td>
                                <td><?= htmlspecialchars($l['service'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['trimestre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['annee'] ?? '') ?></td>
                                <td>
                                    <?php $st = $l['statut'] ?? $l['status'] ?? ''; ?>
                                    <?php if ($st === 'تمت المصادقة' || $st === 'VALID'): ?>
                                        <span class="badge info">تمت المصادقة</span>
                                    <?php elseif ($st === 'مرفوضة' || $st === 'INVALID'): ?>
                                        <span class="badge danger">مرفوضة</span>
                                    <?php else: ?>
                                        <span class="badge warning">في الانتظار</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5">لا توجد لوائح حاليا</td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <div class="box">

        <h2>آخر الملاحظات</h2>

        <div class="table-scroll">

            <table>

                <thead>
                    <tr>
                        <th>نوع الملاحظة</th>
                        <th>التفاصيل</th>
                        <th>المستوى</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($recentObservations)): ?>

                        <?php foreach ($recentObservations as $o): ?>

                            <tr>
                                <td><?= htmlspecialchars($o['type_observation'] ?? '') ?></td>
                                <td><?= htmlspecialchars($o['message'] ?? '') ?></td>
                                <td>
                                    <?php if ($o['niveau'] == 'grave'): ?>
                                        <span class="badge danger">خطير</span>
                                    <?php elseif ($o['niveau'] == 'attention'): ?>
                                        <span class="badge warning">تنبيه</span>
                                    <?php else: ?>
                                        <span class="badge info">عادي</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($o['date_observation'] ?? $o['created_at'] ?? '') ?></td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4">لا توجد ملاحظات حاليا</td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- Modal: أعضاء اللائحة -->
<div id="modalListe" class="modal-overlay" onclick="if(event.target===this)fermerModalListe()">
    <div class="modal-box" style="max-width:960px;">

        <div class="modal-header">
            <h3 id="modalListeTitle">أعضاء اللائحة</h3>
            <button type="button" class="modal-close" onclick="fermerModalListe()">✕</button>
        </div>

        <div style="padding:0 0 12px;">
            <input type="text" id="searchMembres"
                   placeholder="البحث باسم الموظف أو رقم التأجير..."
                   oninput="filtrerMembres()"
                   style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:inherit;direction:rtl;">
        </div>

        <div id="modalListeBody" style="max-height:60vh;overflow:auto;">
            <p style="text-align:center;color:#64748b;">جاري التحميل...</p>
        </div>

    </div>
</div>

<style>
.liste-row:hover { background: #f0f9ff; }
#tableMembres { width:100%; border-collapse:collapse; font-size:13px; }
#tableMembres th { background:#f1f5f9; color:#0f172a; border:1px solid #e2e8f0; padding:9px 10px; white-space:nowrap; }
#tableMembres td { border:1px solid #e2e8f0; padding:8px 10px; text-align:center; vertical-align:middle; }
#tableMembres tbody tr:hover { background:#f8fafc; }
</style>

<script>
function ouvrirListe(idListe, titre) {
    document.getElementById('modalListeTitle').textContent = titre;
    document.getElementById('searchMembres').value = '';
    document.getElementById('modalListeBody').innerHTML = '<p style="text-align:center;color:#64748b;padding:20px;">جاري التحميل...</p>';
    document.getElementById('modalListe').classList.add('active');

    fetch('ajax/get_liste_membres.php?id_liste=' + encodeURIComponent(idListe))
        .then(r => r.json())
        .then(data => afficherMembres(data))
        .catch(() => {
            document.getElementById('modalListeBody').innerHTML =
                '<p style="color:red;text-align:center;padding:20px;">خطأ في تحميل البيانات</p>';
        });
}

function fermerModalListe() {
    document.getElementById('modalListe').classList.remove('active');
}

function esc(s) {
    if (!s) return '—';
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function afficherMembres(data) {
    const body = document.getElementById('modalListeBody');

    if (!data.membres || data.membres.length === 0) {
        body.innerHTML = '<p style="text-align:center;color:#64748b;padding:20px;">لا يوجد أعضاء مسجلون في هذه اللائحة</p>';
        return;
    }

    let html = '<p style="margin-bottom:10px;color:#64748b;font-size:13px;">عدد الموظفين: <strong style="color:#0f172a;">' + data.count + '</strong></p>';
    html += '<table id="tableMembres"><thead><tr>';

    if (data.type === 'permanence') {
        html += '<th>#</th><th>الاسم الكامل</th><th>رقم التأجير</th><th>الإطار</th><th>تواريخ الديمومة</th><th>مجموع الأيام</th>';
        html += '</tr></thead><tbody>';
        data.membres.forEach((m, i) => {
            html += '<tr data-search="' + esc(m.nom_complet) + ' ' + esc(m.numero_tajir) + '">';
            html += '<td>' + (i + 1) + '</td>';
            html += '<td style="text-align:right;">' + esc(m.nom_complet) + '</td>';
            html += '<td>' + esc(m.numero_tajir) + '</td>';
            html += '<td>' + esc(m.cadre) + '</td>';
            html += '<td style="text-align:right;font-size:12px;">' + m.dates.join(' &nbsp;|&nbsp; ') + '</td>';
            html += '<td><strong>' + m.total + '</strong></td>';
            html += '</tr>';
        });
    } else {
        html += '<th>#</th><th>الاسم الكامل</th><th>رقم التأجير</th><th>الإطار</th><th>الشهر الأول</th><th>الشهر الثاني</th><th>الشهر الثالث</th><th>المجموع (ساعة)</th>';
        html += '</tr></thead><tbody>';
        data.membres.forEach((m, i) => {
            const fmt = v => v > 0 ? v.toFixed(1) : '—';
            html += '<tr data-search="' + esc(m.nom_complet) + ' ' + esc(m.numero_tajir) + '">';
            html += '<td>' + (i + 1) + '</td>';
            html += '<td style="text-align:right;">' + esc(m.nom_complet) + '</td>';
            html += '<td>' + esc(m.numero_tajir) + '</td>';
            html += '<td>' + esc(m.cadre) + '</td>';
            html += '<td>' + fmt(m.month_1_hours) + '</td>';
            html += '<td>' + fmt(m.month_2_hours) + '</td>';
            html += '<td>' + fmt(m.month_3_hours) + '</td>';
            html += '<td><strong>' + fmt(m.total_hours) + '</strong></td>';
            html += '</tr>';
        });
    }

    html += '</tbody></table>';
    body.innerHTML = html;
}

function filtrerMembres() {
    const q = document.getElementById('searchMembres').value.trim().toLowerCase();
    const rows = document.querySelectorAll('#tableMembres tbody tr');
    rows.forEach(row => {
        const txt = (row.dataset.search || '').toLowerCase();
        row.style.display = (!q || txt.includes(q)) ? '' : 'none';
    });
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') fermerModalListe();
});
</script>

<?php require 'views/layouts/footer.php'; ?>
