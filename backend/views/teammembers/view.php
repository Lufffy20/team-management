<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\TeamMembers $model */

$this->title = "Team Member #" . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Team Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card shadow-sm border-0 mb-4">

        <!-- HEADER -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><?= Html::encode($this->title) ?></h5>

            <div>
                <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary me-2']) ?>

                <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this item?',
                        'method' => 'post',
                    ],
                ]) ?>
            </div>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-striped table-bordered detail-view'],

                'attributes' => [
                    'id',
                    [
                        'attribute' => 'team_id',
                        'value' => $model->team->name ?? $model->team_id,
                        'label' => 'Team',
                    ],
                    [
                        'attribute' => 'user_id',
                        'value' => $model->user->username ?? $model->user_id,
                        'label' => 'User',
                    ],
                    [
                        'attribute' => 'role',
                        'value' => ucfirst($model->role),
                    ],
                ],
            ]) ?>

        </div>

    </div>

</div>
