<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\TeamMembers $model */

$this->title = 'Update Team Member: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Team Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'View #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card shadow-sm border-0 mb-4">

        <!-- HEADER -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><?= Html::encode($this->title) ?></h5>
        </div>

        <div class="card-body">

            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>

        </div>

    </div>

</div>
