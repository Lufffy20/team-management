<?php
use yii\helpers\Url;
use frontend\assets\AppAsset;
AppAsset::register($this);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <h4 class="mb-2 mb-md-0">Tasks</h4>
  <div class="d-flex gap-2">
    <a href="<?= Url::to(['task/export']) ?>" class="btn btn-sm btn-outline-secondary">Export</a>
    <a href="<?= Url::to(['task/create']) ?>" class="btn btn-sm btn-primary">+ New Task</a>
  </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm task-card mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">

      <!-- SEARCH -->
      <div class="col-md-3">
        <label class="form-label small mb-1">Search</label>
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Search by title..." 
               value="<?= Yii::$app->request->get('search') ?>">
      </div>

      <!-- ASSIGNEE FILTER -->
      <div class="col-md-2">
        <label class="form-label small mb-1">Assignee</label>
        <select name="assignee" class="form-select form-select-sm">
          <option value="">All</option>
          <?php foreach($users as $u): ?>
            <option value="<?= $u->id ?>" 
              <?= Yii::$app->request->get('assignee') == $u->id ? 'selected' : '' ?>>
              <?= $u->first_name . ' ' . $u->last_name ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- PRIORITY -->
      <div class="col-md-2">
        <label class="form-label small mb-1">Priority</label>
        <select name="priority" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="High"   <?= Yii::$app->request->get('priority')=='High'?'selected':'' ?>>High</option>
          <option value="Medium" <?= Yii::$app->request->get('priority')=='Medium'?'selected':'' ?>>Medium</option>
          <option value="Low"    <?= Yii::$app->request->get('priority')=='Low'?'selected':'' ?>>Low</option>
        </select>
      </div>

      <!-- STATUS -->
      <div class="col-md-2">
        <label class="form-label small mb-1">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="To-Do"        <?= Yii::$app->request->get('status')=='To-Do'?'selected':'' ?>>To-Do</option>
          <option value="In Progress"  <?= Yii::$app->request->get('status')=='In Progress'?'selected':'' ?>>In Progress</option>
          <option value="Done"         <?= Yii::$app->request->get('status')=='Done'?'selected':'' ?>>Done</option>
          <option value="Archived"     <?= Yii::$app->request->get('status')=='Archived'?'selected':'' ?>>Archived</option>
        </select>
      </div>

      <!-- DUE DATE -->
      <div class="col-md-3">
        <label class="form-label small mb-1">Due Date</label>
        <input type="date" name="due_date" class="form-control form-control-sm"
               value="<?= Yii::$app->request->get('due_date') ?>">
      </div>

    </form>
  </div>
</div>

<!-- TASKS TABLE -->
<div class="card border-0 shadow-sm task-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light small">
          <tr>
            <th>Task</th>
            <th>Board</th>
            <th>Assignee</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Due Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>

        <tbody class="small">
        <?php if(count($tasks) > 0): ?>
          <?php foreach($tasks as $task): ?>
          <tr>

            <td><div class="fw-semibold"><?= $task->title ?></div></td>
            <td><?= $task->board->title ?? '—' ?></td>

            <!-- ASSIGNEE SAME UI AS OLD -->
            <td>
              <div class="d-flex align-items-center gap-2">
                  <?php $u = $task->assignee; ?>
                  <div class="assignee-avatar">
                      <?= $u ? strtoupper(substr($u->first_name ?: $u->username, 0, 1)) : "U" ?>
                  </div>
                  <span class="assignee-name">
                      <?= $u ? trim($u->first_name." ".$u->last_name) : "Unassigned" ?>
                  </span>
              </div>
            </td>

            <!-- PRIORITY (OLD UI RESTORED) -->
            <td>
              <?php 
                if($task->priority == "High"){
                    $pClass = "badge bg-danger-subtle border border-danger text-danger";
                } elseif($task->priority == "Medium"){
                    $pClass = "badge bg-warning-subtle border border-warning text-warning";
                } elseif($task->priority == "Low"){
                    $pClass = "badge bg-success-subtle border border-success text-success";
                } else {
                    $pClass = "badge bg-secondary-subtle text-dark";
                }
              ?>
              <span class="<?= $pClass ?>"><?= $task->priority ?></span>
            </td>

            <!-- STATUS (OLD UI RESTORED) -->
            <td>
              <?php 
                if($task->status == "To-Do"){
                    $sClass = "badge bg-secondary-subtle text-dark";
                } elseif($task->status == "In Progress"){
                    $sClass = "badge bg-primary-subtle border border-primary text-primary";
                } elseif($task->status == "Done"){
                    $sClass = "badge bg-success-subtle text-success";
                } elseif($task->status == "Archived"){
                    $sClass = "badge bg-dark-subtle text-dark";
                } else {
                    $sClass = "badge bg-secondary-subtle text-dark";
                }
              ?>
              <span class="<?= $sClass ?>"><?= $task->status ?></span>
            </td>

            <td><?= Yii::$app->formatter->asDate($task->due_date) ?: "—" ?></td>

            <td class="text-end">
              <a href="<?= Url::to(['task/view','id'=>$task->id]) ?>" class="btn btn-sm btn-outline-secondary">
                View
              </a>
            </td>

          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center py-3 text-muted">No tasks found</td></tr>
        <?php endif; ?>
        </tbody>

      </table>
    </div>
  </div>
</div>

<style>
.assignee-avatar{
    width:28px;height:28px;background:#4b4b4b;color:#fff;border-radius:50%;
    display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:bold
}
.assignee-name{
    font-weight:600;font-size:14px;color:#111
}
</style>
