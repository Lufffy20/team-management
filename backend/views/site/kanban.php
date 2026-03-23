<?php
use yii\helpers\Url;
?>

<style>
.kanban-board {
    display: flex;
    gap: 20px;
    overflow-x: auto;
}
.kanban-col {
    width: 280px;
    min-width: 280px;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
}
.kanban-col h6 {
    font-weight: bold;
    margin-bottom: 15px;
}
.task-card {
    background: #fff;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,.1);
    cursor: grab;
    border-left: 5px solid #696CFF;
}
.dropzone {
    min-height: 400px;
}
</style>

<div class="container-xxl container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Kanban Board</h4>
        <a href="<?= Url::to(['tasks/create']) ?>" class="btn btn-primary btn-sm">
            + Add Task
        </a>
    </div>

    <div class="kanban-board">

        <?php 
        $columns = [
            'To Do',
            'In Progress',
            'Review',
            'Completed'
        ];
        ?>

        <?php foreach ($columns as $col): ?>
            <div class="kanban-col" data-status="<?= $col ?>">

                <h6><?= $col ?></h6>

                <div class="dropzone" data-status="<?= $col ?>">
                    <?php if (!empty($tasks[$col])): ?>
                        <?php foreach ($tasks[$col] as $task): ?>

                            <div class="task-card" 
                                draggable="true" 
                                data-id="<?= $task->id ?>">

                                <strong><?= $task->title ?></strong>
                                <div class="small text-muted">
                                    <?= $task->priority ?>
                                </div>

                            </div>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>

    </div>
</div>

<script>
const updateUrl = "<?= Url::to(['kanban/update-status']) ?>";

/* DRAG START */
document.querySelectorAll(".task-card").forEach(card => {
    card.addEventListener("dragstart", e => {
        e.dataTransfer.setData("task_id", card.dataset.id);
    });
});

/* DROP ZONES */
document.querySelectorAll(".dropzone").forEach(zone => {

    zone.addEventListener("dragover", e => e.preventDefault());

    zone.addEventListener("drop", e => {
        e.preventDefault();

        let taskId = e.dataTransfer.getData("task_id");
        let newStatus = zone.dataset.status;

        // Move card visually
        let card = document.querySelector(`[data-id='${taskId}']`);
        zone.appendChild(card);

        // AJAX UPDATE
        fetch(updateUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": "<?= Yii::$app->request->csrfToken ?>"
            },
            body: JSON.stringify({
                task_id: taskId,
                status: newStatus
            })
        });
    });
});
</script>
