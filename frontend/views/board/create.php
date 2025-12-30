<?php
use yii\widgets\ActiveForm;
?>

<h3>Create Board</h3>

<?php $form = ActiveForm::begin([
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'errorOptions' => ['class' => 'text-danger small'],
                ],
            ]); ?>

<?= $form->field($model, 'title') ?>
<?= $form->field($model, 'description')->textarea() ?>

<button class="btn btn-primary">Create</button>

<?php \yii\widgets\ActiveForm::end(); ?>
