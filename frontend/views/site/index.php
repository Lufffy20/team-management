<?php
use frontend\assets\AppAsset;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

AppAsset::register($this);

/* Pass stats URL to JS safely */
$this->registerJs(
    "const DASHBOARD_STATS_URL = '" . Url::to(['/site/stats'], true) . "';",
    yii\web\View::POS_HEAD
);
?>

<h3 class="fw-bold mb-4">Dashboard</h3>

<!-- ================= STATS CARDS ================= -->
<div class="row g-3 mb-4">

    <?php
    $cards = [
        ['Total Tasks', $totalTasks ?? 0, ''],
        ['In Progress', $inProgress ?? 0, 'text-primary'],
        ['Due Today', $dueToday ?? 0, 'text-danger'],
        ['Completed This Week', $completedWeekly ?? 0, 'text-success'],
    ];
    ?>

    <?php foreach ($cards as [$label, $value, $class]): ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="text-muted small"><?= $label ?></div>
                <h3 class="fw-bold <?= $class ?>"><?= (int)$value ?></h3>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<!-- ================= CHARTS ================= -->
<div class="row g-4 mb-4">

    <!-- STATUS PIE -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 chart-card">

            <div class="chart-header">
                <h6 class="fw-bold mb-2">Task Status</h6>
            </div>

            <div class="chart-body">
                <canvas id="statusChart"></canvas>
            </div>

        </div>
    </div>

    <!-- TEAM WORKLOAD -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm p-3 chart-card">

            <div class="chart-header d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="fw-bold mb-1">Team Workload</h6>
                    <small class="text-muted">
                        Active tasks assigned to each team member
                    </small>
                </div>

                
    <select id="teamSelect" class="form-select form-select-sm w-auto">
        <?php foreach ($teams as $team): ?>
            <option value="<?= $team->id ?>">
                <?= Html::encode($team->name) ?>
            </option>
        <?php endforeach; ?>
    </select>
            </div>

            <div class="chart-body">
                <canvas id="memberChart"></canvas>
            </div>

        </div>
    </div>

    <!-- TIMELINE -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm p-3 chart-card">

            <div class="chart-header">
                <h6 class="fw-bold mb-2">Weekly Task Activity</h6>
            </div>

            <div class="chart-body">
                <canvas id="timelineChart"></canvas>
            </div>

        </div>
    </div>

</div>


<div class="row g-4">

    <!-- ================= LEFT PANEL ================= -->
    <div class="col-lg-8">

        <!-- UPCOMING TASKS -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between">
                <h6 class="mb-0">Upcoming Tasks</h6>
                <a href="<?= Url::to(['/task/all']) ?>" class="small text-primary">View all</a>
            </div>

            <div class="card-body p-0">
                <ul class="list-group list-group-flush">

                    <?php if ($myTasks): ?>
                        <?php foreach ($myTasks as $t): ?>

                            <?php
                            $isMine = $t->created_by == Yii::$app->user->id;
                            $priorityColor = match ($t->priority) {
                                'high' => 'danger',
                                'medium' => 'warning',
                                default => 'secondary'
                            };

                            $isOverdue = $t->due_date &&
                                strtotime($t->due_date) < strtotime(date('Y-m-d'));
                            ?>

                            <li class="list-group-item d-flex justify-content-between">
                                <div>
                                    <a href="<?= Url::to(['/task/view', 'id' => $t->id]) ?>"
                                       class="fw-semibold text-decoration-none">
                                        <?= Html::encode($t->title) ?>
                                    </a>

                                    <div class="text-muted small mt-1">
                                        <span class="badge bg-<?= $isMine ? 'success' : 'primary' ?>">
                                            <?= $isMine ? 'My Task' : 'Team Task' ?>
                                        </span>
                                        • <?= Html::encode($t->board->title ?? 'No Board') ?>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <span class="badge bg-<?= $priorityColor ?>">
                                        <?= ucfirst($t->priority) ?>
                                    </span>

                                    <div class="small mt-1">
                                        Due:
                                        <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                            <?= $t->due_date ? date('d M', strtotime($t->due_date)) : '—' ?>
                                        </span>
                                    </div>
                                </div>
                            </li>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted">
                            No upcoming tasks
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>

        <!-- TASK LIST -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between">
                <h6 class="fw-bold mb-0">Task List</h6>
                <a href="<?= Url::to(['/task-user/index']) ?>" class="small text-primary">View all</a>
            </div>

            <div class="card-body p-0 small">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'summary' => false,
                    'pager' => false,
                    'tableOptions' => ['class' => 'table table-sm table-hover mb-0'],
                    'columns' => [
                        [
                            'attribute' => 'title',
                            'format' => 'raw',
                            'value' => fn($m) =>
                                Html::a(Html::encode($m->title),
                                    ['/task/view', 'id' => $m->id],
                                    ['class' => 'fw-bold text-decoration-none']
                                ),
                        ],
                        'priority',
                        [
                            'label' => 'Due',
                            'value' => fn($m) =>
                                $m->due_date ? date('d M', strtotime($m->due_date)) : '—',
                        ],
                    ],
                ]) ?>
            </div>
        </div>

    </div>

    <!-- ================= RIGHT PANEL ================= -->
    <div class="col-lg-4">

        <!-- RECENT ACTIVITY -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0">Recent Team Activity</h6>
            </div>

            <div class="card-body small">
                <?php if ($recent): ?>
                    <?php foreach ($recent as $r): ?>
                        <div class="mb-3 border-bottom pb-2">
                            <strong><?= Html::encode($r->updatedBy->username ?? 'User') ?></strong>
                            updated <b><?= Html::encode($r->title) ?></b>
                            <div class="text-muted">
                                <?= Yii::$app->formatter->asRelativeTime($r->updated_at) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-muted">No recent activity</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TEAM OVERVIEW -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0">Team Overview</h6>
            </div>

            <div class="card-body small">
                <?php if ($teamStats): ?>
                    <?php foreach ($teamStats as $m): ?>
                        <div class="mb-2 border-bottom pb-2">
                            <b><?= Html::encode($m['username']) ?></b>
                            <div class="text-muted">
                                Open: <?= $m['openTasks'] ?> |
                                Progress: <?= $m['inProgress'] ?> |
                                Done: <?= $m['completedThisWeek'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-muted">No team members found</div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<style>
    /* ===== Dashboard Charts ===== */
.chart-card {
    height: 340px;                 /* 🔥 SAME CARD HEIGHT */
    display: flex;
    flex-direction: column;
}

.chart-header {
    flex-shrink: 0;
}

.chart-body {
    flex: 1;
    min-height: 220px;             /* 🔥 SAME CHART AREA */
    position: relative;
}

</style>