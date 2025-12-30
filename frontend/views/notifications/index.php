<?php
use yii\helpers\Html;
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Notifications</h4>
        <?= Html::a(
            'Mark all as read',
            ['mark-all-read'],
            ['class' => 'btn btn-sm btn-outline-secondary']
        ) ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-bell-slash fs-1"></i>
            <p>No notifications yet</p>
        </div>
    <?php endif; ?>

    <?php foreach ($notifications as $n): ?>
        <div class="card mb-2 <?= $n->is_read ? '' : 'border-primary' ?>">
            <div class="card-body">
                <strong><?= Html::encode($n->title) ?></strong>
                <p class="mb-1 text-muted"><?= Html::encode($n->message) ?></p>
                <small class="text-secondary">
                    <?= Yii::$app->formatter->asRelativeTime($n->created_at) ?>
                </small>
            </div>
        </div>
    <?php endforeach; ?>

</div>
