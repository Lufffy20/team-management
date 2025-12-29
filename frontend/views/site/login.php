<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use common\widgets\Alert;
use frontend\assets\AppAsset;
AppAsset::register($this);
?>


<!-- Start Login Section -->
<div class="untree_co-section">
    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-6 col-lg-5">

                <div class="p-4 border rounded shadow-sm bg-white">

                    <h2 class="text-center mb-4"><?= Html::encode($this->title) ?></h2>
                    <?= Alert::widget() ?>

                    <p class="text-center text-muted mb-4">
                        Please enter your credentials to login.
                    </p>

                    <?php $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'options' => ['class' => ''],
                    ]); ?>



                    <!-- Username -->
                    <?= $form->field($model, 'username')->textInput([
                        'class' => 'form-control',
                        'placeholder' => 'Enter your username',
                        'autofocus' => true
                    ])->label('Username', ['class' => 'text-black fw-bold']) ?>

                    <!-- Password -->
                    <?= $form->field($model, 'password')->passwordInput([
                        'class' => 'form-control',
                        'placeholder' => 'Enter your password'
                    ])->label('Password', ['class' => 'text-black fw-bold']) ?>

                    <!-- Remember Me -->
                    <div class="mb-3">
                        <?= $form->field($model, 'rememberMe')->checkbox() ?>
                    </div>

                    <!-- Links -->
                    <div class="mt-3 mb-4">

                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-key-fill text-primary me-2"></i>
                            <span class="text-muted small">
                                Forgot your password?
                                <?= Html::a('Reset it here', ['site/request-password-reset'], ['class' => 'fw-semibold']) ?>
                            </span>
                        </div>

                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-plus-fill text-success me-2"></i>
                            <span class="text-muted small">
                                Don't have an account?
                                <?= Html::a('Sign up now', ['site/signup'], ['class' => 'fw-semibold']) ?>
                            </span>
                        </div>


                        <div class="d-flex align-items-center">
                            <i class="bi bi-envelope-check-fill text-success me-2"></i>
                            <span class="text-muted small">
                                Didn’t receive a verification email?
                                <?= Html::a('Resend verification', ['site/resend-verification-email'], ['class' => 'fw-semibold']) ?>
                            </span>
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <?= Html::submitButton('Login', [
                            'class' => 'btn btn-primary',
                            'name' => 'login-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>

            </div>

        </div>

    </div>
</div>
<!-- End Login Section -->
