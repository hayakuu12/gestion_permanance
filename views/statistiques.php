<?php

require_once 'autoload.php';
require_once 'models/Liste.php';
require_once 'models/Observation.php';

$listeModel      = new Liste();
$observationModel = new Observation();

/* ── Basic counts ── */
$totalPermanences = $listeModel->countPermanences();
$totalHeuresSupp  = $listeModel->countHeuresSupp();
$statuts          = $listeModel->countByStatut();
$services         = $listeModel->countByService();
$niveaux          = $observationModel->countByNiveau();

/* ── Extended stats ── */
$moisActuel    = (int) date('n');
$anneeActuelle = (int) date('Y');

$moisNames = [
    1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'ماي',6=>'يونيو',
    7=>'يوليوز',8=>'غشت',9=>'شتنبر',10=>'أكتوبر',11=>'نونبر',12=>'دجنبر'
];

$heuresCeMois   = $listeModel->getHeuresSuppCeMois($moisActuel, $anneeActuelle);
$permCeMois     = $listeModel->getPermanencesCeMois($moisActuel, $anneeActuelle);
$employesActifs = $listeModel->getNombreEmployesActifs();
$moyenneHeures  = $listeModel->getMoyenneHeuresParEmploye();
$topHeures      = $listeModel->getTopEmployeesHeures(5);
$topPerm        = $listeModel->getTopEmployeesPermanences(5);
$evolution      = $listeModel->getEvolutionMensuelle($anneeActuelle);
$comparaison    = $listeModel->getComparaisonMois($moisActuel, $anneeActuelle);

/* Build evolution arrays (fill missing months with 0) */
$evoHeures = array_fill(1, 12, 0);
$evoPerm   = array_fill(1, 12, 0);
foreach ($evolution as $row) {
    $m = intval($row['mois']);
    if ($m >= 1 && $m <= 12) {
        $evoHeures[$m] = floatval($row['total_heures']);
        $evoPerm[$m]   = intval($row['total_perm']);
    }
}

/* Comparison deltas */
$deltaHeures = floatval($comparaison['heures_actuel']) - floatval($comparaison['heures_precedent']);
$deltaPerm   = intval($comparaison['perm_actuel'])     - intval($comparaison['perm_precedent']);

function trendBadge(float $delta, string $unit = ''): string {
    if ($delta > 0)  return "<span class='trend-up'>▲ +" . number_format($delta, 1) . " $unit</span>";
    if ($delta < 0)  return "<span class='trend-down'>▼ " . number_format($delta, 1) . " $unit</span>";
    return "<span class='trend-flat'>— لا تغيير</span>";
}

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <div class="topbar">
        <h1>الإحصائيات والتحليلات</h1>
    </div>

    <!-- ===== KPI ROW 1 ===== -->
    <div class="cards">

        <div class="card stat-card stat-blue">
            <div class="stat-icon">📋</div>
            <div class="stat-body">
                <div class="stat-value"><?= intval($totalPermanences) ?></div>
                <div class="stat-label">لوائح الديمومة الإجمالية</div>
            </div>
        </div>

        <div class="card stat-card stat-green">
            <div class="stat-icon">⏱️</div>
            <div class="stat-body">
                <div class="stat-value"><?= intval($totalHeuresSupp) ?></div>
                <div class="stat-label">لوائح الساعات الإضافية</div>
            </div>
        </div>

        <div class="card stat-card stat-purple">
            <div class="stat-icon">👥</div>
            <div class="stat-body">
                <div class="stat-value"><?= $employesActifs ?></div>
                <div class="stat-label">الموظفون النشيطون</div>
            </div>
        </div>

        <div class="card stat-card stat-orange">
            <div class="stat-icon">📊</div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($moyenneHeures, 1) ?></div>
                <div class="stat-label">متوسط الساعات / موظف</div>
            </div>
        </div>

    </div>

    <!-- ===== KPI ROW 2 — THIS MONTH ===== -->
    <div class="cards">

        <div class="card stat-card stat-teal">
            <div class="stat-icon">🕐</div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($heuresCeMois, 1) ?></div>
                <div class="stat-label">ساعات إضافية — <?= $moisNames[$moisActuel] ?> <?= $anneeActuelle ?></div>
                <div class="stat-trend"><?= trendBadge($deltaHeures, 'س') ?> مقارنة بالشهر السابق</div>
            </div>
        </div>

        <div class="card stat-card stat-indigo">
            <div class="stat-icon">📅</div>
            <div class="stat-body">
                <div class="stat-value"><?= $permCeMois ?></div>
                <div class="stat-label">سجلات الديمومة — <?= $moisNames[$moisActuel] ?> <?= $anneeActuelle ?></div>
                <div class="stat-trend"><?= trendBadge($deltaPerm) ?> مقارنة بالشهر السابق</div>
            </div>
        </div>

        <div class="card stat-card stat-rose">
            <div class="stat-icon">⏮️</div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format(floatval($comparaison['heures_precedent']), 1) ?></div>
                <div class="stat-label">ساعات إضافية — <?= $moisNames[$moisActuel === 1 ? 12 : $moisActuel - 1] ?></div>
                <small style="color:#94a3b8">الشهر السابق للمقارنة</small>
            </div>
        </div>

        <div class="card stat-card stat-amber">
            <div class="stat-icon">📆</div>
            <div class="stat-body">
                <div class="stat-value"><?= intval($comparaison['perm_precedent']) ?></div>
                <div class="stat-label">ديمومة — <?= $moisNames[$moisActuel === 1 ? 12 : $moisActuel - 1] ?></div>
                <small style="color:#94a3b8">الشهر السابق للمقارنة</small>
            </div>
        </div>

    </div>

    <!-- ===== MONTHLY EVOLUTION CHART ===== -->
    <div class="box">
        <h2>التطور الشهري — <?= $anneeActuelle ?></h2>
        <canvas id="evolutionChart" style="max-height:320px;"></canvas>
    </div>

    <!-- ===== CHARTS ROW ===== -->
    <div class="cards">

        <div class="box chart-box">
            <h2>الديمومة مقابل الساعات الإضافية</h2>
            <canvas id="typeChart"></canvas>
        </div>

        <div class="box chart-box">
            <h2>حالة اللوائح</h2>
            <canvas id="statutChart"></canvas>
        </div>

    </div>

    <div class="cards">

        <div class="box chart-box">
            <h2>الملاحظات حسب المستوى</h2>
            <canvas id="niveauChart"></canvas>
        </div>

        <div class="box chart-box">
            <h2>اللوائح حسب المصلحة</h2>
            <canvas id="serviceChart"></canvas>
        </div>

    </div>

    <!-- ===== TOP EMPLOYEES ===== -->
    <div class="cards">

        <!-- Top heures supp -->
        <div class="box" style="flex:1;min-width:300px;">
            <h2>🏆 أعلى الموظفين في الساعات الإضافية</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>رقم التأجير</th>
                        <th>المجموع (ساعة)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($topHeures)): ?>
                        <?php foreach ($topHeures as $i => $emp): ?>
                            <tr>
                                <td>
                                    <?php if ($i === 0): ?>
                                        <span style="font-size:20px;">🥇</span>
                                    <?php elseif ($i === 1): ?>
                                        <span style="font-size:20px;">🥈</span>
                                    <?php elseif ($i === 2): ?>
                                        <span style="font-size:20px;">🥉</span>
                                    <?php else: ?>
                                        <?= $i + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;"><?= htmlspecialchars($emp['nom_complet']) ?></td>
                                <td><?= htmlspecialchars($emp['numero_tajir']) ?></td>
                                <td>
                                    <strong style="color:#1e40af;font-size:16px;">
                                        <?= number_format(floatval($emp['total_heures']), 1) ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">لا توجد بيانات</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Top permanences -->
        <div class="box" style="flex:1;min-width:300px;">
            <h2>🏆 أعلى الموظفين في الديمومة</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>رقم التأجير</th>
                        <th>عدد السجلات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($topPerm)): ?>
                        <?php foreach ($topPerm as $i => $emp): ?>
                            <tr>
                                <td>
                                    <?php if ($i === 0): ?>
                                        <span style="font-size:20px;">🥇</span>
                                    <?php elseif ($i === 1): ?>
                                        <span style="font-size:20px;">🥈</span>
                                    <?php elseif ($i === 2): ?>
                                        <span style="font-size:20px;">🥉</span>
                                    <?php else: ?>
                                        <?= $i + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;"><?= htmlspecialchars($emp['nom_complet']) ?></td>
                                <td><?= htmlspecialchars($emp['numero_tajir']) ?></td>
                                <td>
                                    <strong style="color:#16a34a;font-size:16px;">
                                        <?= intval($emp['total_jours']) ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">لا توجد بيانات</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
Chart.defaults.font.family = "Tahoma, Arial, sans-serif";
Chart.defaults.font.size = 13;

const moisLabels = ["يناير","فبراير","مارس","أبريل","ماي","يونيو",
                    "يوليوز","غشت","شتنبر","أكتوبر","نونبر","دجنبر"];

const paletteBlue   = ["#2563eb","#0ea5e9","#6366f1","#8b5cf6","#ec4899","#f59e0b","#10b981","#ef4444","#14b8a6"];
const paletteStatus = ["#16a34a","#dc2626","#f59e0b"];
const paletteNiveau = ["#2563eb","#f59e0b","#dc2626"];

/* Monthly evolution */
const evoHeures = <?= json_encode(array_values($evoHeures)) ?>;
const evoPerm   = <?= json_encode(array_values($evoPerm)) ?>;

new Chart(document.getElementById("evolutionChart"), {
    type: "bar",
    data: {
        labels: moisLabels,
        datasets: [
            {
                label: "الساعات الإضافية",
                data: evoHeures,
                backgroundColor: "rgba(37,99,235,0.75)",
                borderColor: "#2563eb",
                borderWidth: 1,
                borderRadius: 6,
                yAxisID: "yH"
            },
            {
                label: "سجلات الديمومة",
                data: evoPerm,
                type: "line",
                borderColor: "#16a34a",
                backgroundColor: "rgba(22,163,74,0.1)",
                borderWidth: 2.5,
                tension: 0.35,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: "#16a34a",
                yAxisID: "yP"
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: "index", intersect: false },
        plugins: {
            legend: { position: "bottom" },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.dataset.label + ": " + ctx.formattedValue +
                        (ctx.datasetIndex === 0 ? " س" : " سجل")
                }
            }
        },
        scales: {
            yH: {
                type: "linear",
                position: "right",
                beginAtZero: true,
                title: { display: true, text: "الساعات الإضافية" }
            },
            yP: {
                type: "linear",
                position: "left",
                beginAtZero: true,
                title: { display: true, text: "سجلات الديمومة" },
                grid: { drawOnChartArea: false }
            },
            x: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById("typeChart"), {
    type: "doughnut",
    data: {
        labels: ["الديمومة", "الساعات الإضافية"],
        datasets: [{
            data: [<?= intval($totalPermanences) ?>, <?= intval($totalHeuresSupp) ?>],
            backgroundColor: ["#2563eb", "#0ea5e9"],
            borderWidth: 3,
            borderColor: "#fff"
        }]
    },
    options: {
        plugins: { legend: { position: "bottom" } },
        cutout: "60%"
    }
});

new Chart(document.getElementById("statutChart"), {
    type: "bar",
    data: {
        labels: <?= json_encode(array_column($statuts, 'statut'), JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            label: "عدد اللوائح",
            data: <?= json_encode(array_column($statuts, 'total')) ?>,
            backgroundColor: paletteStatus,
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById("niveauChart"), {
    type: "pie",
    data: {
        labels: <?= json_encode(array_column($niveaux, 'niveau'), JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            data: <?= json_encode(array_column($niveaux, 'total')) ?>,
            backgroundColor: paletteNiveau,
            borderWidth: 3,
            borderColor: "#fff"
        }]
    },
    options: {
        plugins: { legend: { position: "bottom" } }
    }
});

new Chart(document.getElementById("serviceChart"), {
    type: "bar",
    data: {
        labels: <?= json_encode(array_column($services, 'service'), JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            label: "عدد اللوائح",
            data: <?= json_encode(array_column($services, 'total')) ?>,
            backgroundColor: paletteBlue,
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        indexAxis: "y",
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, ticks: { stepSize: 1 } },
            y: { grid: { display: false } }
        }
    }
});
</script>

<?php require 'views/layouts/footer.php'; ?>
