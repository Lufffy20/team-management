<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\User $model */

$this->title = "User Details (#{$model->id})";
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);
?>

<div class="container mt-4">

    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm" style="border-radius: 14px;">

                <!-- Header -->
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h4 class="fw-bold mb-0"><?= Html::encode($this->title) ?></h4>

                    <div>
                        <?= Html::a('Update', ['update', 'id' => $model->id], [
                            'class' => 'btn btn-primary me-2'
                        ]) ?>

                        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this user?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>

                <!-- Body -->
                <div class="card-body px-4 py-4">

                    <div class="row">

                        <!-- AVATAR -->
                        <div class="col-md-3 text-center mb-4">
                            <?php if ($model->avatar): ?>
                                <img src="/uploads/avatars/<?= $model->avatar ?>" 
                                    class="rounded"
                                    style="width: 130px; height: 130px; object-fit: cover; border:1px solid #ccc;">
                            <?php else: ?>
                                <div class="bg-light border rounded d-flex justify-content-center align-items-center"
                                     style="width:130px;height:130px;">
                                     <i class="bx bx-user fs-1"></i>
                                </div>
                            <?php endif; ?>

                            <p class="text-muted mt-2 fw-semibold">User Avatar</p>
                        </div>

                        <!-- DETAILS -->
                        <div class="col-md-9">

                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-bordered table-striped'],
                                'attributes' => [
                                    'id',
                                    'first_name',
                                    'last_name',
                                    'username',
                                    'email:email',
                                    [
                                        'attribute' => 'role',
                                        'value' => $model->role == 1 ? 'Admin' : 'User',
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'value' => function ($m) {
                                            return match ($m->status) {
                                                10 => 'Active',
                                                9  => 'Inactive',
                                                default => 'Deleted',
                                            };
                                        }
                                    ],
                                    [
                                        'attribute' => 'created_at',
                                        'value' => date("d M Y, h:i A", $model->created_at),
                                    ],
                                    [
                                        'attribute' => 'updated_at',
                                        'value' => date("d M Y, h:i A", $model->updated_at),
                                    ],
                                ],
                            ]) ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

</div>
