<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Task $model */
/** @var array $users */

$this->title = 'Create Task';
$this->params['breadcrumbs'][] = ['label' => 'Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container mt-4">

    <div class="task-create">

        <?= $this->render('_form', [
            'model' => $model,
            'users' => $users, // FIX: Pass users dropdown list
        ]) ?>

    </div>

</div>
