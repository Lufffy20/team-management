<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Team;

$teams = ArrayHelper::map(Team::find()->all(), 'id', 'name');

?>

<div class="team-members-form">

    <?php $form = ActiveForm::begin([
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'errorOptions' => ['class' => 'text-danger small'],
                ],
            ]); ?>

    <!-- Team Dropdown -->
    <?= $form->field($model, 'team_id')->dropDownList($teams, ['prompt' => 'Select Team']) ?>

    <!-- Email field only show when creating -->
    <?php if ($model->isNewRecord): ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
    <?php endif; ?>

    <!-- Role Dropdown -->
    <?= $form->field($model, 'role')->dropDownList([
    'member' => 'Member',
    'admin' => 'Admin',
    'manager' => 'Manager',
], ['prompt' => 'Select Role']) ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
