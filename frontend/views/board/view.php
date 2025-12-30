<?php
use yii\helpers\Html;
?>

<h3>Edit Board - <?= Html::encode($board->title) ?></h3>

<div class="row">

    <!-- LEFT SIDE : Board Edit -->
    <div class="col-md-6">
        <div class="card p-4 mb-4 shadow-sm">

            <?= Html::beginForm(['/board/update'], 'post') ?>
                <?= Html::hiddenInput('id', $board->id) ?>

                <label class="fw-semibold mb-1">Board Title</label>
                <input
                    type="text"
                    name="title"
                    class="form-control mb-3"
                    value="<?= Html::encode($board->title) ?>"
                >

                <label>Description</label>
                <textarea
                    name="description"
                    class="form-control mb-3"
                ><?= Html::encode($board->description) ?></textarea>

                <button class="btn btn-success">Update</button>
                <a href="/task/kanban?board_id=<?= $board->id ?>" class="btn btn-dark">
                    Go to Board
                </a>
            <?= Html::endForm() ?>

        </div>
    </div>

    <!-- RIGHT SIDE : Board Members -->
    <div class="col-md-6">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3">Members in this Board</h5>

            <?php if (!empty($members)): ?>
                <ul class="list-group">
                    <?php foreach ($members as $m): ?>

                        <?php
                            $isYou     = ($m->user_id == Yii::$app->user->id);
                            $isManager = ($m->user_id == $board->created_by);
                        ?>

                        <li class="list-group-item d-flex justify-content-between align-items-center">

                            <div>
                                <b>
                                    <?= Html::encode($m->user->username ?? $m->user->email) ?>

                                    <?php if ($isYou): ?>
                                        <span class="badge bg-dark ms-1">You</span>
                                    <?php endif; ?>

                                    <?php if ($isManager): ?>
                                        <span class="badge bg-dark ms-1">Manager</span>
                                    <?php endif; ?>

                                </b>
                                <br>
                                <span class="text-muted small">
                                    <?= Html::encode($m->user->email) ?>
                                </span>
                            </div>

                            <!-- REMOVE BUTTON (ONLY MANAGER, NOT SELF) -->
                            <?php if (
                                $board->created_by == Yii::$app->user->id &&
                                !$isYou
                            ): ?>
                                <?= Html::a(
                                    'Remove',
                                    [
                                        '/board/remove-member',
                                        'board_id' => $board->id,
                                        'user' => $m->user_id,
                                    ],
                                    [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'data' => [
                                            'confirm' => 'Are you sure you want to remove this member?',
                                            'method' => 'post',
                                        ],
                                    ]
                                ) ?>
                            <?php endif; ?>

                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">No members added yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>
