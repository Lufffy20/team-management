<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Request Password Reset';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="untree_co-section">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">

                <div class="p-4 border rounded shadow-sm bg-white">

                    <!-- Title -->
                    <h2 class="text-center mb-3"><?= Html::encode($this->title) ?></h2>

                    <p class="text-center text-muted mb-4">
                        Enter your registered email and we’ll send you a password reset link.
                    </p>

                    <?php $form = ActiveForm::begin([
                        'id' => 'request-password-reset-form'
                    ]); ?>

                    <!-- Email Field -->
                    <?= $form->field($model, 'email')->textInput([
                        'class' => 'form-control',
                        'placeholder' => 'Enter your email',
                        'autofocus' => true
                    ])->label('Email Address', ['class' => 'text-black fw-bold']) ?>

                    <!-- Submit Button -->
                    <div class="d-grid mt-3">
                        <?= Html::submitButton('Send Reset Link', [
                            'class' => 'btn btn-primary'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>

            </div>
        </div>

    </div>
</div>
