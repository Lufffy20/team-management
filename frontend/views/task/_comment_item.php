<?php
use yii\helpers\Html;
?>
<div class="mb-3">
    <strong><?= Html::encode($c->user->username) ?></strong>
    <div class="text-muted small">
        <?= Yii::$app->formatter->asRelativeTime($c->created_at) ?>
    </div>
    <div><?= nl2br(Html::encode($c->comment)) ?></div>
</div>
