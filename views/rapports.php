<?php

require_once 'autoload.php';

require_once 'models/Liste.php';
require_once 'models/Observation.php';

$listeModel       = new Liste();
$observationModel = new Observation();

/* ── VALIDATION ACTIONS ── */
if (isset($_POST['valider'])) {
    $listeModel->changerStatut($_POST['id_liste'], 'تمت المصادقة', null);
}
if (isset($_POST['refuser'])) {
    $listeModel->changerStatut($_POST['id_liste'], 'مرفوضة', $_POST['commentaire']);
}

/* ── FILTERS ── */
$annee        = $_GET['annee']        ?? '';
$trimestre    = $_GET['trimestre']    ?? '';
$service      = $_GET['service']      ?? '';
$numero_tajir = $_GET['numero_tajir'] ?? '';
$type_liste   = $_GET['type_liste']   ?? '';

/* ── DATA ── */
$elements     = $listeModel->filtrerElements($annee, $trimestre, $service, $numero_tajir, $type_liste);
$observations = $observationModel->getObservations();

$moisNames = [
    1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'ماي',6=>'يونيو',
    7=>'يوليوز',8=>'غشت',9=>'شتنبر',10=>'أكتوبر',11=>'نونبر',12=>'دجنبر'
];

/* Group heures_supp — each element is already one overtime_record with month columns */
function groupHeuresSupp(array $elements): array {
    $grouped = [];
    foreach ($elements as $h) {
        if (($h['type_liste'] ?? '') !== 'heures_supp') continue;
        $key = ($h['id_liste'] ?? '') . '|' . ($h['numero_tajir'] ?? 'x');
        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'nom_complet'  => $h['nom_complet']    ?? '',
                'numero_tajir' => $h['numero_tajir']    ?? '',
                'cadre'        => $h['cadre']           ?? '',
                'service'      => $h['service']         ?? '',
                'trimestre'    => $h['trimestre']       ?? '',
                'annee'        => $h['annee']           ?? '',
                'statut'       => $h['statut']          ?? '',
                'id_liste'     => $h['id_liste']        ?? '',
                'base_month'   => intval($h['base_month'] ?? 1),
                'h1'           => floatval($h['month_1_hours'] ?? 0),
                'h2'           => floatval($h['month_2_hours'] ?? 0),
                'h3'           => floatval($h['month_3_hours'] ?? 0),
                'total'        => floatval($h['nombre_heures'] ?? 0),
                'travaux'      => $h['travaux'] ?? '',
            ];
        }
    }
    return $grouped;
}

function classifyDateRapport(array $p): string {
    $dt = $p['date_type'] ?? '';
    if ($dt === 'HOLIDAY') return 'ferie';
    if ($dt === 'WEEKEND') return 'weekend';
    $jour = $p['jour'] ?? '';
    if ($jour === 'السبت' || $jour === 'الأحد') return 'weekend';
    return 'normal';
}

function groupPermanences(array $elements): array {
    $grouped = [];
    foreach ($elements as $p) {
        if (($p['type_liste'] ?? '') !== 'permanence') continue;
        $key = ($p['id_liste'] ?? '') . '|' . ($p['numero_tajir'] ?? 'x');
        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'nom_complet'  => $p['nom_complet']  ?? '',
                'numero_tajir' => $p['numero_tajir']  ?? '',
                'cin'          => $p['cin']           ?? '',
                'cadre'        => $p['cadre']         ?? '',
                'service'      => $p['service']       ?? '',
                'statut'       => $p['statut']        ?? '',
                'id_liste'     => $p['id_liste']      ?? '',
                'records'      => []
            ];
        }
        $grouped[$key]['records'][] = $p;
    }
    return $grouped;
}

/* ── BUILD GROUPED DATA ── */
$grouped_hs   = groupHeuresSupp($elements);
$grouped_perm = groupPermanences($elements);

/* Grand totals */
$grand_total_heures = 0.0;
foreach ($grouped_hs as $g) {
    $grand_total_heures += $g['total'];
}

$grand_total_jours = 0;
foreach ($grouped_perm as $g) {
    $grand_total_jours += count($g['records']);
}

/* ── STATUS BADGE HELPER ── */
function statutBadge(string $statut): string {
    if ($statut === 'تمت المصادقة' || $statut === 'VALID')
        return '<span class="badge info">تمت المصادقة</span>';
    if ($statut === 'مرفوضة' || $statut === 'INVALID')
        return '<span class="badge danger">مرفوضة</span>';
    return '<span class="badge warning">في الانتظار</span>';
}

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <h1>التقارير والمراقبة</h1>
    </div>

    <!-- EXPORTS -->
    <div class="cards">

        <div class="card">
            <h3>تصدير Excel</h3>
            <p>XLS</p>
            <br>
            <a href="exports/export_excel.php<?= $annee || $trimestre || $service ? '?' . http_build_query(array_filter(compact('annee','trimestre','service','numero_tajir','type_liste'))) : '' ?>"
               class="btn-valid">تحميل Excel</a>
        </div>

        <div class="card">
            <h3>تصدير PDF</h3>
            <p>PDF</p>
            <br>
            <a href="exports/export_pdf.php<?= $annee || $trimestre || $service ? '?' . http_build_query(array_filter(compact('annee','trimestre','service','numero_tajir','type_liste'))) : '' ?>"
               class="btn-edit">تحميل PDF</a>
        </div>

        <div class="card">
            <h3>ملخص النتائج</h3>
            <p style="font-size:14px;line-height:1.8;">
                موظفو الساعات الإضافية: <strong><?= count($grouped_hs) ?></strong><br>
                موظفو الديمومة: <strong><?= count($grouped_perm) ?></strong><br>
                مجموع الساعات: <strong style="color:#1e40af;"><?= number_format($grand_total_heures, 1) ?> س</strong>
            </p>
        </div>

    </div>

    <!-- FILTERS -->
    <div class="box">
        <h2>البحث والتصفية</h2>
        <form method="GET">
            <input type="hidden" name="page" value="rapports">
            <div class="row">

                <div class="input-group">
                    <label>النوع</label>
                    <select name="type_liste">
                        <option value="">الكل</option>
                        <option value="heures_supp" <?= $type_liste=="heures_supp" ? "selected" : "" ?>>الساعات الإضافية</option>
                        <option value="permanence"  <?= $type_liste=="permanence"  ? "selected" : "" ?>>الديمومة</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>السنة</label>
                    <input type="number" name="annee" value="<?= htmlspecialchars($annee) ?>">
                </div>

                <div class="input-group">
                    <label>الشطر</label>
                    <select name="trimestre">
                        <option value="">الكل</option>
                        <option value="1" <?= $trimestre=="1" ? "selected" : "" ?>>الأول</option>
                        <option value="2" <?= $trimestre=="2" ? "selected" : "" ?>>الثاني</option>
                        <option value="3" <?= $trimestre=="3" ? "selected" : "" ?>>الثالث</option>
                        <option value="4" <?= $trimestre=="4" ? "selected" : "" ?>>الرابع</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>المصلحة</label>
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

                <div class="input-group">
                    <label>رقم التأجير</label>
                    <input type="text" name="numero_tajir" value="<?= htmlspecialchars($numero_tajir) ?>">
                </div>

            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit" class="btn-edit">بحث</button>
                <a href="?page=rapports" class="btn-delete" style="padding:12px 20px;">إعادة تعيين</a>
            </div>
        </form>
    </div>

    <!-- ===== SECTION 1: HEURES SUPPLÉMENTAIRES ===== -->
    <?php if ($type_liste === '' || $type_liste === 'heures_supp'): ?>
    <div class="box">

        <h2>
            الساعات الإضافية
            <?php if (!empty($grouped_hs)): ?>
                <span style="font-size:14px;font-weight:normal;color:#64748b;margin-right:12px;">
                    (<?= count($grouped_hs) ?> موظف —
                    المجموع: <strong style="color:#1e40af;"><?= number_format($grand_total_heures, 1) ?> ساعة</strong>)
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
                        <th rowspan="2">الحالة</th>
                        <th rowspan="2">الإجراء</th>
                    </tr>
                    <tr>
                        <th class="sub-header">الشهر الأول</th>
                        <th class="sub-header">الشهر الثاني</th>
                        <th class="sub-header">الشهر الثالث</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($grouped_hs)): ?>
                        <?php foreach ($grouped_hs as $g): ?>
                            <?php
                            $bm = $g['base_month'];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($g['nom_complet']) ?></td>
                                <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
                                <td><?= htmlspecialchars($g['cadre']) ?></td>
                                <td><?= htmlspecialchars($g['service']) ?></td>
                                <td><?= htmlspecialchars($g['trimestre']) ?></td>
                                <td><?= htmlspecialchars($g['annee']) ?></td>
                                <?php foreach ([[$g['h1'],$bm], [$g['h2'],$bm+1], [$g['h3'],$bm+2]] as [$hv,$mn]): ?>
                                <td class="heures-cell">
                                    <?php if ($hv > 0): ?>
                                        <strong><?= number_format($hv, 1) ?></strong>
                                        <small class="month-label">(<?= $moisNames[$mn] ?? $mn ?>)</small>
                                    <?php else: ?><span class="no-date">—</span><?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                                <td class="heures-cell">
                                    <?php if ($g['total'] > 0): ?>
                                        <strong style="font-size:17px;color:#0f172a;"><?= number_format($g['total'], 1) ?></strong>
                                    <?php else: ?><span class="no-date">—</span><?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($g['travaux']) ?></td>
                                <td><?= statutBadge($g['statut']) ?></td>
                                <td>
                                    <div class="actions-box">
                                        <form method="POST">
                                            <input type="hidden" name="id_liste" value="<?= htmlspecialchars($g['id_liste']) ?>">
                                            <button type="submit" name="valider" class="btn-valid">قبول</button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="id_liste" value="<?= htmlspecialchars($g['id_liste']) ?>">
                                            <input type="text" name="commentaire" class="comment-input" placeholder="سبب الرفض" required>
                                            <button type="submit" name="refuser" class="btn-delete">رفض</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- TOTALS ROW -->
                        <tr style="background:#f0f9ff;font-weight:bold;border-top:2px solid #93c5fd;">
                            <td colspan="9" style="text-align:right;color:#1e40af;">المجموع الكلي للساعات</td>
                            <td class="heures-cell">
                                <strong style="font-size:17px;color:#1e40af;"><?= number_format($grand_total_heures, 1) ?></strong>
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="13">لا توجد معطيات للساعات الإضافية</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== SECTION 2: PERMANENCES ===== -->
    <?php if ($type_liste === '' || $type_liste === 'permanence'): ?>
    <div class="box">

        <h2>الديمومة
            <?php if (!empty($grouped_perm)): ?>
                <span style="font-size:14px;font-weight:normal;color:#64748b;margin-right:12px;">
                    (<?= count($grouped_perm) ?> موظف)
                </span>
            <?php endif; ?>
        </h2>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>الاسم الكامل</th>
                        <th>رقم التأجير</th>
                        <th>الإطار</th>
                        <th>المصلحة</th>
                        <th>أيام الديمومة</th>
                        <th>عطل نهاية الأسبوع</th>
                        <th>أعياد وطنية</th>
                        <th>مجموع الأيام</th>
                        <th>الحالة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($grouped_perm)): ?>
                        <?php foreach ($grouped_perm as $g):
                            $allDates = $weekendDates = $ferieDates = [];
                            $totalJours = 0;
                            foreach ($g['records'] as $rec) {
                                $cat     = classifyDateRapport($rec);
                                $jour    = $rec['jour'] ?? '';
                                $type    = $rec['type_jour'] ?? '';
                                $raw     = $rec['date_debut'] ?? '';
                                $dateFmt = $raw ? date('d/m/Y', strtotime($raw)) : '';
                                $lbl     = $dateFmt ?: ($jour ?: $type ?: '—');
                                $allDates[] = ['lbl' => $lbl, 'cat' => $cat];
                                if ($cat === 'weekend') $weekendDates[] = $dateFmt ?: ($jour ?: 'عطلة أسبوعية');
                                elseif ($cat === 'ferie') $ferieDates[] = $dateFmt ?: ($jour ?: 'عيد');
                                $totalJours++;
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($g['nom_complet']) ?></td>
                            <td><?= htmlspecialchars($g['numero_tajir']) ?></td>
                            <td><?= htmlspecialchars($g['cadre']) ?></td>
                            <td><?= htmlspecialchars($g['service']) ?></td>
                            <td class="dates-cell">
                                <?php foreach ($allDates as $d): ?>
                                    <span class="date-pill date-<?= $d['cat'] ?>"><?= htmlspecialchars($d['lbl']) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td class="dates-cell">
                                <?php if (!empty($weekendDates)): ?>
                                    <?php foreach (array_unique($weekendDates) as $wd): ?>
                                        <span class="date-pill date-weekend"><?= htmlspecialchars($wd) ?></span>
                                    <?php endforeach; ?>
                                    <small class="month-label">(<?= count($weekendDates) ?> يوم)</small>
                                <?php else: ?><span class="no-date">—</span><?php endif; ?>
                            </td>
                            <td class="dates-cell">
                                <?php if (!empty($ferieDates)): ?>
                                    <?php foreach (array_unique($ferieDates) as $fd): ?>
                                        <span class="date-pill date-ferie"><?= htmlspecialchars($fd) ?></span>
                                    <?php endforeach; ?>
                                    <small class="month-label">(<?= count($ferieDates) ?> يوم)</small>
                                <?php else: ?><span class="no-date">—</span><?php endif; ?>
                            </td>
                            <td><strong><?= $totalJours ?></strong></td>
                            <td><?= statutBadge($g['statut']) ?></td>
                            <td>
                                <div class="actions-box">
                                    <form method="POST">
                                        <input type="hidden" name="id_liste" value="<?= htmlspecialchars($g['id_liste']) ?>">
                                        <button type="submit" name="valider" class="btn-valid">قبول</button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="id_liste" value="<?= htmlspecialchars($g['id_liste']) ?>">
                                        <input type="text" name="commentaire" class="comment-input" placeholder="سبب الرفض" required>
                                        <button type="submit" name="refuser" class="btn-delete">رفض</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <!-- TOTALS ROW -->
                        <tr style="background:#f0fdf4;font-weight:bold;border-top:2px solid #86efac;">
                            <td colspan="7" style="text-align:right;color:#16a34a;">مجموع أيام الديمومة</td>
                            <td><strong style="font-size:17px;color:#16a34a;"><?= $grand_total_jours ?></strong></td>
                            <td colspan="2"></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="10">لا توجد معطيات ديمومة</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== OBSERVATIONS ===== -->
    <div class="box">
        <h2>الملاحظات الإدارية</h2>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>رقم اللائحة</th>
                        <th>النوع</th>
                        <th>المصلحة</th>
                        <th>نوع الملاحظة</th>
                        <th>التفاصيل</th>
                        <th>المستوى</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($observations)): ?>
                        <?php foreach ($observations as $o): ?>
                            <tr>
                                <td><?= htmlspecialchars($o['id_liste'] ?? '') ?></td>
                                <td><?= ($o['type_liste'] ?? '') == 'permanence' ? 'ديمومة' : 'ساعات إضافية' ?></td>
                                <td><?= htmlspecialchars($o['service'] ?? '') ?></td>
                                <td><?= htmlspecialchars($o['type_observation'] ?? '') ?></td>
                                <td><?= htmlspecialchars($o['message'] ?? '') ?></td>
                                <td>
                                    <?php if (($o['niveau'] ?? '') == "grave"): ?>
                                        <span class="badge danger">خطير</span>
                                    <?php elseif (($o['niveau'] ?? '') == "attention"): ?>
                                        <span class="badge warning">تنبيه</span>
                                    <?php else: ?>
                                        <span class="badge info">عادي</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($o['date_observation'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7">لا توجد ملاحظات حاليا</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require 'views/layouts/footer.php'; ?>
