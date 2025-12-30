<?php
use yii\widgets\ActiveForm;
?>

<h2>Create New Team</h2>

<?php $form = ActiveForm::begin([
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'errorOptions' => ['class' => 'text-danger small'],
                ],
            ]); ?>

<?= $form->field($model,'name'); ?>
<?= $form->field($model,'description')->textarea(); ?>

<button class="btn btn-primary">Create</button>

<?php \yii\widgets\ActiveForm::end(); ?>
