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

    <img
        src="<?= Yii::$app->avatar->get($model) ?>"
        alt="User Avatar"
        class="rounded shadow-sm"
        width="130"
        height="130"
        style="object-fit: cover; border:1px solid #ccc;"
    >

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
            'label' => 'Teams',
            'format' => 'raw',
            'value' => function ($model) {

                if (empty($model->teams)) {
                    return '<span class="badge bg-secondary">No Team</span>';
                }

                $badges = [];

                foreach ($model->teams as $team) {
                    $badges[] = '<span class="badge bg-info me-1">'
                        . Html::encode($team->name)
                        . '</span>';
                }

                return implode(' ', $badges);
            },
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
