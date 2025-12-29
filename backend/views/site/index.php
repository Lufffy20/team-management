<?php
use yii\helpers\Url;

$this->title = 'Dashboard';
?>

<div class="container-xxl py-4">

    <!-- ================= TOP SUMMARY CARDS ================= -->
    <div class="row g-3">

        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <small class="text-muted">Total Tasks</small>
                <h3 class="fw-bold mb-0"><?= $totalTasks ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <small class="text-muted">Completed</small>
                <h3 class="fw-bold text-success mb-0"><?= $doneCount ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <small class="text-muted">In Progress</small>
                <h3 class="fw-bold text-info mb-0"><?= $inProgressCount ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <small class="text-muted">Archived</small>
                <h3 class="fw-bold text-secondary mb-0"><?= $archivedCount ?></h3>
            </div>
        </div>

    </div>

    <!-- ================= CHARTS SECTION ================= -->
    <div class="row mt-4 g-4">

        <!-- STATUS DONUT -->
        <div class="col-md-4">
            <div class="card shadow-sm p-3 h-100">
                <h6 class="fw-semibold mb-3">Task Status</h6>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- WEEKLY TREND -->
        <div class="col-md-8">
            <div class="card shadow-sm p-3 h-100">
                <h6 class="fw-semibold mb-3">Tasks Created (Last 7 Days)</h6>
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>

    </div>

    <!-- ================= PRIORITY BAR ================= -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm p-3">
                <h6 class="fw-semibold mb-3">Priority Distribution</h6>
                <canvas id="priorityChart" height="90"></canvas>
            </div>
        </div>
    </div>

    <!-- ================= RECENT TASKS ================= -->
    <div class="card shadow-sm p-3 mt-4">

        <h6 class="fw-semibold mb-3">Recent Tasks</h6>

        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Board</th>
                    <th>Status</th>
                    <th>Team</th>
                    <th>Created</th>
                </tr>
            </thead>

            <tbody>
            <?php if (!empty($recentTasks)): ?>
                <?php foreach ($recentTasks as $task): ?>
                    <tr>
                        <td class="fw-semibold"><?= $task->title ?></td>
                        <td><?= $task->board->title ?? '-' ?></td>
                        <td>
                            <span class="badge bg-secondary">
                                <?= ucfirst(str_replace('_', ' ', $task->status)) ?>
                            </span>
                        </td>
                        <td><?= $task->team->name ?? '-' ?></td>
                        <td><?= date('d M Y', $task->created_at) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        No recent tasks found
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <a href="<?= Url::to(['task/index']) ?>" class="btn btn-outline-primary btn-sm">
            View All Tasks →
        </a>

    </div>

</div>

<?php
$this->registerJsVar('dashboardData', [
    'statusChart'   => array_values($statusChart),
    'weeklyLabels'  => array_column($weeklyStats, 'day'),
    'weeklyCounts'  => array_column($weeklyStats, 'count'),
    'priorityStats' => array_values($priorityStats),
]);
?>
