<?php

require_once 'autoload.php';
require_once 'models/Liste.php';
require_once 'models/Observation.php';

$listeModel = new Liste();
$observationModel = new Observation();

$totalPermanences = $listeModel->countPermanences();
$totalHeuresSupp = $listeModel->countHeuresSupp();

$statuts = $listeModel->countByStatut();
$services = $listeModel->countByService();
$niveaux = $observationModel->countByNiveau();

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
            <p><?= $totalPermanences ?></p>
        </div>

        <div class="card">
            <h3>الساعات الإضافية</h3>
            <p><?= $totalHeuresSupp ?></p>
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
const typeLabels = ["الديمومة", "الساعات الإضافية"];
const typeData = [
    <?= $totalPermanences ?>,
    <?= $totalHeuresSupp ?>
];

const statutLabels = <?= json_encode(array_column($statuts, 'statut'), JSON_UNESCAPED_UNICODE) ?>;
const statutData = <?= json_encode(array_column($statuts, 'total')) ?>;

const niveauLabels = <?= json_encode(array_column($niveaux, 'niveau'), JSON_UNESCAPED_UNICODE) ?>;
const niveauData = <?= json_encode(array_column($niveaux, 'total')) ?>;

const serviceLabels = <?= json_encode(array_column($services, 'service'), JSON_UNESCAPED_UNICODE) ?>;
const serviceData = <?= json_encode(array_column($services, 'total')) ?>;

new Chart(document.getElementById("typeChart"), {
    type: "doughnut",
    data: {
        labels: typeLabels,
        datasets: [{
            data: typeData
        }]
    }
});

new Chart(document.getElementById("statutChart"), {
    type: "bar",
    data: {
        labels: statutLabels,
        datasets: [{
            label: "عدد اللوائح",
            data: statutData
        }]
    }
});

new Chart(document.getElementById("niveauChart"), {
    type: "pie",
    data: {
        labels: niveauLabels,
        datasets: [{
            data: niveauData
        }]
    }
});

new Chart(document.getElementById("serviceChart"), {
    type: "bar",
    data: {
        labels: serviceLabels,
        datasets: [{
            label: "عدد اللوائح",
            data: serviceData
        }]
    }
});
</script>

<?php require 'views/layouts/footer.php'; ?>