<?php
use yii\helpers\Url;
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">All Tasks</h4>
    <a href="<?= Url::to(['tasks/create']) ?>" class="btn btn-primary btn-sm">
      <i class="bx bx-plus"></i> Create Task
    </a>
  </div>

  <!-- FILTERS -->
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-body">

      <form method="get">

        <div class="row g-3">

          <!-- Assigned User -->
          <div class="col-md-3">
            <label class="form-label fw-semibold">Assigned To</label>
            <select class="form-select" name="TaskSearch[assigned_to]">
              <option value="">All</option>

              <?php foreach ($users as $u): ?>
                <option value="<?= $u->id ?>"
                  <?= ($searchModel->assigned_to == $u->id) ? 'selected' : '' ?>>
                  <?= $u->first_name . ' ' . $u->last_name ?>
                </option>
              <?php endforeach; ?>

            </select>
          </div>

          <!-- Status -->
          <div class="col-md-3">
            <label class="form-label fw-semibold">Status</label>
            <select class="form-select" name="TaskSearch[status]">
              <option value="">All</option>
              <option value="To Do"        <?= $searchModel->status=='To Do'?'selected':'' ?>>To Do</option>
              <option value="In Progress"  <?= $searchModel->status=='In Progress'?'selected':'' ?>>In Progress</option>
              <option value="Review"       <?= $searchModel->status=='Review'?'selected':'' ?>>Review</option>
              <option value="Completed"    <?= $searchModel->status=='Completed'?'selected':'' ?>>Completed</option>
            </select>
          </div>

          <!-- Priority -->
          <div class="col-md-3">
            <label class="form-label fw-semibold">Priority</label>
            <select class="form-select" name="TaskSearch[priority]">
              <option value="">All</option>
              <option value="Low"    <?= $searchModel->priority=='Low'?'selected':'' ?>>Low</option>
              <option value="Medium" <?= $searchModel->priority=='Medium'?'selected':'' ?>>Medium</option>
              <option value="High"   <?= $searchModel->priority=='High'?'selected':'' ?>>High</option>
            </select>
          </div>

          <!-- Due Date -->
          <div class="col-md-3">
            <label class="form-label fw-semibold">Due Date</label>
            <input type="date" class="form-control"
                name="TaskSearch[due_date]"
                value="<?= $searchModel->due_date ?>">
          </div>

        </div>

        <!-- Buttons -->
        <div class="mt-3 d-flex justify-content-end gap-2">
          <a href="<?= Url::to(['tasks/alltask']) ?>" class="btn btn-outline-secondary">
            <i class="bx bx-reset"></i> Reset
          </a>
          <button class="btn btn-primary">
            <i class="bx bx-search"></i> Apply Filters
          </button>
        </div>

      </form>

    </div>
  </div>

  <!-- TASK TABLE -->
  <div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between">
      <h5 class="card-title mb-0">Task List</h5>
      <span class="text-muted small">
        <?= $dataProvider->getTotalCount() ?> results
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Task</th>
            <th>Assigned To</th>
            <th>Project</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Due Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>

        <tbody>

          <?php $tasks = $dataProvider->models; ?>

          <?php if (!empty($tasks)): ?>
            <?php foreach ($tasks as $task): ?>
              <tr>
                <td class="fw-semibold"><?= $task->title ?></td>

                <td><?= $task->assignedUser->first_name ?? '—' ?></td>

                <td class="text-muted"><?= $task->project->name ?? '-' ?></td>

                <td>
                  <span class="badge bg-label-<?=
                    $task->priority=='High'?'danger':
                    ($task->priority=='Medium'?'warning':'success')
                  ?>">
                    <?= $task->priority ?>
                  </span>
                </td>

                <td>
                  <span class="badge bg-label-<?=
                    $task->status=='Completed'   ? 'success' :
                    ($task->status=='In Progress'? 'info' :
                    ($task->status=='Review'     ? 'warning' : 'secondary'))
                  ?>">
                    <?= $task->status ?>
                  </span>
                </td>

                <td><?= $task->due_date ?: '-' ?></td>

                <td class="text-end">
                  <a href="<?= Url::to(['tasks/view', 'id' => $task->id]) ?>" 
                     class="btn btn-sm btn-outline-primary">
                    <i class="bx bx-show"></i>
                  </a>

                  <a href="<?= Url::to(['tasks/update', 'id' => $task->id]) ?>" 
                     class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-edit"></i>
                  </a>

                  <a href="<?= Url::to(['tasks/delete', 'id' => $task->id]) ?>" 
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Delete this task?')">
                    <i class="bx bx-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>

          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                No tasks found.
              </td>
            </tr>
          <?php endif; ?>

        </tbody>

      </table>
    </div>
  </div>

</div>
