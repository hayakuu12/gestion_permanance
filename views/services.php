<?php

require_once 'autoload.php';
require_once 'models/Liste.php';

$listeModel = new Liste();

$statsParService = $listeModel->getStatsByService();

$totalPermanence  = array_sum(array_column($statsParService, 'permanence'));
$totalHeuresSupp  = array_sum(array_column($statsParService, 'heures_supp'));
$totalGlobal      = $totalPermanence + $totalHeuresSupp;

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <div class="topbar">
        <h1>إحصائيات المصالح</h1>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="cards">

        <div class="card">
            <h3>عدد المصالح</h3>
            <p><?= count($statsParService) ?></p>
        </div>

        <div class="card">
            <h3>إجمالي سجلات الديمومة</h3>
            <p><?= $totalPermanence ?></p>
        </div>

        <div class="card">
            <h3>إجمالي سجلات الساعات الإضافية</h3>
            <p><?= $totalHeuresSupp ?></p>
        </div>

        <div class="card">
            <h3>المجموع الكلي</h3>
            <p><?= $totalGlobal ?></p>
        </div>

    </div>

    <!-- SERVICE CARDS GRID -->
    <?php if (!empty($statsParService)): ?>

        <div class="services-grid">

            <?php foreach ($statsParService as $s): ?>

                <?php
                    $totalService  = $s['permanence'] + $s['heures_supp'];
                    $pctPermanence = $totalService > 0 ? round($s['permanence'] / $totalService * 100) : 0;
                    $pctHeures     = $totalService > 0 ? round($s['heures_supp'] / $totalService * 100) : 0;
                ?>

                <div class="service-card">

                    <div class="service-name">
                        <?= htmlspecialchars($s['service']) ?>
                    </div>

                    <div class="service-stats">

                        <div class="service-stat perm">
                            <span class="service-stat-label">الديمومة</span>
                            <span class="service-stat-value"><?= $s['permanence'] ?></span>
                            <span class="service-stat-sub">سجل</span>
                        </div>

                        <div class="service-divider"></div>

                        <div class="service-stat hs">
                            <span class="service-stat-label">الساعات الإضافية</span>
                            <span class="service-stat-value"><?= $s['heures_supp'] ?></span>
                            <span class="service-stat-sub">سجل</span>
                        </div>

                    </div>

                    <div class="service-bar-wrap">
                        <div class="service-bar">
                            <div class="service-bar-perm" style="width: <?= $pctPermanence ?>%">
                                <?php if ($pctPermanence >= 15): ?>
                                    <?= $pctPermanence ?>%
                                <?php endif; ?>
                            </div>
                            <div class="service-bar-hs" style="width: <?= $pctHeures ?>%">
                                <?php if ($pctHeures >= 15): ?>
                                    <?= $pctHeures ?>%
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="service-bar-legend">
                            <span class="legend-perm">الديمومة</span>
                            <span class="legend-hs">الساعات الإضافية</span>
                        </div>
                    </div>

                    <div class="service-total">
                        المجموع: <strong><?= $totalService ?></strong> سجل
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="box">
            <p style="text-align:center; padding: 30px; color: #64748b;">لا توجد بيانات حاليا</p>
        </div>

    <?php endif; ?>

</div>

<?php require 'views/layouts/footer.php'; ?>
