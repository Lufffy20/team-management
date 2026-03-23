<?php
use yii\helpers\Html;
use frontend\assets\AppAsset;

AppAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($this->title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <?php $this->head() ?>
</head>

<body>
<?php $this->beginBody() ?>

<!-- TOPBAR -->
<?= $this->render('topbar') ?>

<!-- OVERLAY FOR MOBILE SIDEBAR -->
<div class="menu-overlay" id="menuOverlay"></div>

<!-- SIDEBAR -->
<?= $this->render('sidebar') ?>

<!-- MAIN CONTENT AREA -->
<div class="content">

    <!-- FLASH MESSAGES -->
    <div class="container mb-3">
        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                <?= Html::encode($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- MAIN PAGE CONTENT -->
    <div class="container">
        <?= $content ?>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('menuOverlay');
    const toggleBtn = document.getElementById('sidebarToggleBtn');

    if (toggleBtn) {
        // Toggle Sidebar
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent immediate closing
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });
    }

    if (overlay) {
        // Close Sidebar when clicking on overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }
});
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
