<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Board $model */

$this->title = 'Update Board: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Boards', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= Html::encode($this->title) ?></h5>
            <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>

        <div class="card-body pt-3">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>

    </div>

</div>
