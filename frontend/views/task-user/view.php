<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Task $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container py-4">  <!-- 🔥 Container Added Here -->

    <div class="task-view card shadow-sm p-4 rounded-3"> <!-- Optional better UI wrapper -->

        <h2 class="mb-3"><?= Html::encode($this->title) ?></h2>

        <p>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger ms-2',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        </p>

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'user_id',
                'title',
                'description:ntext',
                'status',
                'assigned_to',
                'priority',
                'due_date',
                'assignee_id',
                'sort_order',
                'created_by',
                'created_at',
                'updated_at',
                'team_id',
                'board_id',
                'position',
            ],
        ]) ?>

    </div>

</div> <!-- container end -->
