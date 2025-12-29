<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>

<div class="modal-header">
    <h5 class="modal-title">Edit Task</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<?php $form = ActiveForm::begin([
    'id' => 'taskUpdateForm',
    'action' => ['/task/update-ajax', 'id' => $model->id],
    'options' => ['enctype' => 'multipart/form-data'],
]); ?>

<div class="modal-body">

    <?= $form->field($model, 'title')->textInput() ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
    <?= $form->field($model, 'priority')->dropDownList(\common\models\Task::priorities()) ?>
    <?= $form->field($model, 'status')->dropDownList(\common\models\Task::statuses()) ?>
    <?= $form->field($model, 'due_date')->input('date') ?>

    <?= $form->field($model, 'assignee_id')
        ->dropDownList($users, ['prompt' => 'Select User'])
        ->label('Assign To') ?>

</div>

<div class="modal-footer d-flex justify-content-between">

    <button type="button"
            class="btn btn-danger"
            onclick="deleteTask(<?= $model->id ?>)">
        🗑 Delete
    </button>

    <div>
        <button type="submit" class="btn btn-success">💾 Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ Close</button>
    </div>

</div>

<?php ActiveForm::end(); ?>
