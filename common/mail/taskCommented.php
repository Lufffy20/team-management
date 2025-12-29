<?php
use yii\helpers\Html;
?>

<h3>Hello <?= Html::encode($task->assignee->username) ?> 👋</h3>

<p>
<strong><?= Html::encode($commenter->username) ?></strong>
commented on the task assigned to you.
</p>

<hr>

<p><strong>📌 Task:</strong> <?= Html::encode($task->title) ?></p>

<p><strong>💬 Comment:</strong></p>
<div style="padding:10px;border-left:3px solid #0d6efd;background:#f8f9fa">
    <?= nl2br(Html::encode($comment->comment)) ?>
</div>

<hr>

<p>
👉 <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['task/update', 'id' => $task->id]) ?>">
View Task
</a>
</p>

<p style="color:#777;font-size:12px">
You are receiving this email because a comment was added on your task.
</p>
