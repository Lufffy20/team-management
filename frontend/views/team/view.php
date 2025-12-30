<div class="row">

    <!-- LEFT SIDE : Members -->
    <div class="col-md-9">
        <h4 class="mb-3 fw-bold">Team Members</h4>

        <?php
        // Current user's role inside this team
        $currentUserRole = strtolower(
            \common\models\TeamMembers::find()
                ->where(['team_id' => $team->id, 'user_id' => Yii::$app->user->id])
                ->select('role')
                ->scalar()
        );

        // Only Admin or Manager can manage members
        $canManage = in_array($currentUserRole, ['admin', 'manager']);
        ?>

        <div class="row g-3">

        <?php foreach ($members as $m): ?>

            <?php
                // Team owner
                $isManager = ($m->user_id == $team->created_by);

                // Self
                $isSelf = ($m->user_id == Yii::$app->user->id);

                // Open tasks
                $taskCount = \common\models\Task::find()
                    ->where([
                        'assigned_to' => $m->user_id,
                        'status' => 'open'
                    ])->count();

                // ✅ FIXED: Only boards of THIS TEAM
                $memberBoards = \common\models\BoardMembers::find()
                    ->alias('bm')
                    ->innerJoin('board b', 'b.id = bm.board_id')
                    ->where([
                        'bm.user_id' => $m->user_id,
                        'b.team_id'  => $team->id
                    ])
                    ->select('bm.board_id')
                    ->column();
            ?>

            <div class="col-md-4">
                <div class="card member-card 
                    <?= $isManager ? 'manager-card' : ($isSelf ? 'self-card' : '') ?> 
                    border-0 shadow-sm text-center p-3">

                    <!-- Avatar -->
                    <img src="https://ui-avatars.com/api/?name=<?= $m->user->username ?>&background=random&rounded=true&size=70"
                         class="rounded-circle mx-auto mb-2">

                    <!-- Name -->
                    <h6 class="mb-0"><?= $m->user->username ?></h6>

                    <!-- Badges -->
                    <div class="mt-1">
                        <?php if ($isSelf): ?>
                            <span class="badge bg-primary">You</span>
                        <?php elseif ($isManager): ?>
                            <span class="badge bg-warning text-dark">Manager</span>
                        <?php endif; ?>
                    </div>

                    <div class="text-muted small mb-1"><?= ucfirst($m->role) ?></div>
                    <div class="small">Open Tasks: <?= $taskCount ?></div>

                    <!-- Boards -->
                    <div class="mt-2 small">
                        <strong>Boards:</strong><br>

                        <?php if (!empty($memberBoards)): ?>
                            <?php foreach ($memberBoards as $bid): ?>
                                <?php $board = \common\models\Board::findOne($bid); ?>
                                <?php if ($board): ?>
                                    <span class="badge bg-info text-dark m-1">
                                        <?= $board->title ?>
                                    </span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="badge bg-danger">No Board Assigned</span>
                        <?php endif; ?>
                    </div>

                    <!-- Edit / Delete -->
                    <?php if ($canManage && !$isManager && !$isSelf): ?>
                        <a href="/team/edit-member?user_id=<?= $m->user_id ?>&team_id=<?= $team->id ?>"
                           class="btn btn-sm btn-outline-primary w-100 mt-2">
                            ✏ Edit Member
                        </a>

                        <a href="/team/delete-member?user_id=<?= $m->user_id ?>&team_id=<?= $team->id ?>"
                           class="btn btn-sm btn-danger w-100 mt-2"
                           onclick="return confirm('Are you sure you want to remove this member?')">
                            🗑 Delete Member
                        </a>
                    <?php endif; ?>

                </div>
            </div>

        <?php endforeach; ?>
        </div>
    </div>

    <!-- ADD MEMBER -->
    <?php if ($canManage): ?>
    <div class="col-md-3">
        <a href="/team/add-member-page?id=<?= $team->id ?>"
           class="btn btn-primary w-100 p-3 fw-bold">
            ➕ Add Members
        </a>
    </div>
    <?php endif; ?>

</div>
