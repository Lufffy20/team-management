<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Address;
use frontend\assets\AppAsset;

AppAsset::register($this);

$this->title = 'My Addresses';
?>

<div class="container-xxl py-3">

    <!-- Page Title + Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><?= Html::encode($this->title) ?></h4>

        <?= Html::a(
            '<i class="bi bi-plus-circle"></i> Add Address',
            ['create'],
            ['class' => 'btn btn-primary']
        ) ?>
    </div>

    <!-- Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">

            <div id="address-grid">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout' => "{items}\n{pager}",
                    'tableOptions' => [
                        'class' => 'table table-hover align-middle mb-0'
                    ],
                    'pager' => [
                        'class' => yii\widgets\LinkPager::class,
                        'options' => ['class' => 'pagination justify-content-center mb-0'],
                        'linkContainerOptions' => ['class' => 'page-item'],
                        'linkOptions' => ['class' => 'page-link'],
                        'disabledListItemSubTagOptions' => ['class' => 'page-link'],
                    ],

                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'header' => '#',
                            'contentOptions' => ['class' => 'text-muted'],
                        ],

                        [
                            'attribute' => 'address_type',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $badges = [
                                    Address::TYPE_HOME => 'primary',
                                    Address::TYPE_BILLING => 'success',
                                    Address::TYPE_SHIPPING => 'warning',
                                ];

                                return Html::tag(
                                    'span',
                                    ucfirst($model->address_type),
                                    ['class' => 'badge bg-' . ($badges[$model->address_type] ?? 'secondary')]
                                );
                            },
                        ],

                        [
                            'attribute' => 'address',
                            'contentOptions' => ['style' => 'max-width:300px; white-space:normal;'],
                        ],

                        'city',
                        'state',
                        'pincode',

                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'Actions',
                            'contentOptions' => ['class' => 'text-nowrap'],
                            'buttons' => [
                                'view' => function ($url) {
                                    return Html::a(
                                        '<i class="bi bi-eye"></i>',
                                        $url,
                                        ['class' => 'btn btn-sm btn-outline-secondary me-1', 'title' => 'View']
                                    );
                                },
                                'update' => function ($url) {
                                    return Html::a(
                                        '<i class="bi bi-pencil"></i>',
                                        $url,
                                        ['class' => 'btn btn-sm btn-outline-primary me-1', 'title' => 'Edit']
                                    );
                                },
                                'delete' => function ($url, $model) {
                                    return Html::a(
                                        '<i class="bi bi-trash"></i>',
                                        'javascript:void(0);',
                                        [
                                            'class' => 'btn btn-sm btn-outline-danger btn-delete-address',
                                            'title' => 'Delete',
                                            'data-id' => $model->id,
                                        ]
                                    );
                                },

                            ],
                        ],
                    ],
                ]); ?>

            </div>
        </div>
    </div>

    <?php

    use yii\helpers\Url;

    $this->registerJs("
    window.addressDeleteUrl = '" . Url::to(['address/delete']) . "';
", \yii\web\View::POS_HEAD);
    ?>