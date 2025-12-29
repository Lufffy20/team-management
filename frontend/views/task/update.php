<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\widgets\Alert;
use yii\helpers\Url;
use frontend\assets\AppAsset;

AppAsset::register($this);

/** @var common\models\Task $model */

$assigneeList = [];

if ($model->board && $model->board->team && $model->board->team->members) {
    $role = $model->board->getUserRole(Yii::$app->user->id);

    foreach ($model->board->team->members as $m) {
        if ($role !== 'manager' && $m->role === 'manager') {
            continue;
        }

        $label = $m->user->username ?? 'User';

        if ($m->user_id == Yii::$app->user->id) {
            $label .= ' (You)';
        }

        if ($m->role === 'manager') {
            $label .= ' (Manager)';
        }

        $assigneeList[$m->user_id] = $label;
    }
}
?>

<div class="task-edit-box">

<h4 class="fw-bold mb-3">Edit Task</h4>
<?= Alert::widget() ?>

<!-- ================= TASK UPDATE FORM ================= -->
<?php $form = ActiveForm::begin([
    'action' => ['task/update', 'id' => $model->id],
    'method' => 'post',
]); ?>

<?= $form->field($model, 'title')->textInput() ?>
<?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'priority')->dropDownList(\common\models\Task::priorities()) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'status')->dropDownList(\common\models\Task::statuses()) ?>
    </div>
</div>

<?= $form->field($model, 'due_date')->input('date') ?>
<?= $form->field($model, 'assignee_id')->dropDownList($assigneeList, ['prompt' => 'Select Member']) ?>

<?php if ($model->assignee): ?>
<div class="task-assignee mt-2 mb-2 d-flex align-items-center gap-2">
    <div class="assignee-avatar">
        <?= strtoupper(substr($model->assignee->username, 0, 1)) ?>
    </div>
    <span class="assignee-name"><?= Html::encode($model->assignee->username) ?></span>
</div>
<?php endif; ?>

<div class="d-flex gap-2 mb-3">
    <?= Html::submitButton('Save Changes', ['class' => 'btn btn-success']) ?>
    <?= Html::a('Delete Task', ['task/delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data' => ['confirm' => 'Are you sure?', 'method' => 'post']
    ]) ?>
</div>

<?php ActiveForm::end(); ?>

<hr>

<!-- ================= TABS ================= -->
<ul class="nav nav-tabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#attachments">📎 Attachments</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#comments">
            💬 Comments <?= count($model->comments) ? '(' . count($model->comments) . ')' : '' ?>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#subtasks">✅ Subtasks</button>
    </li>
    <li class="nav-item">
        <button type="button" class="nav-link"
        data-bs-toggle="tab"
        data-bs-target="#activity">
    🕒 Activity
</button>

    </li>
</ul>

<div class="tab-content border border-top-0 p-3">

<!-- ================= ATTACHMENTS ================= -->
<div class="tab-pane fade show active" id="attachments">

<?php if ($model->attachments): ?>
    <?php foreach ($model->attachments as $a): ?>
        <?php
            $filePath = Yii::getAlias('@web/uploads/tasks/' . $a->file);
            $ext      = strtolower(pathinfo($a->file, PATHINFO_EXTENSION));
            $isImage  = in_array($ext, ['jpg','jpeg','png','gif','webp']);
            $modalId  = 'viewAttachmentModal' . $a->id;
        ?>

        <div class="attachment-item d-flex justify-content-between align-items-center mb-2">

            <!-- LEFT -->
            <?php if ($isImage): ?>
                <div class="d-flex align-items-center gap-2">
                    <img src="<?= $filePath ?>"
                         style="width:42px;height:42px;object-fit:cover;border-radius:6px;">
                    <span><?= Html::encode($a->file) ?></span>
                </div>
            <?php else: ?>
                <a href="<?= $filePath ?>" target="_blank">
                    📎 <?= Html::encode($a->file) ?>
                </a>
            <?php endif; ?>

            <!-- RIGHT ACTIONS -->
            <div class="d-flex gap-1">

                <!-- 👁 VIEW -->
                <?php if ($isImage): ?>
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal"
                            data-bs-target="#<?= $modalId ?>">
                        View
                    </button>
                <?php else: ?>
                    <a href="<?= $filePath ?>"
                       target="_blank"
                       class="btn btn-sm btn-outline-secondary">
                        View
                    </a>
                <?php endif; ?>

                <!-- 🗑 DELETE -->
                <?= Html::a('Delete', ['task/delete-attachment', 'id' => $a->id], [
                    'class' => 'btn btn-sm btn-outline-danger',
                    'data'  => ['confirm' => 'Delete attachment?', 'method' => 'post']
                ]) ?>

            </div>
        </div>

        <!-- IMAGE PREVIEW MODAL -->
        <?php if ($isImage): ?>
        <div class="modal fade" id="<?= $modalId ?>" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title"><?= Html::encode($a->file) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body text-center">
                        <img src="<?= $filePath ?>" class="img-fluid rounded">
                    </div>

                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php endforeach; ?>
<?php else: ?>
    <div class="text-muted small">No attachments</div>
<?php endif; ?>

<hr>

<?php
$remaining = 5 - count($model->attachments);
?>

<!-- ================= UPLOAD FORM (SEPARATE) ================= -->
<?php $uploadForm = ActiveForm::begin([
    'action' => ['task/upload-attachment', 'id' => $model->id],
    'method' => 'post',
    'options' => ['enctype' => 'multipart/form-data']
]); ?>

<input type="file"
       name="Task[attachmentFiles][]"
       multiple
       class="form-control"
       <?= $remaining <= 0 ? 'disabled' : '' ?>>

<small class="text-muted d-block mt-1">
    Max 5 attachments allowed. <?= $remaining ?> remaining.
</small>

<button type="submit"
        class="btn btn-primary btn-sm mt-2"
        <?= $remaining <= 0 ? 'disabled' : '' ?>>
    Upload Attachment
</button>

<?php ActiveForm::end(); ?>

</div>

<!-- ================= COMMENTS ================= -->
<div class="tab-pane fade" id="comments">

<?php $commentForm = ActiveForm::begin([
    'action' => ['task/add-comment', 'id' => $model->id],
    'method' => 'post'
]); ?>

<textarea name="comment"
          class="form-control mb-2"
          rows="2"
          placeholder="Write a comment..."></textarea>

<button type="submit" class="btn btn-primary btn-sm">
    Post Comment
</button>

<?php ActiveForm::end(); ?>

<hr>

<?php if ($model->comments): ?>
    <?php foreach ($model->comments as $c): ?>
        <div class="mb-3">
            <strong><?= Html::encode($c->user->username) ?></strong>
            <div class="text-muted small">
                <?= Yii::$app->formatter->asRelativeTime($c->created_at) ?>
            </div>
            <div><?= nl2br(Html::encode($c->comment)) ?></div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-muted small">No comments yet</div>
<?php endif; ?>

</div>

<!-- ================= SUBTASKS ================= -->
<div class="tab-pane fade" id="subtasks">

<div class="input-group mb-3">
    <input type="text" id="newSubtaskInput" class="form-control" placeholder="Add subtask">
    <button type="button" class="btn btn-primary" id="addSubtaskBtn" data-task="<?= $model->id ?>">
        Add
    </button>
</div>

<?php foreach ($model->subtasks as $s): ?>

<div class="d-flex align-items-center justify-content-between subtask-item mb-2">

    <div class="d-flex align-items-center gap-2">

        <input type="checkbox"
               class="toggle-subtask"
               data-subtask-id="<?= $s->id ?>"
               <?= $s->is_done ? 'checked' : '' ?>>

        <span class="subtask-title <?= $s->is_done ? 'line-through text-muted' : '' ?>">
            <?= Html::encode($s->title) ?>
        </span>

    </div>

    <button type="button"
            class="btn btn-sm btn-outline-danger delete-subtask"
            data-id="<?= $s->id ?>">
        ✖
    </button>

</div>

<?php endforeach; ?>

</div>

<!-- ================= ACTIVITY ================= -->
<div class="tab-pane fade" id="activity">
        <?php if (!empty($activities)): ?>
            <ul class="list-unstyled">
                <?php foreach ($activities as $a): ?>
                    <li class="mb-3">
                        <div class="fw-semibold"><?= Html::encode($a->action) ?></div>
                        <div class="text-muted small"><?= Html::encode($a->details) ?></div>
                        <div class="text-muted small">
                            <?= Html::encode($a->user->username ?? 'System') ?>
                            • <?= Yii::$app->formatter->asDatetime($a->created_at, 'php:d M Y h:i A') ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="text-muted small">No activity yet</div>
        <?php endif; ?>
    </div>




</div>
