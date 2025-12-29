<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\TeamMember $model */

$this->title = 'Create Team Member';
$this->params['breadcrumbs'][] = ['label' => 'Team Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="team-member-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
