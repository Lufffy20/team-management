<?php
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = "Recent Team Activity";
?>

<h3 class="fw-bold mb-4"><?= $this->title ?></h3>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => false,

            'tableOptions' => ['class' => 'table table-hover mb-0'],

            'columns' => [

                [
                    'label' => 'User',
                    'format' => 'raw',
                    'value' => function ($task) {
                        $username = $task->updatedBy->username ?? $task->createdBy->username ?? "Unknown";
                        return "<b>{$username}</b>";
                    }
                ],

                [
                    'label' => 'Task',
                    'format' => 'raw',
                    'value' => fn($task) => $task->title,
                ],

                [
                    'label' => 'Board',
                    'value' => fn($task) => $task->board->title ?? '—',
                ],

                [
                    'label' => 'Team',
                    'value' => fn($task) => $task->board->team->name ?? '—',
                ],

                [
    'label' => 'Updated',
    'format' => 'raw',
    'value' => function($task) {
        $relative = Yii::$app->formatter->asRelativeTime($task->updated_at);
        $date = Yii::$app->formatter->asDatetime($task->updated_at, 'php:d M Y, h:i A');

        return "<div>{$relative}<br><small class='text-muted'>{$date}</small></div>";
    },
],


            ]
        ]); ?>

    </div>
</div>
