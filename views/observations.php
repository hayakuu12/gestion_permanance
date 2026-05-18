<?php

require_once 'autoload.php';

require_once 'models/Observation.php';
require_once 'models/Liste.php';

$success = "";

$observationModel = new Observation();
$listeModel = new Liste();

$listes = $listeModel->getRecentListes();

if (isset($_POST['ajouter_observation'])) {

    $observationModel->ajouterObservation(
        $_POST['id_liste'],
        $_POST['type_observation'],
        $_POST['message'],
        $_POST['niveau']
    );

    $success = "تمت إضافة الملاحظة بنجاح";
}

$observations = $observationModel->getObservations();

?>

<?php require 'views/layouts/header.php'; ?>
<?php require 'views/layouts/sidebar.php'; ?>

<div class="main">

    <div class="topbar">
        <h1>الملاحظات الإدارية</h1>
    </div>

    <?php if ($success != ""): ?>
        <div class="success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="box">

        <h2>إضافة ملاحظة</h2>

        <form method="POST">

            <div class="row">

                <div class="input-group">

                    <label>اللائحة</label>

                    <select name="id_liste" required>

                        <option value="">اختيار اللائحة</option>

                        <?php foreach ($listes as $liste): ?>
                            <option value="<?= htmlspecialchars($liste['id'] ?? $liste['id_liste'] ?? '') ?>">
                                <?= htmlspecialchars($liste['id'] ?? $liste['id_liste'] ?? '') ?> —
                                <?= ($liste['type_liste'] ?? '') == 'permanence' ? 'ديمومة' : 'ساعات إضافية' ?> —
                                <?= htmlspecialchars($liste['service'] ?? '') ?> —
                                <?= htmlspecialchars($liste['annee'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="input-group">

                    <label>نوع الملاحظة</label>

                    <select name="type_observation" required>
                        <option value="">اختيار نوع الملاحظة</option>
                        <option value="خطأ في الرقم المالي">خطأ في الرقم المالي</option>
                        <option value="خطأ في رقم البطاقة الوطنية">خطأ في رقم البطاقة الوطنية</option>
                        <option value="تجاوز عدد الأيام المسموح">تجاوز عدد الأيام المسموح</option>
                        <option value="عدم التوقيع أو التأشير">عدم التوقيع أو التأشير</option>
                        <option value="القوائم غير مطبوعة">القوائم غير مطبوعة</option>
                        <option value="عدم احترام الآجال">عدم احترام الآجال</option>
                        <option value="الجمع بين تعويضين">الجمع بين تعويضين</option>
                        <option value="ملاحظة أخرى">ملاحظة أخرى</option>
                    </select>

                </div>

                <div class="input-group">

                    <label>المستوى</label>

                    <select name="niveau" required>
                        <option value="info">عادي</option>
                        <option value="attention">تنبيه</option>
                        <option value="grave">خطير</option>
                    </select>

                </div>

            </div>

            <div class="input-group">

                <label>تفاصيل الملاحظة</label>

                <textarea name="message" placeholder="اكتب تفاصيل الملاحظة هنا..." required></textarea>

            </div>

            <button type="submit" name="ajouter_observation">
                إضافة الملاحظة
            </button>

        </form>

    </div>

    <div class="box">

        <h2>لائحة الملاحظات</h2>

        <div class="table-scroll">

            <table>

                <thead>
                    <tr>
                        <th>رقم اللائحة</th>
                        <th>نوع اللائحة</th>
                        <th>المصلحة</th>
                        <th>نوع الملاحظة</th>
                        <th>التفاصيل</th>
                        <th>المستوى</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($observations)): ?>

                        <?php foreach ($observations as $obs): ?>

                            <tr>
                                <td><?= htmlspecialchars($obs['id_liste'] ?? '') ?></td>
                                <td><?= $obs['type_liste'] == 'permanence' ? 'ديمومة' : 'ساعات إضافية' ?></td>
                                <td><?= htmlspecialchars($obs['service'] ?? '') ?></td>
                                <td><?= htmlspecialchars($obs['type_observation'] ?? '') ?></td>
                                <td><?= htmlspecialchars($obs['message'] ?? '') ?></td>
                                <td>
                                    <?php if ($obs['niveau'] == 'grave'): ?>
                                        <span class="badge danger">خطير</span>
                                    <?php elseif ($obs['niveau'] == 'attention'): ?>
                                        <span class="badge warning">تنبيه</span>
                                    <?php else: ?>
                                        <span class="badge info">عادي</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($obs['date_observation'] ?? $obs['created_at'] ?? '') ?></td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7">لا توجد ملاحظات حاليا</td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php require 'views/layouts/footer.php'; ?>
