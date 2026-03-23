<?php
use yii\helpers\Html;
?>
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
