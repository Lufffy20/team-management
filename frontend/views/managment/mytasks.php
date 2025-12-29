<h4 class="mb-3 fw-bold d-flex justify-content-between align-items-center">
    My Tasks
    <a href="<?= \yii\helpers\Url::to(['managment/create-task']) ?>" class="btn btn-primary btn-sm">
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
    ?>

    <?php foreach ($filters as $key => $label): ?>
        <li class="nav-item">
            <a class="nav-link <?= $status === $key ? 'active' : '' ?> px-3"
               href="<?= \yii\helpers\Url::to(['managment/mytasks', 'status' => $key]) ?>">
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
                <div style="font-size:48px;">🗂️</div>
                <div class="mt-2">No tasks found</div>
            </div>
        <?php else: ?>
            <ul class="list-group list-group-flush">

                <?php foreach ($tasks as $task): ?>

                    <?php
                        // Priority badge
                        $badgeClass = match($task->priority) {
                            'high'   => 'bg-danger-subtle text-danger border border-danger',
                            'medium' => 'bg-warning-subtle text-warning border border-warning',
                            default  => 'bg-success-subtle text-success border border-success',
                        };

                        // Status colors
                        $statusColor = match($task->status) {
                            'todo'        => '#6c757d',
                            'in_progress' => '#0d6efd',
                            'done'        => '#198754',
                            default       => '#6c757d',
                        };
                    ?>

                    <li class="list-group-item py-3 task-item position-relative"
                        style="transition:0.2s; cursor:pointer;"
                        onclick="window.location='<?= \yii\helpers\Url::to(['managment/view-task','id'=>$task->id]) ?>'">


                        <div class="d-flex justify-content-between align-items-start">

                            <div>
                                <!-- STATUS DOT + TITLE -->
                                <div class="fw-semibold d-flex align-items-center gap-2">
                                    <span style="
                                        height:10px; width:10px; border-radius:50%;
                                        display:inline-block; background:<?= $statusColor ?>;">
                                    </span>
                                    <?= htmlspecialchars($task->title) ?>
                                </div>

                                <!-- DESCRIPTION + DUE DATE -->
                                <div class="text-muted small mt-1">
                                    <?= htmlspecialchars($task->description) ?>
                                    &nbsp;·&nbsp;
                                    <b>Due: <?= date("d M", strtotime($task->due_date)) ?></b>
                                </div>
                            </div>

                            <!-- RIGHT SIDE ACTIONS -->
                            <div class="d-flex flex-column align-items-end gap-1">

                                <!-- PRIORITY BADGE -->
                                <span class="badge rounded-pill <?= $badgeClass ?>">
                                    <?= ucfirst($task->priority) ?>
                                </span>

                                <div class="mt-1 d-flex gap-2">

                                    <!-- EDIT BUTTON -->
                                    <a href="<?= \yii\helpers\Url::to(['managment/update-task', 'id' => $task->id]) ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       onclick="event.stopPropagation();">
                                       Edit
                                    </a>

                                    <!-- DELETE BUTTON -->
                                    <a href="<?= \yii\helpers\Url::to(['managment/delete-task', 'id' => $task->id]) ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="event.stopPropagation(); return confirm('Delete this task?');">
                                       Delete
                                    </a>

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
    .task-item:hover {
        background: #f9f9f9;
        transform: translateX(4px);
    }
</style>
