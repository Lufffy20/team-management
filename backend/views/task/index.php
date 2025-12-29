<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use common\models\Task;
use yii\jui\DatePicker;
use yii\widgets\Pjax;


$this->title = 'Tasks';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <div class="card">

    <!-- Card Header -->
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><?= Html::encode($this->title) ?></h5>

      <?= Html::a('Create Task', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>

    <!-- Card Body -->
    <div class="card-body pt-3 pb-3">

      <div class="table-responsive text-nowrap">

      
        <?php Pjax::begin([
            'id' => 'task-grid-pjax',
            'enablePushState' => false, 
            'enableReplaceState' => false,
            'timeout' => 5000,
        ]); ?>

<?php echo Html::beginForm(
    ['index'],
    'get',
    [
        'data-pjax' => 1,
        'id' => 'pageSizeForm',
    ]
); ?>

<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <label class="me-2 fw-semibold">Show</label>

        <?= Html::dropDownList(
    'per-page',
    Yii::$app->request->get('per-page', 10),
    [
        10  => '10',
        25  => '25',
        50  => '50',
        100 => '100',
    ],
    [
        'class' => 'form-select form-select-sm d-inline-block w-auto',
        'onchange' => '
    $.pjax.reload({
        container: "#task-grid-pjax",
        url: "' . Url::to(['index']) . '",
        data: { "per-page": this.value },
        push: false,
        replace: false
    });
',

    ]
) ?>

        <span class="ms-2">entries</span>
    </div>
</div>

<?php echo Html::endForm(); ?>


        <?= GridView::widget([
          'dataProvider' => $dataProvider,
          'filterModel'  => $searchModel,

          'tableOptions' => [
            'class' => 'table table-bordered table-hover'
          ],

          'pager' => [
              'class' => 'yii\bootstrap5\LinkPager',
              'options' => ['class' => 'pagination justify-content-center mt-3'],
              'linkContainerOptions' => ['class' => 'page-item'],
              'linkOptions' => ['class' => 'page-link'],
              'prevPageLabel' => '<i class="bx bx-chevron-left"></i>',
              'nextPageLabel' => '<i class="bx bx-chevron-right"></i>',
              'firstPageLabel' => false,
              'lastPageLabel'  => false,
          ],

          'columns' => [

              [
                'class' => 'yii\grid\SerialColumn',
                'header' => '#',
                'headerOptions' => ['class' => 'p-3'],
                'contentOptions' => ['class' => 'p-3'],
              ],


              [
    'attribute' => 'id',
    'filter' => Html::activeTextInput($searchModel, 'id', [
        'class' => 'form-control',
        'placeholder' => 'Search ID'
    ]),
],

              // TASK TITLE
              [
                'attribute' => 'title',
                'label' => 'Task',
                'format' => 'raw',
                'value' => fn($model) => "<strong>" . Html::encode($model->title) . "</strong>",
                'headerOptions' => ['class' => 'p-3'],
                'contentOptions' => ['class' => 'p-3'],

                'filter' => Html::activeTextInput(
                  $searchModel,
                  'title',
                  [
                    'class' => 'form-control',
                    'placeholder' => 'Search task'
                  ]
              ),
              ],


              [
    'attribute' => 'board_id',
    'label' => 'Board',
    'value' => function ($model) {
        return $model->board ? $model->board->title : '—';
    },
    'filter' => Html::activeTextInput(
        $searchModel,
        'board_id',
        [
            'class' => 'form-control',
            'placeholder' => 'Board ID'
        ]
    ),
    'headerOptions' => ['class' => 'p-3'],
    'contentOptions' => ['class' => 'p-3'],
],


              [
    'attribute' => 'team_id',
    'label' => 'Team',
    'value' => function ($model) {
        return $model->team ? $model->team->name : '—';
    },
    'filter' => Html::activeTextInput(
        $searchModel,
        'team_id',
        [
            'class' => 'form-control',
            'placeholder' => 'Team ID'
        ]
    ),
    'headerOptions' => ['class' => 'p-3'],
    'contentOptions' => ['class' => 'p-3'],
],


              // ASSIGNED TO
              [
                'attribute' => 'assigned_to',
                'label' => 'Assigned To',
                'value' => function ($model) {
                    return $model->assignedUser 
                        ? $model->assignedUser->username
                        : '—';
                },
                'headerOptions' => ['class' => 'p-3'],
                'contentOptions' => ['class' => 'p-3'],

                'filter' => Html::activeTextInput(
    $searchModel,
    'assigned_to',
    [
        'class' => 'form-control',
        'placeholder' => 'Search user'
    ]
),

              ],



              // PRIORITY
              [
    'attribute' => 'priority',
    'format' => 'raw',

    'filter' => Html::activeDropDownList(
        $searchModel,
        'priority',
        [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        ],
        ['class' => 'form-select', 'prompt' => 'All']
    ),

    'value' => function ($model) {
        $colors = [
            'low'    => 'success',
            'medium' => 'warning',
            'high'   => 'danger',
        ];
        $color = $colors[$model->priority] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . ucfirst($model->priority) . '</span>';
    },

    'headerOptions' => ['class' => 'p-3'],
    'contentOptions' => ['class' => 'p-3'],
],


              // STATUS
              [
    'attribute' => 'status',
    'format' => 'raw',

    //  STATUS FILTER (dynamic – DB se)
    'filter' => Html::activeDropDownList(
        $searchModel,
        'status',
        \common\models\TaskSearch::getStatusList(),
        [
            'class' => 'form-select',
            'prompt' => 'All'
        ]
    ),

    'value' => fn($m) =>
        '<span class="badge bg-info">' . Html::encode($m->status) . '</span>',

    'headerOptions' => ['class' => 'p-3'],
    'contentOptions' => ['class' => 'p-3'],
],


              // DUE DATE
              [
    'attribute' => 'due_date',
    'label' => 'Due Date',

    // DATE PICKER FILTER
    'filter' => Html::input(
        'date',
        $searchModel->formName() . '[due_date]',
        $searchModel->due_date,
        ['class' => 'form-control']
    ),

    'value' => fn($m) =>
        $m->due_date ? date("d M Y", strtotime($m->due_date)) : '—',

    'headerOptions' => ['class' => 'p-3'],
    'contentOptions' => ['class' => 'p-3'],
],

[
    'attribute' => 'last_reminder_at',
    'label' => 'Last Reminder',
    'value' => function ($model) {
        return $model->last_reminder_at
            ? Yii::$app->formatter->asDatetime(
                $model->last_reminder_at,
                'php:d M Y, h:i A'
            )
            : '—';
    },
    'filter' => false,
],



              // ACTIONS DROPDOWN
              [
                  'class' => ActionColumn::class,
                  'header' => 'Actions',
                  'headerOptions' => ['class' => 'p-3'],
                  'contentOptions' => ['class' => 'p-3'],
                  'template' => '{actions}',

                  'buttons' => [
                      'actions' => function ($url, $model) {
                          return '
                          <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                              <i class="bx bx-dots-vertical-rounded fs-4"></i>
                            </button>
                            <div class="dropdown-menu">

                              <a class="dropdown-item" href="' . Url::to(['view', 'id' => $model->id]) . '">
                                <i class="bx bx-show me-1"></i> View
                              </a>

                              <a class="dropdown-item" href="' . Url::to(['update', 'id' => $model->id]) . '">
                                <i class="bx bx-edit-alt me-1"></i> Edit
                              </a>

                              <a class="dropdown-item text-danger"
                                href="' . Url::to(['delete', 'id' => $model->id]) . '"
                                data-method="post"
                                data-confirm="Are you sure you want to delete this task?">
                                <i class="bx bx-trash me-1"></i> Delete
                              </a>

                            </div>
                          </div>';
                      }
                  ]
              ],

          ],
        ]); ?>

        <?php Pjax::end(); ?>

      </div>

    </div>

  </div>

</div>
