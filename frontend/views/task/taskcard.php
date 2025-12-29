<?php
use common\models\Task;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var Task $model */

$priorityClass = [
    Task::PRIORITY_LOW    => 'bg-success-subtle border border-success text-success',
    Task::PRIORITY_MEDIUM => 'bg-warning-subtle border border-warning text-warning',
    Task::PRIORITY_HIGH   => 'bg-danger-subtle border border-danger text-danger',
];

$priorityLabel = Task::priorities()[$model->priority] ?? 'Medium';
$badgeClass    = $priorityClass[$model->priority] ?? $priorityClass[Task::PRIORITY_MEDIUM];

/* 🔥 GET FIRST IMAGE ATTACHMENT */
$coverImage = null;
if (!empty($model->attachments)) {
    foreach ($model->attachments as $a) {
        $ext = strtolower(pathinfo($a->file, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $coverImage = Yii::getAlias('@web/uploads/tasks/' . $a->file);
            break;
        }
    }
}
?>

<!-- ================== TASK CARD ================== -->
<div class="kanban-task task-item"
     id="taskCard<?= $model->id ?>"
     draggable="true"
     data-id="<?= $model->id ?>"
     onclick="window.location.href='<?= Url::to(['task/update', 'id' => $model->id]) ?>'"
     style="cursor:pointer; user-select:none;">

    <!-- 🔥 IMAGE ON TOP (NO CROP) -->
    <?php if ($coverImage): ?>
        <div class="task-cover">
            <img src="<?= $coverImage ?>" alt="Task Image">
        </div>
    <?php endif; ?>

    <div class="task-content">

        <div class="task-title fw-semibold">
            <?= Html::encode($model->title) ?>
        </div>

        <div class="task-meta d-flex justify-content-between align-items-center mt-2">
            <span class="badge-priority px-2 py-1 rounded small <?= $badgeClass ?>">
                <?= $priorityLabel ?>
            </span>

            <span class="text-muted small opacity-75">
                <?= $model->due_date
                    ? "Due: " . Yii::$app->formatter->asDate($model->due_date, 'php:d M')
                    : "No due" ?>
            </span>
        </div>

        <?php if ($model->assignee): ?>
            <div class="task-assignee mt-2">
                <div class="assignee-avatar">
                    <?= strtoupper(substr($model->assignee->username, 0, 1)) ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

