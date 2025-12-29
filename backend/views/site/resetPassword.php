<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Reset password';
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'password')->passwordInput(['autofocus' => true]) ?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
