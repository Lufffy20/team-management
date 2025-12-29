<?php 
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<h3 class="fw-bold mb-3">Edit Member</h3>

<div class="card p-4 shadow-sm" style="max-width:450px">

    <?php $form = ActiveForm::begin(['action'=>'/team/update-member-settings']); ?>

        <?= Html::hiddenInput('team_id', $teamId) ?>
        <?= Html::hiddenInput('user_id', $userId) ?>

        <label class="fw-semibold mt-2">Role</label>
        <select name="role" class="form-control">
            <option value="manager" <?= $role=='manager'?'selected':'' ?>>Manager</option>
            <option value="member" <?= $role=='member'?'selected':'' ?>>Member</option>
        </select>

        <label class="fw-semibold mt-3">Boards</label>
        <select name="boards[]" class="form-control" multiple required>
            <?php foreach($boards as $b): ?>
                <option value="<?= $b->id ?>" 
                    <?= in_array($b->id,$memberBoards)?'selected':'' ?>>
                    <?= $b->title ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn btn-primary w-100 mt-3">💾 Update Member</button>

    <?php ActiveForm::end(); ?>

</div>
