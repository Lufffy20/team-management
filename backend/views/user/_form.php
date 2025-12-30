<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="container-fluid py-4">

    <div class="row justify-content-center">

        <div class="col-12">

            <div class="card shadow-sm" style="padding: 20px; border-radius: 12px;">

                <h3 class="card-header text-center fw-bold py-3" style="font-size: 26px;">
                    <?= $model->isNewRecord ? 'Create User' : 'Update User' ?>
                </h3>

                <div class="card-body px-4 py-4">

                    <?php $form = ActiveForm::begin([
                        'options' => ['enctype' => 'multipart/form-data'],
                        'fieldConfig' => [
                            'labelOptions' => ['class' => 'form-label fw-semibold'],
                            'template' => "{label}\n{input}\n{error}",
                            'errorOptions' => ['class' => 'text-danger small'],
                            'inputOptions' => ['class' => 'form-control form-control-lg'],
                        ],
                    ]); ?>


                    <div class="row">
                        <!-- First Name -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'first_name', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-user"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->textInput(['placeholder' => 'Enter first name']) ?>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'last_name', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-user-circle"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->textInput(['placeholder' => 'Enter last name']) ?>
                        </div>
                    </div>


                    <div class="row">
                        <!-- Username -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'username', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->textInput(['placeholder' => 'Enter username']) ?>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'email', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->input('email', ['placeholder' => 'example@example.com']) ?>
                        </div>
                    </div>


                    <div class="row">
                        <!-- Password -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'password', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-lock"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->passwordInput([
                                'placeholder' => $model->isNewRecord 
                                    ? 'Enter password' 
                                    : 'Leave blank to keep old password'
                            ]) ?>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'confirm_password', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->passwordInput(['placeholder' => 'Re-enter password']) ?>
                        </div>
                    </div>


                    <div class="row">
                        <!-- Role -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'role', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-user-pin"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->dropDownList([
                                1 => 'Admin',
                                0 => 'User',
                            ], ['prompt' => 'Select Role']) ?>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'status', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-check-shield"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->dropDownList([
                                10 => 'Active',
                                9  => 'Inactive',
                                0  => 'Deleted',
                            ], ['prompt' => 'Select Status']) ?>
                        </div>
                    </div>


                    <!-- AVATAR UPLOAD -->
                    <div class="row">
                        <div class="col-md-12">

                            <?= $form->field($model, 'avatarFile', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-image"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->fileInput() ?>

                            <?php if (!$model->isNewRecord && $model->avatar): ?>
                                <div class="text-center mb-3">
                                    <label class="fw-bold">Current Avatar:</label><br>
                                    <img src="/uploads/avatars/<?= $model->avatar ?>" 
                                        width="120"
                                        style="border-radius: 8px; border:1px solid #ccc;">
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>


                    <div class="text-center mt-5">
                        <?= Html::submitButton(
                            $model->isNewRecord ? 'Create User' : 'Update User',
                            ['class' => 'btn btn-primary btn-lg px-5 py-2', 'style' => 'font-size:18px;']
                        ) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>

            </div>
        </div>

    </div>
</div>
