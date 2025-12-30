<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
?>

<?php $form = ActiveForm::begin([
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'errorOptions' => ['class' => 'text-danger small'],
                ],
            ]); ?>

<h4 class="fw-bold mb-3">
    <?= $model->isNewRecord ? 'Create Task' : 'Update Task' ?>
</h4>

<?= $form->field($model, 'title') ?>

<?= $form->field($model, 'description')->textarea() ?>

<?= $form->field($model, 'board_id')->dropDownList(
    ArrayHelper::map($boards, 'id', 'title'),
    ['prompt' => 'Select Board']
) ?>



<?= $form->field($model, 'priority')->dropDownList([
    'low' => 'Low',
    'medium' => 'Medium',
    'high' => 'High'
]) ?>

<?= $form->field($model, 'due_date')->input('date') ?>

<?= $form->field($model, 'status')->dropDownList([
    'todo' => 'To Do',
    'in_progress' => 'In Progress',
    'done' => 'Done'
]) ?>

<div class="mt-3">
    <?= Html::submitButton('Save Task', ['class' => 'btn btn-primary']) ?>
    <a href="<?= Yii::$app->request->referrer ?>" class="btn btn-light">Cancel</a>
</div>

<?php ActiveForm::end(); ?>
