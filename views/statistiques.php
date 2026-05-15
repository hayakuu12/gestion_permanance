<?php

require_once 'autoload.php';
require_once 'models/Liste.php';
require_once 'models/Observation.php';

$listeModel = new Liste();
$observationModel = new Observation();

$totalPermanences = $listeModel->countPermanences();
$totalHeuresSupp  = $listeModel->countHeuresSupp();

$statuts  = $listeModel->countByStatut();
$services = $listeModel->countByService();
$niveaux  = $observationModel->countByNiveau();

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <div class="topbar">
        <h1>الإحصائيات</h1>
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

    </div>

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

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
Chart.defaults.font.family = "Tahoma, Arial, sans-serif";
Chart.defaults.font.size = 13;

const paletteBlue   = ["#2563eb", "#0ea5e9", "#6366f1", "#8b5cf6", "#ec4899", "#f59e0b", "#10b981", "#ef4444", "#14b8a6"];
const paletteStatus = ["#16a34a", "#dc2626", "#f59e0b"];
const paletteNiveau = ["#2563eb", "#f59e0b", "#dc2626"];

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
        plugins: {
            legend: { position: "bottom" }
        },
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
