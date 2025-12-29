<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Signup';

?>

<!-- Start Signup Section -->
<div class="untree_co-section">
    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-6 col-lg-5">

                <div class="p-4 border rounded shadow-sm bg-white">

                    <h2 class="text-center mb-4"><?= Html::encode($this->title) ?></h2>

                    <!-- 🔥 FLASH MESSAGE FOR TEST -->
                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success text-center">
                            <?= Yii::$app->session->getFlash('success') ?>
                        </div>
                    <?php endif; ?>

                    <p class="text-center text-muted mb-4">
                        Create your account by filling the form below.
                    </p>

                    <?php $form = ActiveForm::begin([
                        'id' => 'form-signup',
                        'options' => ['class' => ''],
                    ]); ?>

                    <!-- First Name -->
                    <?= $form->field($model, 'first_name')->textInput([
                        'class' => 'form-control',
                        'placeholder' => 'Enter your first name',
                        'autofocus' => true
                    ])->label('First Name', ['class' => 'text-black fw-bold']) ?>

                    <!-- Last Name -->
                    <?= $form->field($model, 'last_name')->textInput([
                        'class' => 'form-control',
                        'placeholder' => 'Enter your last name',
                    ])->label('Last Name', ['class' => 'text-black fw-bold']) ?>

                    <!-- Username -->
                    <?= $form->field($model, 'username')->textInput([
                        'class' => 'form-control',
                        'placeholder' => 'Enter your username',
                    ])->label('Username', ['class' => 'text-black fw-bold']) ?>

                    <!-- Email -->
                    <?= $form->field($model, 'email')->textInput([
                        'class' => 'form-control',
                        'placeholder' => 'Enter your email'
                    ])->label('Email', ['class' => 'text-black fw-bold']) ?>

                    <!-- Password -->
                    <?= $form->field($model, 'password')->passwordInput([
                        'class' => 'form-control',
                        'placeholder' => 'Create a password'
                    ])->label('Password', ['class' => 'text-black fw-bold']) ?>

                    <!-- Confirm Password -->
                    <?= $form->field($model, 'confirm_password')->passwordInput([
                        'class' => 'form-control',
                        'placeholder' => 'Re-enter your password'
                    ])->label('Confirm Password', ['class' => 'text-black fw-bold']) ?>

                    <!-- Submit Button -->
                    <div class="d-grid mt-3">
                        <?= Html::submitButton('Signup', [
                            'class' => 'btn btn-primary',
                            'name' => 'signup-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                    <div class="text-center mt-4">
                        <p class="text-muted small">
                            Already have an account?
                            <?= Html::a('Login here', ['site/login']) ?>
                        </p>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>
<!-- End Signup Section -->
