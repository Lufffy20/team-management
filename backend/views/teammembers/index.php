<?php

use common\models\TeamMembers;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Team Members';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card shadow-sm border-0 mb-4">

        <!-- HEADER -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><?= Html::encode($this->title) ?></h5>
            <?= Html::a('Create Team Member', ['create'], ['class' => 'btn btn-primary']) ?>
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
                    'prevPageLabel' => '<i class="bx bx-chevron-left"></i>',
                    'nextPageLabel' => '<i class="bx bx-chevron-right"></i>',
                    'firstPageLabel' => false,
                    'lastPageLabel' => false,
                ],

                'columns' => [

                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'id',
                        'contentOptions' => [
                            'style' => 'text-align:center; width:80px; font-weight:bold;'
                        ],
                        'filter' => Html::activeTextInput($searchModel, 'id', [
                            'class' => 'form-control',
                            'placeholder' => 'ID'
                        ]),
                    ],


                    // ⭐ TEAM NAME SEARCH (text input)
                    [
                        'attribute' => 'team_name',
                        'label' => 'Team',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->team
                                ? '<span class="badge bg-primary">' . $model->team->name . '</span>'
                                : 'N/A';
                        },
                        'filter' => Html::activeTextInput($searchModel, 'team_name', [
                            'class' => 'form-control',
                            'placeholder' => 'Search Team'
                        ]),
                        'contentOptions' => ['style' => 'font-weight:600;'],
                    ],

                    // ⭐ USER SEARCH (text input)
                    [
                        'attribute' => 'username',
                        'label' => 'User',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $user = $model->user->username ?? 'N/A';
                            $team = $model->team->name ?? 'N/A';
                            return "
                                <div style='line-height:18px'>
                                    <strong>$user</strong><br>
                                    <small class='text-muted'>Team: $team</small>
                                </div>
                            ";
                        },
                        'filter' => Html::activeTextInput($searchModel, 'username', [
                            'class' => 'form-control',
                            'placeholder' => 'Search User'
                        ]),
                    ],

                    // ⭐ ROLE SEARCH (dropdown)
                    [
                        'attribute' => 'role',
                        'format' => 'html',
                        'value' => function ($model) {
                            $color = $model->role === 'manager' ? 'success' : 'secondary';
                            return "<span class='badge bg-$color text-uppercase'>{$model->role}</span>";
                        },
                        'filter' => Html::activeDropDownList(
                            $searchModel,
                            'role',
                            [
                                'manager' => 'Manager',
                                'member'  => 'Member',
                            ],
                            [
                                'class' => 'form-select',
                                'prompt' => 'All Roles',
                            ]
                        ),
                        'contentOptions' => ['style' => 'text-align:center;'],
                    ],


                    // ⭐ ACTION DROPDOWN
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

                                            <a class="dropdown-item text-danger"
                                                href="' . Url::to(['delete', 'id' => $model->id]) . '"
                                                data-confirm="Are you sure you want to delete this member?"
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
