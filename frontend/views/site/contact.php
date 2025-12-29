<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Contact';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Contact</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success">
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger">
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>

        <div class="mb-3">
            <?= $form->field($model, 'name')->textInput(['placeholder' => 'Your Name']) ?>
        </div>

        <div class="mb-3">
            <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email Address']) ?>
        </div>

        <div class="mb-3">
            <?= $form->field($model, 'subject')->textInput(['placeholder' => 'Subject']) ?>
        </div>

        <div class="mb-3">
            <?= $form->field($model, 'body')->textarea(['rows' => 5, 'placeholder' => 'Message']) ?>
        </div>

        <div class="mb-4">
            <?= $form->field($model, 'verifyCode')->textInput(['placeholder' => 'Verification Code']) ?>
        </div>

        <div class="d-flex justify-content-end">
            <?= Html::submitButton('Send Message', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
