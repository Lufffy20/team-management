<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\ArrayHelper;
use common\models\Task;
use common\models\Board;
use yii\widgets\Pjax;

$this->title = 'Tasks';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= Html::encode($this->title) ?></h5>
            <?= Html::a('Create Task', ['create'], ['class' => 'btn btn-primary']) ?>
        </div>

        <div class="card-body pt-3 pb-3">

            <div class="table-responsive text-nowrap">

            <?php Pjax::begin([
          'id' => 'task-grid-pjax',
          'enablePushState' => false, 
          'enableReplaceState' => false,
          'timeout' => 5000,
      ]); ?>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel'  => $searchModel,

                    'tableOptions' => ['class' => 'table table-bordered table-hover align-middle'],

                    'pager' => [
                        'class' => \yii\bootstrap5\LinkPager::class,
                        'options' => ['class' => 'pagination justify-content-center mt-4'],
                        'pageCssClass' => 'page-item',
                        'activePageCssClass' => 'active',
                        'disabledPageCssClass' => 'disabled',
                        'linkOptions' => ['class' => 'page-link'],
                        'prevPageLabel' => '&laquo;',
                        'nextPageLabel' => '&raquo;',
                        'firstPageLabel' => false,
                        'lastPageLabel' => false,
                    ],

                    'columns' => [

                        /* ================= SERIAL ================= */
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'header' => '#',
                            'headerOptions' => ['class' => 'p-3'],
                            'contentOptions' => ['class' => 'p-3'],
                        ],

                        /* ================= TASK TITLE ================= */
                        [
                            'attribute' => 'title',
                            'label' => 'Task',
                            'format' => 'raw',
                            'value' => fn($m) => '<b>' . Html::encode($m->title) . '</b>',
                            'filterInputOptions' => [
                                'class' => 'form-control',
                                'placeholder' => 'Search task'
                            ],
                            'headerOptions' => ['class' => 'p-3'],
                            'contentOptions' => ['class' => 'p-3'],
                        ],

                        /* ================= BOARD ================= */
                        [
                            'attribute' => 'board_id',
                            'label' => 'Board',
                            'value' => fn($m) => $m->board ? $m->board->title : '—',

                            'filter' => Html::activeDropDownList(
                                $searchModel,
                                'board_id',
                                ArrayHelper::map(
                                    Board::find()
                                        ->joinWith('team.members')
                                        ->where(['team_members.user_id' => Yii::$app->user->id])
                                        ->all(),
                                    'id',
                                    'title'
                                ),
                                [
                                    'class' => 'form-select',
                                    'prompt' => 'All Boards'
                                ]
                            ),

                            'headerOptions' => ['class' => 'p-3'],
                            'contentOptions' => ['class' => 'p-3'],
                        ],

                        /* ================= TEAM ================= */
                        [
                            'label' => 'Team',
                            'value' => fn($m) =>
                                ($m->board && $m->board->team)
                                    ? $m->board->team->name
                                    : '—',
                            'filter' => false,
                            'headerOptions' => ['class' => 'p-3'],
                            'contentOptions' => ['class' => 'p-3'],
                        ],

                        /* ================= ASSIGNED TO ================= */
                        [
    'attribute' => 'assigned_to',
    'label' => 'Assigned To',

    'value' => fn($m) =>
        $m->assignedUser
            ? $m->assignedUser->first_name . ' ' . $m->assignedUser->last_name
            : '—',

    /*
     * FILTER: Only users who are members of ANY team
     * where current user is present
     */
    'filter' => Html::activeDropDownList(
        $searchModel,
        'assigned_to',
        \yii\helpers\ArrayHelper::map(
            \common\models\User::find()
                ->joinWith(['teamMembers tm'])
                ->joinWith(['teamMembers.team t'])
                ->where(['tm.user_id' => Yii::$app->user->id])
                ->groupBy('user.id')
                ->all(),
            'id',
            function ($u) {
                return trim($u->first_name . ' ' . $u->last_name);
            }
        ),
        [
            'class' => 'form-select',
            'prompt' => 'All Members'
        ]
    ),

    'headerOptions' => ['class' => 'p-3'],
    'contentOptions' => ['class' => 'p-3'],
],

                        /* ================= PRIORITY ================= */
                        [
                            'attribute' => 'priority',
                            'format' => 'raw',
                            'value' => function ($m) {
                                $c = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger'];
                                return $m->priority
                                    ? "<span class='badge bg-{$c[$m->priority]}'>" . ucfirst($m->priority) . "</span>"
                                    : '—';
                            },

                            'filter' => [
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ],

                            'filterInputOptions' => [
                                'class' => 'form-select',
                                'prompt' => 'All Priorities'
                            ],

                            'headerOptions' => ['class' => 'p-3'],
                            'contentOptions' => ['class' => 'p-3'],
                        ],

                        /* ================= STATUS ================= */
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => fn($m) =>
                                $m->status
                                    ? "<span class='badge bg-info'>" . Html::encode($m->status) . "</span>"
                                    : '—',

                            'filter' => [
                                Task::STATUS_TODO => 'To Do',
                                Task::STATUS_IN_PROGRESS => 'In Progress',
                                Task::STATUS_DONE => 'Done',
                                Task::STATUS_ARCHIVED => 'Archived',
                            ],

                            'filterInputOptions' => [
                                'class' => 'form-select',
                                'prompt' => 'All Status'
                            ],

                            'headerOptions' => ['class' => 'p-3'],
                            'contentOptions' => ['class' => 'p-3'],
                        ],

                        /* ================= DUE DATE ================= */
                        [
    'attribute' => 'due_date',
    'label' => 'Due Date',
    'format' => 'raw',
    'value' => fn($m) =>
        $m->due_date ? date("d M Y", strtotime($m->due_date)) : '—',

    'filter' => Html::activeInput(
        'date',
        $searchModel,
        'due_date',
        [
            'class' => 'form-control',
            'placeholder' => 'Select date'
        ]
    ),

    'headerOptions' => ['class' => 'p-3'],
    'contentOptions' => ['class' => 'p-3'],
],


                        /* ================= ACTIONS ================= */
                        [
                            'class' => ActionColumn::class,
                            'header' => 'Actions',
                            'template' => '{menu}',
                            'headerOptions' => ['class' => 'p-2'],
                            'contentOptions' => ['style' => 'width:60px;text-align:center;'],
                            'buttons' => [
                                'menu' => function ($url, $model) {
                                    return '
                                    <div class="dropdown">
                                        <a href="#" data-bs-toggle="dropdown"
                                           style="font-size:22px;color:#444;text-decoration:none;">⋮</a>

                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item" href="' . Url::to(['view', 'id' => $model->id]) . '">
                                                    View
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="' . Url::to(['update', 'id' => $model->id]) . '">
                                                    Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger"
                                                   data-method="post"
                                                   data-confirm="Delete this task?"
                                                   href="' . Url::to(['delete', 'id' => $model->id]) . '">
                                                    Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>';
                                }
                            ]
                        ],

                    ]
                ]); ?>
                <?php Pjax::end(); ?>

            </div>
        </div>
    </div>
</div>
