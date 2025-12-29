<?php
use yii\helpers\Html;
?>

<h2>Hello <?= Html::encode($assignee->username) ?> 👋</h2>

<p>
You have been assigned a task by
<strong><?= Html::encode($assigner->username) ?></strong>.
</p>

<hr>

<p><strong>📌 Task:</strong> <?= Html::encode($task->title) ?></p>

<?php if ($task->description): ?>
<p><strong>📝 Description:</strong><br>
<?= nl2br(Html::encode($task->description)) ?></p>
<?php endif; ?>

<p><strong>⚡ Priority:</strong> <?= ucfirst($task->priority) ?></p>

<?php if ($task->due_date): ?>
<p><strong>📅 Due Date:</strong>
<?= Yii::$app->formatter->asDate($task->due_date) ?></p>
<?php endif; ?>

<hr>

<p>
👉 <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['task/view', 'id' => $task->id]) ?>">
View Task
</a>
</p>

<p style="color:#777;font-size:12px">
This is an automated notification.
</p>
