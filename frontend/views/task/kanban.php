<?php
/**
 * @var yii\web\View $this
 * @var common\models\Task[][] $tasks
 * @var array|common\models\KanbanColumn[] $columns
 */

use yii\helpers\Url;
use frontend\assets\AppAsset;

AppAsset::register($this);


// FETCH boards for both owner + members
$teamBoards = \common\models\TeamMembers::find()
    ->select('team_id')
    ->where(['user_id' => Yii::$app->user->id])
    ->column();

$boards = \common\models\Board::find()
    ->where(['created_by' => Yii::$app->user->id])
    ->orWhere(['team_id' => $teamBoards])
    ->all();

$currentBoard = $boardId ?? null;
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
?>

<?php if (empty($boards)): ?>
    <div class="text-center my-5 p-4 border rounded bg-light shadow-sm">
        <h3 class="fw-bold mb-2">No Boards Found</h3>
        <p class="text-muted mb-3">Create your first board to start using Kanban.</p>
        <a href="<?= Url::to(['/board/create']) ?>" class="btn btn-primary btn-lg">+ Create Board</a>
    </div>
<?php return; endif; ?>

<?php
$this->registerJs("
    window.KANBAN_CREATE_URL = '" . Url::to(['/task/create-ajax']) . "';
    window.KANBAN_UPDATE_URL = '" . Url::to(['/task/update-status']) . "';
    window.KANBAN_VIEW_URL   = '" . Url::to(['/task/view-ajax']) . "';
    window.KANBAN_COLUMN_ORDER_URL = '" . Url::to(['/task/update-column-order']) . "';
    window.KANBAN_CSRF = '" . Yii::$app->request->csrfToken . "';
    window.ROLE = '$role';
", \yii\web\View::POS_HEAD);
?>


<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
        <h4 class="mb-0 fw-bold">Kanban Board</h4>

        <select class="form-select" style="width:180px"
                onchange="location.href='?board_id='+this.value">
            <?php foreach ($boards as $board): ?>
                <option value="<?= $board->id ?>" <?= $board->id == $currentBoard ? 'selected' : '' ?>>
                    <?= $board->title ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- 🔥 Task Create = allowed for ALL (owner/manager/member) -->
    <?php if(in_array($role,['owner','manager','member'])): ?>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#taskModal">
            + New Task
        </button>
    <?php endif; ?>

</div>




<div class="kanban-wrapper">

<?php
if (!empty($columns) && is_object(current($columns))) {
    $renderColumns = [];
    foreach ($columns as $colObj) {
        $renderColumns[] = [
            'status' => $colObj->status,
            'label'  => $colObj->label,
            'badge'  => ($colObj->status == 'todo') ? 'bg-secondary' :
                        (($colObj->status == 'in_progress') ? 'bg-primary' :
                        (($colObj->status == 'done') ? 'bg-success' : 'bg-dark')),
        ];
    }
} else {
    $renderColumns = [
        ['status' => 'todo',         'label' => 'To-Do',     'badge' => 'bg-secondary'],
        ['status' => 'in_progress',  'label' => 'In Progress','badge' => 'bg-primary'],
        ['status' => 'done',         'label' => 'Done',       'badge' => 'bg-success'],
        ['status' => 'archived',     'label' => 'Archived',   'badge' => 'bg-dark'],
    ];
}
?>


<?php foreach ($renderColumns as $col): ?>
    <?php
        $status = $col['status'];
        $label  = $col['label'];
        $badge  = $col['badge'];
        $columnTasks = $tasks[$status] ?? [];
    ?>

    <div class="kanban-column" data-status="<?= $status ?>" data-board="<?= $currentBoard ?>" draggable="true">
        <div class="card shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="kanban-column-header"><?= $label ?></div>
                    <span class="badge <?= $badge ?> column-count"><?= count($columnTasks) ?></span>
                </div>

                <div class="text-muted small mb-3">
                    <?= $status === 'todo' ? 'Tasks not started'
                        : ($status === 'in_progress' ? 'Busy working'
                        : ($status === 'done' ? 'Completed' : 'Archived')) ?>
                </div>

                <div class="kanban-tasks-dropzone">
                    <?php foreach ($columnTasks as $task): ?>
                        <?= $this->render('taskcard', ['model' => $task]) ?>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </div>

<?php endforeach; ?>
</div>


<!-- ================= CREATE TASK MODAL ================= -->
<div class="modal fade" id="taskModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="taskCreateForm"
          class="modal-content"
          method="post"
          enctype="multipart/form-data"
          onsubmit="return false;">

      <input type="hidden" name="<?= $csrfParam ?>" value="<?= $csrfToken ?>">

      <div class="modal-header">
        <h5 class="modal-title">New Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- TITLE -->
        <div class="mb-3">
          <label class="form-label">Title</label>
          <input type="text" name="Task[title]" class="form-control" required>
        </div>

        <!-- DESCRIPTION -->
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="Task[description]" class="form-control" rows="3"></textarea>
        </div>

        <!-- PRIORITY -->
        <div class="mb-3">
          <label class="form-label">Priority</label>
          <select name="Task[priority]" class="form-select">
            <option value="low">Low</option>
            <option value="medium" selected>Medium</option>
            <option value="high">High</option>
          </select>
        </div>

        <!-- DUE DATE -->
        <div class="mb-3">
          <label class="form-label">Due Date</label>
          <input type="date" name="Task[due_date]" class="form-control">
        </div>

        <!-- 🔥 ATTACHMENTS (IMAGES + FILES) -->
        <div class="mb-3">
          <label class="form-label">
            Attach Files
            <small class="text-muted"></small>
          </label>
          <input type="file"
                 name="Task[attachmentFiles][]"
                 class="form-control"
                 multiple
                 accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx">
        </div>

        <!-- HIDDEN -->
        <input type="hidden" name="Task[board_id]" value="<?= $currentBoard ?>">
        <input type="hidden" name="Task[status]" value="todo">

      </div>

      <div class="modal-footer">
        <button type="button"
                class="btn btn-light"
                data-bs-dismiss="modal">
          Cancel
        </button>

        <button type="submit"
                class="btn btn-primary">
          Create Task
        </button>
      </div>

    </form>
  </div>
</div>


<!-- VIEW TASK -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Task Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="task-details-body">
        <div class="text-center py-4 text-muted">Loading...</div>
      </div>
    </div>
  </div>
</div>



<?php
$this->registerJsFile(
    '@web/teammanagment/js/kanban.js',
    ['depends' => [\yii\web\JqueryAsset::class], 'position' => \yii\web\View::POS_END]
);

$this->registerJs("
    window.KANBAN_DELETE_IMAGE_URL = '" . \yii\helpers\Url::to(['/task/delete-image']) . "';
", \yii\web\View::POS_HEAD);

?>

