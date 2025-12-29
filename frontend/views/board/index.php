<h3>My Projects</h3>

<a href="/board/create" class="btn btn-primary mb-3">+ New Project</a>

<div class="row">
<?php foreach ($boards as $board): ?>

  <?php
      // find role
      if ($board->created_by == Yii::$app->user->id) {
          $role = "owner";
      } else {
          $tm = \common\models\TeamMembers::findOne([
              'team_id' => $board->team_id,
              'user_id' => Yii::$app->user->id
          ]);
          $role = $tm ? $tm->role : "guest";
      }
  ?>

<div class="col-md-3">
    <div class="card p-3 mb-3">

      <h5 class="fw-bold"><?= $board->title ?></h5>
      <p class="text-muted small"><?= $board->description ?: "No description available" ?></p>

      <!-- OPEN KANBAN -->
      <a href="/task/kanban?board_id=<?= $board->id ?>" class="btn btn-info btn-sm w-100 mb-2">
        Open Board
      </a>

      <!-- VIEW PROJECT -->
      <a href="/board/view?id=<?= $board->id ?>" class="btn btn-secondary btn-sm w-100 mb-2">
        View Project
      </a>

      <!-- DELETE only if owner -->
      <?php if($role == 'owner'): ?>
      <a href="/board/delete?id=<?= $board->id ?>"
         class="btn btn-danger btn-sm w-100"
         onclick="return confirm('Delete board?');">
         Delete
      </a>
      <?php endif; ?>

    </div>
</div>


<?php endforeach; ?>
</div>
