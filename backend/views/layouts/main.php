<?php

use yii\helpers\Html;
use backend\assets\AppAsset;
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="layout-wrapper layout-content-navbar">
<?php $this->beginBody() ?>

<div class="layout-container">

        <!-- Navbar -->
        <?= $this->render('sidebar') ?>

        <?= $this->render('navbar') ?>
        <!-- Content wrapper -->
        <div class="content-wrapper">
            <!-- Main Content -->
            <?= $content ?>
            <!-- / Main Content -->

            <!-- Footer -->
            <?= $this->render('footer') ?>
        </div>
        <!-- / Content wrapper -->

    </div>
    <!-- / layout-page -->

<!-- / layout-container -->

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
