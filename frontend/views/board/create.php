<h3>Create Board</h3>

<?php $form = \yii\widgets\ActiveForm::begin(); ?>

<?= $form->field($model, 'title') ?>
<?= $form->field($model, 'description')->textarea() ?>

<button class="btn btn-primary">Create</button>

<?php \yii\widgets\ActiveForm::end(); ?>
