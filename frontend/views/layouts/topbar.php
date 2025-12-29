<?php
use yii\helpers\Url;
use yii\helpers\Html;

$user = Yii::$app->user->identity;

if (!Yii::$app->user->isGuest) {

    // Safe check for avatar (avoid test failure)
    $avatarAttribute = $user->avatar ?? null;

    if (!empty($avatarAttribute)) {
        $avatarUrl = Yii::$app->request->baseUrl . "/uploads/avatars/" . $avatarAttribute;
    } else {
        // Safe handling for missing first/last name in test data
        $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if ($fullName === '') {
            $fullName = $user->username ?? 'User';
        }

        $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) . "&size=50";
    }

} else {
    // custom image for guests
    $avatarUrl = Yii::$app->request->baseUrl . "/teammanagment/img/nico.png";
}
?>

<div class="topbar d-flex justify-content-end align-items-center p-2">

    <?php if (!Yii::$app->user->isGuest): ?>
        <div class="me-3">
            <a href="<?= Url::to(['notifications/index']) ?>" class="text-decoration-none nav-link">
                <i class="bi bi-bell fs-5"></i>
            </a>
        </div>
    <?php endif; ?>

    <div class="dropdown">

        <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
            <img src="<?= $avatarUrl ?>" class="avatar-sm me-2 rounded-circle" width="40" height="40">
        </a>

        <ul class="dropdown-menu dropdown-menu-end">

            <?php if (Yii::$app->user->isGuest): ?>

                <li><a class="dropdown-item" href="<?= Url::to(['/site/login']) ?>">Login</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/site/signup']) ?>">Signup</a></li>

            <?php else: ?>

                <li class="px-3 py-2">
                    <div class="d-flex align-items-center">
                        <img src="<?= $avatarUrl ?>" class="rounded-circle me-2" width="50" height="50" style="object-fit: cover;">
                        <div>
                            <div class="fw-semibold"><?= $user->username ?></div>
                            <div class="text-muted small"><?= $user->email ?></div>
                        </div>
                    </div>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li><a class="dropdown-item" href="<?= Url::to(['managment/profile']) ?>">My Profile</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['managment/mytasks']) ?>">My Tasks</a></li>

                <li><hr class="dropdown-divider"></li>

                    <li>
                        <?= Html::beginForm(['/site/logout'], 'post') ?>
                            <button class="dropdown-item text-danger">Logout</button>
                        <?= Html::endForm() ?>
                    </li>

            <?php endif; ?>

        </ul>

    </div>

</div>
