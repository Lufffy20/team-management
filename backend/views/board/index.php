<?php

use common\models\Board;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;


$this->title = 'Boards';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card shadow-sm border-0 mb-4">
        
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><?= Html::encode($this->title) ?></h5>
            <?= Html::a('Create Board', ['create'], ['class' => 'btn btn-primary']) ?>
        </div>

        <div class="card-body pt-3">

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
                'filterModel' => $searchModel,
                'summary' => false,

                'tableOptions' => [
                    'class' => 'table table-striped table-bordered align-middle'
                ],

                'pager' => [
                    'class' => 'yii\bootstrap5\LinkPager',
                    'options' => ['class' => 'pagination justify-content-center mt-3'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link'],
                ],

                'columns' => [

                    ['class' => 'yii\grid\SerialColumn'],

                    [
    'attribute' => 'id',
    'filter' => Html::activeTextInput($searchModel, 'id', [
        'class' => 'form-control',
        'placeholder' => 'Search ID'
    ]),
],


                    [
                        'attribute' => 'title',
                        'contentOptions' => ['style' => 'font-weight:600;'],
                        'filter' => Html::activeTextInput($searchModel, 'title', [
                            'class' => 'form-control',
                            'placeholder' => 'Search Title'
                        ]),
                    ],

                    // ⭐ Short Description
                    [
                        'attribute' => 'description',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return strlen($model->description) > 50
                                ? substr($model->description, 0, 50) . '...'
                                : $model->description;
                        },
                        'contentOptions' => ['style' => 'width:25%;'],
                        'filter' => Html::activeTextInput($searchModel, 'description', [
                            'class' => 'form-control',
                            'placeholder' => 'Search Description'
                        ]),
                    ],

                    // ⭐ Tasks Count
                    [
                        'label' => 'Tasks',
                        'value' => function ($model) {
                            return $model->getTasks()->count();
                        },
                        'contentOptions' => ['style' => 'text-align:center; font-weight:bold;'],
                        'filter' => false, // No search
                    ],

                    // ⭐ Team Name (SEARCHABLE)
                    [
                        'attribute' => 'team_name',
                        'label' => 'Team',
                        'value' => fn($m) => $m->team->name ?? 'N/A',
                        'filter' => Html::activeTextInput($searchModel, 'team_name', [
                            'class' => 'form-control',
                            'placeholder' => 'Search Team'
                        ]),
                    ],

                    [
    'label' => 'Team Members',
    'format' => 'raw',
    'value' => function ($model) {

        if (!$model->team || empty($model->team->members)) {
            return '<span class="badge bg-secondary">No Members</span>';
        }

        $badges = [];

        foreach ($model->team->members as $member) {
            if ($member->user) {
                $badges[] =
                    '<span class="badge bg-info me-1">'
                    . Html::encode($member->user->username)
                    . '</span>';
            }
        }

        return implode(' ', $badges);
    },
    'contentOptions' => ['style' => 'white-space:normal;'],
    'filter' => false, // members pe search nahi
],


                    // ⭐ Created By (SEARCHABLE)
                    [
                        'attribute' => 'created_by_username',
                        'label' => 'Created By',
                        'value' => function ($model) {
                            return $model->createdBy->username ?? 'N/A';
                        },
                        'filter' => Html::activeTextInput($searchModel, 'created_by_username', [
                            'class' => 'form-control',
                            'placeholder' => 'Search User'
                        ]),
                    ],

                    // ⭐ Created Date
                    [
    'attribute' => 'created_at',
    'value' => fn($m) => date('d M Y', $m->created_at),
    'filter' => Html::activeInput('date', $searchModel, 'created_at', [
        'class' => 'form-control',
    ]),
],

                    // ⭐ Actions
                    [
                        'class' => ActionColumn::class,
                        'header' => 'Actions',
                        'template' => '{actions}',
                        'contentOptions' => ['style' => 'white-space:nowrap; text-align:center;'],

                        'buttons' => [
                            'actions' => function ($url, $model) {
                                return '
                                    <div class="dropdown">
                                        <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="' . Url::to(['view', 'id' => $model->id]) . '">
                                                <i class="bx bx-show me-1"></i> View
                                            </a>
                                            <a class="dropdown-item" href="' . Url::to(['update', 'id' => $model->id]) . '">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                            <a class="dropdown-item text-danger" href="' . Url::to(['delete', 'id' => $model->id]) . '" 
                                                data-confirm="Are you sure you want to delete this board?" 
                                                data-method="post">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                ';
                            }
                        ]
                    ],
                ],
            ]); ?>
            <?php Pjax::end(); ?>

        </div>
    </div>
</div>

<?= Html::a(
    'Clear Filters',
    ['index'],
    ['class' => 'btn btn-outline-secondary ms-2']
) ?>
