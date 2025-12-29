<?php
use yii\helpers\Url;

$controller = Yii::$app->controller->id;
$action = Yii::$app->controller->action->id;        
?>

<div class="sidebar">
    <div class="logo mb-3">TeamTasks</div>

    <ul class="nav flex-column">

        <!-- DASHBOARD -->
        <li class="nav-item">
            <a href="<?= Url::to(['/']) ?>"
               class="nav-link <?= ($controller == 'site' && $action == 'index') ? 'active' : '' ?>">
                <i class="bi bi-grid"></i> Dashboard
            </a>
        </li>

        <!-- TASKS SECTION -->
        <li class="nav-item mt-2 text-muted small px-3">Tasks</li>

        <li class="nav-item">

            <a href="#"
   class="nav-link d-flex justify-content-between align-items-center
   <?= (
        $controller == 'task-user' || 
        ($controller == 'managment' && in_array($action, ['mytasks','team','members'])) ||
        $controller == 'board' || 
        ($controller == 'task' && $action == 'kanban')
      ) ? 'open' : '' ?>"
   data-bs-toggle="collapse"
   data-bs-target="#taskMenu"
   role="button"
   aria-expanded="<?= (
        $controller == 'task-user' || 
        ($controller == 'managment' && $action=='mytasks') ||
        $controller == 'board' || 
        ($controller=='task' && $action=='kanban')
      ) ? 'true' : 'false' ?>"
   data-bs-auto-close="false">

    <span><i class="bi bi-list-task"></i> Tasks</span>
    <i class="bi bi-chevron-down small"></i>
</a>


<ul id="taskMenu" 
    class="collapse ms-3 
   <?= (
        $controller=='task-user' || 
        ($controller=='managment' && $action=='mytasks') ||
        $controller=='board' || 
        ($controller=='task' && $action=='kanban')
      ) ? 'show' : '' ?>">

    <li>
        <a href="<?= Url::to(['task-user/index']) ?>"
           class="nav-link <?= ($controller=='task-user' && $action=='index') ? 'active' : '' ?>">
            All Tasks
        </a>
    </li>

    <li>
        <a href="<?= Url::to(['managment/mytasks']) ?>"
           class="nav-link <?= ($controller=='managment' && $action=='mytasks') ? 'active' : '' ?>">
            My Tasks
        </a>
    </li>


                <li>
                    <a href="<?= Url::to(['board/index']) ?>"
                       class="nav-link <?= ($controller == 'board') ? 'active' : '' ?>">
                        New Project
                    </a>
                </li>

                <li>
                    <a href="<?= Url::to(['task/kanban']) ?>"
                       class="nav-link <?= ($controller == 'task' && $action == 'kanban') ? 'active' : '' ?>">
                        Kanban Board
                    </a>
                </li>

            </ul>
        </li>

        <!-- TEAMS SECTION -->
<li class="nav-item mt-2 text-muted small px-3">Teams</li>

<li class="nav-item">

<a class="nav-link d-flex justify-content-between align-items-center
   <?= ($controller=='team') ? 'open' : '' ?>"
   data-bs-toggle="collapse"
   data-bs-target="#teamMenu"
   role="button"
   aria-expanded="<?= ($controller=='team') ? 'true' : 'false' ?>"
   data-bs-auto-close="false">

    <span><i class="bi bi-people"></i> Teams</span>
    <i class="bi bi-chevron-down small"></i>
</a>

<ul id="teamMenu" 
    class="collapse ms-3 <?= ($controller=='team') ? 'show' : '' ?>">

    <li>
        <a href="<?= Url::to(['team/index']) ?>"
           class="nav-link <?= ($controller=='team' && $action=='index') ? 'active' : '' ?>">
            Team List
        </a>
    </li>

    <li>
        <a href="<?= Url::to(['team/create']) ?>"
           class="nav-link <?= ($controller=='team' && $action=='create') ? 'active' : '' ?>">
            Add New Team
        </a>
    </li>

<li>
    <a href="<?= Url::to(['team/members']) ?>"
       class="nav-link <?= ($controller=='team' && $action=='members') ? 'active' : '' ?>">
        Members
    </a>
</li>


</ul>
</li>


        <!-- OTHERS -->
        <li class="nav-item mt-2 text-muted small px-3">Others</li>

        <a href="<?= Url::to(['setting/index']) ?>"
   class="nav-link <?= ($controller == 'setting') ? 'active' : '' ?>">
   <i class="bi bi-gear"></i> Settings
</a>


    </ul>
</div>
