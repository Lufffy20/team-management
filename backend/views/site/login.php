<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use common\widgets\Alert;

$this->title = 'Login';
?>
<div class="site-login">
    <div class="mt-5 offset-lg-3 col-lg-6">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Alert::widget() ?>

        <p>Please fill out the following fields to login:</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

            <?= $form->field($model, 'password')->passwordInput() ?>

            <?= $form->field($model, 'rememberMe')->checkbox() ?>

            <div class="form-group">
                <?= Html::submitButton('Login', ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']) ?>
            </div>

            <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-key-fill text-primary me-2"></i>
                            <span class="text-muted small">
                                Forgot your password?
                                <?= Html::a('Reset it here', ['site/request-password-reset'], ['class' => 'fw-semibold']) ?>
                            </span>
                        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
