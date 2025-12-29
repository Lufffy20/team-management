<?php 
use yii\helpers\Html;
use yii\helpers\Url;

/** @var $model \common\models\Task */
?>

<div class="card shadow-sm border-0">
    <div class="card-body">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">
                <?= Html::encode($model->title) ?>
            </h4>

            <div class="d-flex gap-2">
                <a href="<?= Url::to(['managment/update-task', 'id' => $model->id]) ?>" 
                   class="btn btn-sm btn-primary">
                   Edit
                </a>

                <a href="<?= Url::to(['managment/delete-task', 'id' => $model->id]) ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Are you sure you want to delete this task?');">
                   Delete
                </a>
            </div>
        </div>

        <hr>

        <!-- DESCRIPTION -->
        <p class="text-muted" style="font-size:15px;">
            <?= nl2br(Html::encode($model->description)) ?>
        </p>

        <!-- TASK DETAILS -->
        <div class="mt-4">

            <div class="mb-2">
                <strong>Status:</strong>
                <?php 
                    $statuses = [
                        'todo' => 'secondary',
                        'in_progress' => 'primary',
                        'done' => 'success',
                    ];
                    $badge = $statuses[$model->status] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $badge ?>"><?= ucfirst($model->status) ?></span>
            </div>

            <div class="mb-2">
                <strong>Priority:</strong>
                <?php 
                    $priorityColors = [
                        'low' => 'success',
                        'medium' => 'warning',
                        'high' => 'danger',
                    ];
                    $pColor = $priorityColors[$model->priority] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $pColor ?>"><?= ucfirst($model->priority) ?></span>
            </div>

            <div class="mb-2">
                <strong>Due Date:</strong>
                <?= date("d M Y", strtotime($model->due_date)) ?>
            </div>

            <div class="mb-2">
                <strong>Assigned By:</strong>
                <?= Html::encode(
                    $model->creator->id === Yii::$app->user->id
                    ? 'You'
                    : ($model->creator->name ?? 'System')
                ) ?>
            </div>

            <div class="mb-2">
                <strong>Board:</strong>
                <?= Html::encode($model->board->title ?? 'N/A') ?>
            </div>


        <div class="mb-2">
            <strong>Team:</strong>
            <?= Html::encode($model->board->team->name ?? 'N/A') ?>
        </div>


        </div>

        <hr>

        <!-- BACK BUTTON -->
        <a href="<?= Url::to(['managment/mytasks']) ?>" class="btn btn-outline-secondary btn-sm">
            ← Back to My Tasks
        </a>

    </div>
</div>
