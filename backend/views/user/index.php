<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use common\models\User;
use yii\widgets\Pjax;

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <div class="card">

    <!-- Card Header -->
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><?= Html::encode($this->title) ?></h5>

      <?= Html::a('Create User', ['create'], ['class' => 'btn btn-primary']) ?>
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
          'filterModel' => $searchModel,

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
            'lastPageLabel' => false,
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
              'headerOptions' => ['class' => 'p-3'],
              'contentOptions' => ['class' => 'p-3'],
            ],

            [
              'attribute' => 'first_name',
              'headerOptions' => ['class' => 'p-3'],
              'contentOptions' => ['class' => 'p-3'],
            ],

            [
              'attribute' => 'last_name',
              'headerOptions' => ['class' => 'p-3'],
              'contentOptions' => ['class' => 'p-3'],
            ],


            [
              'attribute' => 'username',
              'format' => 'raw',
              'value' => function ($model) {

                $avatarUrl = Yii::$app->avatar->get($model);

                return '
        <div class="d-flex align-items-center gap-2">
            <img
                src="' . $avatarUrl . '"
                class="rounded-circle shadow-sm"
                width="40"
                height="40"
                style="object-fit:cover"
            >
            <strong>' . Html::encode($model->username) . '</strong>
        </div>';
              },
            ],



            [
              'class' => ActionColumn::class,
              'header' => 'Action',
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
                          data-confirm="Are you sure you want to delete this user?">
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