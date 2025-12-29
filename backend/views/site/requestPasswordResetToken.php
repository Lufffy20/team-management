<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Request password reset';
?>

<h1><?= Html::encode($this->title) ?></h1>

<p>Please enter your email. A reset link will be sent.</p>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

<div class="form-group">
    <?= Html::submitButton('Send', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
