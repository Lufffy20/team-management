<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Team $model */

$this->title = 'Create Team';
$this->params['breadcrumbs'][] = ['label' => 'Teams', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card">

        <div class="card-header">
            <h5 class="mb-0"><?= Html::encode($this->title) ?></h5>
        </div>

        <div class="card-body">

            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>

        </div>

    </div>

</div>
