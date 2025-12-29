<?php
use yii\helpers\Url;
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">My Tasks</h4>
    <a href="<?= Url::to(['tasks/create']) ?>" class="btn btn-primary btn-sm">
      <i class="bx bx-plus"></i> New Task
    </a>
  </div>

  <!-- FILTERS -->
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-body">

      <form method="get">

        <div class="row g-3">

          <!-- Status Filter -->
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="">All</option>
              <option value="To Do" <?= $status=='To Do'?'selected':'' ?>>To Do</option>
              <option value="In Progress" <?= $status=='In Progress'?'selected':'' ?>>In Progress</option>
              <option value="Review" <?= $status=='Review'?'selected':'' ?>>Review</option>
              <option value="Completed" <?= $status=='Completed'?'selected':'' ?>>Completed</option>
            </select>
          </div>

          <!-- Priority Filter -->
          <div class="col-md-3">
            <label class="form-label">Priority</label>
            <select class="form-select" name="priority">
              <option value="">All</option>
              <option value="Low" <?= $priority=='Low'?'selected':'' ?>>Low</option>
              <option value="Medium" <?= $priority=='Medium'?'selected':'' ?>>Medium</option>
              <option value="High" <?= $priority=='High'?'selected':'' ?>>High</option>
            </select>
          </div>

          <!-- Due Date Filter -->
          <div class="col-md-3">
            <label class="form-label">Due Date</label>
            <input type="date" class="form-control" name="due_date" value="<?= $due_date ?>">
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100">
              <i class="bx bx-search"></i> Filter
            </button>
          </div>

        </div>

      </form>

    </div>
  </div>

  <!-- TASK LIST -->
  <div class="card shadow-sm border-0">
    <div class="card-header">
      <h5 class="card-title mb-0">Your Assigned Tasks</h5>
    </div>

    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Task</th>
            <th>Project</th>
            <th>Priority</th>
            <th>Due Date</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>

        <tbody>

          <?php if (!empty($tasks)): ?>
            <?php foreach ($tasks as $task): ?>
              <tr>
                <td><?= $task->title ?></td>

                <td>
                  <span class="text-muted">
                    <?= $task->project->name ?? '-' ?>
                  </span>
                </td>

                <td>
                  <span class="badge bg-label-<?=
                    $task->priority=='High'?'danger':
                    ($task->priority=='Medium'?'warning':'success')
                  ?>">
                    <?= $task->priority ?>
                  </span>
                </td>

                <td><?= $task->due_date ?: '-' ?></td>

                <td>
                  <span class="badge bg-label-<?=
                    $task->status=='Completed'?'success':
                    ($task->status=='In Progress'?'info':
                    ($task->status=='Review'?'warning':'secondary'))
                  ?>">
                    <?= $task->status ?>
                  </span>
                </td>

                <td class="text-end">
                  <a href="<?= Url::to(['tasks/view', 'id'=>$task->id]) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bx bx-show"></i>
                  </a>
                  <a href="<?= Url::to(['tasks/update', 'id'=>$task->id]) ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-edit"></i>
                  </a>
                </td>

              </tr>
            <?php endforeach; ?>

          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                No tasks found.
              </td>
            </tr>
          <?php endif; ?>

        </tbody>

      </table>
    </div>

  </div>

</div>
