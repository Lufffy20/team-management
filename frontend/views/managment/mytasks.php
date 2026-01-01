<h4 class="mb-3 fw-bold d-flex justify-content-between align-items-center">
    My Tasks
    <a href="<?= \yii\helpers\Url::to(['managment/create-task']) ?>" class="btn btn-primary btn-sm shadow-sm">
        + Add Task
    </a>
</h4>

<!-- FILTER TABS -->
<ul class="nav nav-pills small mb-3 gap-2">
<?php
$filters = [
    null => 'All',
    'todo' => 'To-Do',
    'in_progress' => 'In Progress',
    'done' => 'Done'
];
foreach ($filters as $key => $label): ?>
    <li class="nav-item">
        <a class="nav-link px-3 <?= $status === $key ? 'active' : '' ?>"
           href="<?= \yii\helpers\Url::to(['managment/mytasks','status'=>$key]) ?>">
            <?= $label ?>
        </a>
    </li>
<?php endforeach; ?>
</ul>

<!-- TASK LIST -->
<div class="card border-0 shadow-sm">
<div class="card-body p-0">

<?php if (empty($tasks)): ?>
    <div class="text-center text-muted py-5">
        <div style="font-size:48px;">📭</div>
        <div class="mt-2 fw-semibold">No tasks found</div>
    </div>
<?php else: ?>
<ul class="list-group list-group-flush">

<?php foreach ($tasks as $task): ?>
<?php
    $priorityClass = match($task->priority) {
        'high'   => 'danger',
        'medium' => 'warning',
        default  => 'success',
    };

    $statusLabel = match($task->status) {
        'todo' => 'To-Do',
        'in_progress' => 'In Progress',
        'done' => 'Done',
        default => 'Unknown'
    };

    $statusColor = match($task->status) {
        'todo' => 'secondary',
        'in_progress' => 'primary',
        'done' => 'success',
        default => 'secondary'
    };

    $isOverdue = strtotime($task->due_date) < strtotime(date('Y-m-d'))
                 && $task->status !== 'done';
?>

<li class="list-group-item task-row"
    onclick="window.location='<?= \yii\helpers\Url::to(['managment/view-task','id'=>$task->id]) ?>'">

<div class="d-flex justify-content-between align-items-start">

<!-- LEFT -->
<div>
    <div class="fw-semibold fs-6 d-flex align-items-center gap-2">
        <?= htmlspecialchars($task->title) ?>
        <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?>">
            <?= $statusLabel ?>
        </span>
    </div>

    <div class="text-muted small mt-1">
        <?= htmlspecialchars($task->description) ?>
    </div>

    <div class="small mt-1 <?= $isOverdue ? 'text-danger fw-semibold' : 'text-muted' ?>">
        📅 Due: <?= date("d M Y", strtotime($task->due_date)) ?>
        <?= $isOverdue ? '(Overdue)' : '' ?>
    </div>
</div>

<!-- RIGHT -->
<div class="text-end">

    <span class="badge rounded-pill bg-<?= $priorityClass ?> mb-2">
        <?= ucfirst($task->priority) ?>
    </span>

    <div class="dropdown">
        <button class="btn btn-sm btn-light border"
                onclick="event.stopPropagation()"
                data-bs-toggle="dropdown">
            ⋮
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item"
                   href="<?= \yii\helpers\Url::to(['managment/update-task','id'=>$task->id]) ?>"
                   onclick="event.stopPropagation()">✏️ Edit</a>
            </li>
            <li>
                <a class="dropdown-item text-danger"
                   href="<?= \yii\helpers\Url::to(['managment/delete-task','id'=>$task->id]) ?>"
                   onclick="event.stopPropagation(); return confirm('Delete this task?')">
                   🗑 Delete
                </a>
            </li>
        </ul>
    </div>

</div>
</div>
</li>

<?php endforeach; ?>
</ul>
<?php endif; ?>

</div>
</div>

<style>
.task-row {
    cursor: pointer;
    transition: all .2s ease;
    padding: 1rem 1.25rem;
}
.task-row:hover {
    background: #f8f9fa;
    box-shadow: inset 4px 0 0 #0d6efd;
}
</style>
