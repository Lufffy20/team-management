<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\TaskSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'My Tasks';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><?= Html::encode($this->title) ?></h4>

        <a href="<?= Url::to(['create']) ?>" class="btn btn-primary btn-sm">
            <i class="bx bx-plus"></i> Create Task
        </a>
    </div>

    <div class="card p-3">

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => null, // My Tasks me filter ki zarurat nahi
            'columns' => [

                ['class' => 'yii\grid\SerialColumn'],

                'title',
                'priority',
                'due_date',

                [
                    'attribute' => 'status',
                    'value' => function($model){
                        return ucfirst(str_replace('_', ' ', $model->status));
                    },
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {delete}',
                ],
            ],
        ]); ?>

    </div>
</div>
