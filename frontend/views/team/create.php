<h2>Create New Team</h2>

<?php $form = \yii\widgets\ActiveForm::begin(); ?>

<?= $form->field($model,'name'); ?>
<?= $form->field($model,'description')->textarea(); ?>

<button class="btn btn-primary">Create</button>

<?php \yii\widgets\ActiveForm::end(); ?>
