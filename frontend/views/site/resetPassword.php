<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Reset Password';
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
                        Please choose your new password below.
                    </p>

                    <?php $form = ActiveForm::begin([
                        'id' => 'reset-password-form',
                    ]); ?>

                    <!-- Password Field -->
                    <?= $form->field($model, 'password')->passwordInput([
                        'class' => 'form-control',
                        'placeholder' => 'Enter new password',
                        'autofocus' => true
                    ])->label('New Password', ['class' => 'text-black fw-bold']) ?>

                    <!-- Submit Button -->
                    <div class="d-grid mt-3">
                        <?= Html::submitButton('Reset Password', [
                            'class' => 'btn btn-primary'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>

            </div>
        </div>

    </div>
</div>
