<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Task $model */
/** @var array $users */

$this->title = 'Update Task: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="container mt-4">

    <div class="task-update">

        <?= $this->render('_form', [
            'model' => $model,
            'users' => $users,   // FIX: pass users dropdown list
        ]) ?>

    </div>

</div>
