<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Upcoming Tasks (Personal + Team)</h6>
        <a href="<?= Url::to(['/task/list']) ?>" class="small">View all</a>
    </div>

    <div class="card-body p-0">
        <ul class="list-group list-group-flush">

            <?php if(!empty($myTasks)): ?>
                <?php foreach($myTasks as $t): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">

                    <div>
                        <div class="fw-semibold"><?= Html::encode($t->title) ?></div>

                        <div class="text-muted small mt-1">
                            <?php
                                $source = ($t->created_by == Yii::$app->user->id) ? "My Task" : "Team Task";
                                $badgeColor = $source=="My Task" ? "success" : "primary";
                            ?>
                            <span class="badge bg-<?= $badgeColor ?>"><?= $source ?></span>
                            • Board: <?= $t->board->title ?? "No Board" ?>
                            • Assigned to <?= $t->assignee->username ?? "No Assignee" ?>
                        </div>
                    </div>

                    <div class="text-end">
                        <?php 
                            $color = $t->priority=='high'?'danger':
                                    ($t->priority=='medium'?'warning':'secondary');
                        ?>
                        <span class="badge bg-<?= $color ?>-subtle border border-<?= $color ?> text-<?= $color ?>">
                            <?= ucfirst($t->priority) ?>
                        </span>

                        <div class="text-muted small mt-1">
                            Due: <?= date('d M', strtotime($t->due_date)) ?>
                        </div>
                    </div>

                </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="list-group-item text-center text-muted">No upcoming tasks</li>
            <?php endif; ?>

        </ul>
    </div>
</div>
