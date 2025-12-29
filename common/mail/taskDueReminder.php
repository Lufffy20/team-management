<?php
use yii\helpers\Html;
?>

<h2>Hello <?= Html::encode($assignee->username) ?> 👋</h2>

<p>
This is a reminder for a task assigned to you.
</p>

<hr>

<p><strong>📌 Task:</strong> <?= Html::encode($task->title) ?></p>

<?php if ($task->description): ?>
<p><strong>📝 Description:</strong><br>
<?= nl2br(Html::encode($task->description)) ?></p>
<?php endif; ?>

<p>
<strong>📅 Due Date:</strong>
<span style="color:red">
<?= Yii::$app->formatter->asDate($task->due_date) ?>
</span>
</p>

<hr>

<p style="color:#777;font-size:12px">
This is an automated due-date reminder.
</p>
