<?php use yii\helpers\Html; ?>

<h3>Edit Board - <?= $board->title ?></h3>

<div class="row">

    <!-- LEFT SIDE : Board Edit -->
    <div class="col-md-6">
        <div class="card p-4 mb-4 shadow-sm">

            <?= Html::beginForm(['/board/update'], 'post') ?>
                <?= Html::hiddenInput('id', $board->id) ?>

                <label class="fw-semibold mb-1">Board Title</label>
                <input type="text" name="title" class="form-control mb-3" value="<?= $board->title ?>">

                <label>Description</label>
                <textarea name="description" class="form-control mb-3"><?= $board->description ?></textarea>

                <button class="btn btn-success">Update</button>
                <a href="/task/kanban?board_id=<?= $board->id ?>" class="btn btn-dark">Go to Board</a>
            <?= Html::endForm() ?>

        </div>
    </div>

    <!-- RIGHT SIDE : Board Members -->
    <div class="col-md-6">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3">Members in this Board</h5>

            <?php if(count($members) > 0): ?>
                <ul class="list-group">
                    <?php foreach($members as $m): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                <b><?= $m->user->username ?? $m->user->email ?></b><br>
                                <span class="text-muted small"><?= $m->user->email ?></span>
                            </div>

                            <!-- REMOVE BUTTON HIDDEN -->
                            <!-- <a ...>Remove</a> -->
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">No members added yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>
