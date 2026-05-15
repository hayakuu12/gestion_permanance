<?php

require_once 'autoload.php';

require_once 'models/Controle.php';

$controleModel = new Controle();

$controles = $controleModel->getControles();

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <div class="topbar">
        <h1>المراقبة والملاحظات</h1>
    </div>

    <div class="box">

        <h2>نتائج المراقبة التلقائية</h2>

        <div class="table-scroll">

            <table>

                <thead>
                    <tr>
                        <th>الاسم الكامل</th>
                        <th>رقم التأجير</th>
                        <th>نوع المراقبة</th>
                        <th>الملاحظة</th>
                        <th>المستوى</th>
                        <th>التاريخ</th>
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
                                <td><?= htmlspecialchars($controle['date_controle'] ?? '') ?></td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6">لا توجد مخالفات أو ملاحظات حاليا</td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php require 'views/layouts/footer.php'; ?>
