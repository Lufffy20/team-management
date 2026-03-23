<div>
    <h5><?= $task->title ?></h5>

    <p class="text-muted mb-1">Project: <?= $task->project->name ?? '-' ?></p>

    <p><?= $task->description ?></p>

    <p>
        <span class="badge bg-label-primary"><?= $task->priority ?></span>
        <span class="badge bg-label-info"><?= $task->status ?></span>
    </p>

    <p class="small text-muted mb-0">Due: <?= $task->due_date ?></p>
</div>
