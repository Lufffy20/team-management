<?php
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\Notification;

$isGuest = Yii::$app->user->isGuest;
$user    = Yii::$app->user->identity;

//Avatar URL (component-based)
$avatarUrl = $isGuest
    ? Yii::$app->request->baseUrl . "/teammanagment/img/nico.png"
    : Yii::$app->avatar->get($user);

//Notification count (only for logged-in users)
$unreadCount = 0;
if (!$isGuest) {
    $unreadCount = Notification::find()
        ->where([
            'user_id' => $user->id,
            'is_read' => 0
        ])
        ->count();
}
?>

<div class="topbar d-flex justify-content-end align-items-center p-2">

    <?php if (!$isGuest): ?>
        <div class="me-3 position-relative">
            <a href="<?= Url::to(['notifications/index']) ?>"
               class="nav-link text-decoration-none">

                <i class="bi bi-bell fs-5"></i>

                <?php if ($unreadCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle
                                 badge rounded-pill bg-danger">
                        <?= $unreadCount ?>
                    </span>
                <?php endif; ?>

            </a>
        </div>
    <?php endif; ?>

    <div class="dropdown">

        <a href="#"
           class="d-flex align-items-center text-decoration-none"
           data-bs-toggle="dropdown">
            <img src="<?= $avatarUrl ?>"
                 class="avatar-sm me-2 rounded-circle"
                 width="40"
                 height="40"
                 style="object-fit:cover;">
        </a>

        <ul class="dropdown-menu dropdown-menu-end">

            <?php if ($isGuest): ?>

                <li>
                    <a class="dropdown-item" href="<?= Url::to(['/site/login']) ?>">
                        Login
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= Url::to(['/site/signup']) ?>">
                        Signup
                    </a>
                </li>

            <?php else: ?>

                <li class="px-3 py-2">
                    <div class="d-flex align-items-center">
                        <img src="<?= $avatarUrl ?>"
                             class="rounded-circle me-2"
                             width="50"
                             height="50"
                             style="object-fit:cover;">
                        <div>
                            <div class="fw-semibold">
                                <?= Html::encode($user->username) ?>
                            </div>
                            <div class="text-muted small">
                                <?= Html::encode($user->email) ?>
                            </div>
                        </div>
                    </div>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <a class="dropdown-item" href="<?= Url::to(['managment/profile']) ?>">
                        My Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= Url::to(['managment/mytasks']) ?>">
                        My Tasks
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <?= Html::beginForm(['/site/logout'], 'post') ?>
                        <button class="dropdown-item text-danger">
                            Logout
                        </button>
                    <?= Html::endForm() ?>
                </li>

            <?php endif; ?>

        </ul>

    </div>

</div>
