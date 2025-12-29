<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Board $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Boards', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card mb-4">

        <!-- Header -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= Html::encode($this->title) ?></h5>
            <div>
                <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary me-2']) ?>
                <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this board?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('Back', ['index'], ['class' => 'btn btn-secondary ms-2']) ?>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body">

            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-bordered table-striped'],
                'attributes' => [
                    'id',
                    'title',
                    [
                        'attribute' => 'description',
                        'format' => 'ntext',
                        'value' => $model->description ?: '<i>No description</i>',
                    ],
                    [
                        'attribute' => 'created_by',
                        'value' => $model->created_by,
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => date('d M Y, h:i A', $model->created_at),
                    ],
                    'team_id',
                ],
            ]) ?>

        </div>

    </div>

</div>
