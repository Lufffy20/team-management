<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Task $model */
/** @var array $users */

?>

<div class="container-fluid py-4">

    <div class="row justify-content-center">

        <div class="col-lg-10">

            <div class="card shadow-sm" style="border-radius: 12px; padding:20px;">

                <h3 class="card-header text-center fw-bold py-3" style="font-size: 26px;">
                    <?= $model->isNewRecord ? 'Create Task' : 'Update Task' ?>
                </h3>

                <div class="card-body px-4 py-4">

                    <?php $form = ActiveForm::begin([
                        'fieldConfig' => [
                            'labelOptions'  => ['class' => 'form-label fw-semibold'],
                            'errorOptions'  => ['class' => 'text-danger mt-1'],
                            'inputOptions'  => ['class' => 'form-control form-control-lg'],
                        ],
                    ]); ?>

                    <div class="row">

                        <!-- Title -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'title', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-task"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->textInput(['placeholder' => 'Enter task title']) ?>
                        </div>

                        <!-- Priority -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'priority', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-flag"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->dropDownList([
                                'low'    => 'Low',
                                'medium' => 'Medium',
                                'high'   => 'High'
                            ], ['prompt' => 'Select Priority']) ?>
                        </div>

                    </div>


                    <div class="row">

                        <!-- Status -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'status', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-check-circle"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->dropDownList([
                                'todo'        => 'To-Do',
                                'in_progress' => 'In Progress',
                                'done'        => 'Done',
                                'archived'    => 'Archived'
                            ], ['prompt' => 'Select Status']) ?>
                        </div>

                        <!-- Assigned To -->
                        <div class="col-md-6">
                            <?= $form->field($model, 'assigned_to', [
                                'template' => '
                                    {label}
                                    <div class="input-group mb-4 input-group-lg">
                                        <span class="input-group-text"><i class="bx bx-user"></i></span>
                                        {input}
                                    </div>
                                    {error}
                                ',
                            ])->dropDownList($users, ['prompt' => 'Select User']) ?>
                        </div>

                    </div>


                    <!-- DESCRIPTION -->
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'description', [
                                'template' => '
                                    {label}
                                    {input}
                                    {error}
                                ',
                            ])->textarea(['rows' => 4, 'class' => 'form-control form-control-lg mb-4', 'placeholder' => 'Enter task description']) ?>
                        </div>
                    </div>


                    <div class="row">

    <!-- Due Date -->
    <div class="col-md-6">
        <?= $form->field($model, 'due_date', [
            'template' => '
                {label}
                <div class="input-group mb-4 input-group-lg">
                    <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                    {input}
                </div>
                {error}
            ',
        ])->input('date') ?>
    </div>

    <!-- Reminder (TEMP – for testing) -->
    <div class="col-md-6">
        <?= $form->field($model, 'last_reminder_at', [
            'template' => '
                {label}
                <div class="input-group mb-4 input-group-lg">
                    <span class="input-group-text">
                        <i class="bx bx-bell"></i>
                    </span>
                    {input}
                </div>
                {error}
            ',
        ])->dropDownList(
            [
                ''    => 'NULL (No Reminder)',
                'now' => 'Set Reminder (Now)',
            ],
            ['prompt' => 'Select Reminder']
        ) ?>
    </div>

</div>

                    


                    <!-- SUBMIT BUTTON -->
                    <div class="text-center mt-4">
                        <?= Html::submitButton(
                            $model->isNewRecord ? 'Create Task' : 'Update Task',
                            ['class' => 'btn btn-primary btn-lg px-5', 'style' => 'font-size:18px;']
                        ) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>

            </div>

        </div>

    </div>

</div>
