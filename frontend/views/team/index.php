<?php
use yii\helpers\Html;
use common\models\TeamMembers;
use common\models\User;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">📂 My Teams</h3>
    <a href="/team/create" class="btn btn-primary">+ Create Team</a>
</div>

<?php if (count($teams) == 0): ?>

    <div class="text-center p-5 border rounded shadow-sm bg-light">
        <h4 class="fw-bold mb-2">😕 No teams found</h4>
        <p class="text-muted">You haven't created or joined any team yet.</p>
        <a href="/team/create" class="btn btn-success">+ Create your first Team</a>
    </div>

<?php else: ?>

<div class="row g-3">

<?php foreach ($teams as $t): ?>

    <?php
        $team = $t->team;
        $memberCount = count($team->members);

        // 🔹 CURRENT USER ROLE FROM team_members TABLE
        $currentUserRole = strtolower(
            TeamMembers::find()
                ->where([
                    'team_id' => $team->id,
                    'user_id' => Yii::$app->user->id
                ])
                ->select('role')
                ->scalar()
        );

        // 🔹 Admin OR Manager = full power
        $canManage = in_array($currentUserRole, ['admin', 'manager']);

        // 🔹 Team creator name
        $managerName = ($team->created_by == Yii::$app->user->id)
            ? 'You'
            : (User::findOne($team->created_by)->username ?? 'Unknown');
    ?>

    <div class="col-md-4 col-lg-3">
        <div class="card border-0 shadow-sm p-3 hover-card" style="border-radius:14px;">

            <h5 class="fw-bold mb-1">
                <a href="/team/view?id=<?= $team->id ?>" class="text-decoration-none text-dark">
                    <?= Html::encode($team->name) ?>
                </a>
            </h5>

            <p class="text-muted small" style="min-height:35px;">
                <?= $team->description
                    ? Html::encode(substr($team->description, 0, 40)) . '...'
                    : 'No description available' ?>
            </p>

            <div class="d-flex justify-content-between small mb-2">
                <span>👥 Members: <b><?= $memberCount ?></b></span>
                <span>👤 Owner: <b><?= Html::encode($managerName) ?></b></span>
            </div>

            <div class="text-muted small">
                📅 Created: <b><?= date('d M Y', $team->created_at) ?></b>
            </div>

            <div class="d-flex gap-2 mt-3">

                <?php if ($canManage): ?>

                    <!-- Admin OR Manager -->
                    <a href="/team/view?id=<?= $team->id ?>"
                       class="btn btn-outline-primary btn-sm w-50">
                        View →
                    </a>

                    <?= Html::a(
                        'Delete',
                        ['/team/delete', 'id' => $team->id],
                        [
                            'class' => 'btn btn-danger btn-sm w-50',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this team?',
                                'method' => 'post',
                            ],
                        ]
                    ) ?>

                <?php else: ?>

                    <!-- Normal Member -->
                    <a href="/team/view?id=<?= $team->id ?>"
                       class="btn btn-outline-primary btn-sm w-100">
                        View →
                    </a>

                <?php endif; ?>

            </div>

        </div>
    </div>

<?php endforeach; ?>

</div>
<?php endif; ?>

<style>
.hover-card {
    transition: 0.3s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
</style>
