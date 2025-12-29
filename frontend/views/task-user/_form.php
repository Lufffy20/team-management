<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\User;
use common\models\Board; // 🔥 required import for board list

/** @var yii\web\View $this */
/** @var common\models\Task $model */
/** @var yii\widgets\ActiveForm $form */

?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card shadow-sm">

        <div class="card-header">
            <h5 class="mb-0"><?= $model->isNewRecord ? 'Create Task' : 'Update Task' ?></h5>
        </div>

        <div class="card-body">

            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'status')->dropDownList([
                        'todo' => 'To Do',
                        'in_progress' => 'In Progress',
                        'done' => 'Done',
                        'archived' => 'Archived',
                    ], ['prompt' => 'Select Status']) ?>
                </div>
            </div>

            <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>

            <div class="row">

                <div class="col-md-4">
                    <?= $form->field($model, 'priority')->dropDownList([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High'
                    ], ['prompt' => 'Select Priority']) ?>
                </div>

                <div class="col-md-4">
                    <?= $form->field($model, 'due_date')->input('date') ?>
                </div>
            </div>

            <!-- 🔥 Board Select (Required for Kanban Appearance) -->
            <div class="row mt-2">
    <div class="col-md-12">
        <?= $form->field($model, 'board_id')->dropDownList(
            ArrayHelper::map(
                \common\models\Board::find()->where(['created_by' => Yii::$app->user->id])->all(),
                'id',
                'title'
            ),
            ['prompt' => 'Select Kanban Board']
        ) ?>
    </div>
</div>


            <!-- Auto fill — Hidden Field -->
            <?= $form->field($model, 'created_by')->hiddenInput(['value' => Yii::$app->user->id])->label(false) ?>

            <div class="text-end mt-3">
                <?= Html::submitButton($model->isNewRecord ? 'Create Task' : 'Update Task', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
