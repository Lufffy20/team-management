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
                    return '<i class="bx bx-user me-2"></i><strong>' . 
                            Html::encode($model->username) . '</strong>';
                },
                'headerOptions' => ['class' => 'p-3'],
                'contentOptions' => ['class' => 'p-3'],
              ],


              [
    'label' => 'Teams',
    'format' => 'raw',
    'value' => function ($model) {

        if (empty($model->teams)) {
            return '<span class="badge bg-secondary">No Team</span>';
        }

        $badges = [];

        foreach ($model->teams as $team) {
            $badges[] = '<span class="badge bg-info me-1">'
                . Html::encode($team->name)
                . '</span>';
        }

        return implode(' ', $badges);
    },
    'headerOptions' => ['class' => 'p-3'],
    'contentOptions' => ['class' => 'p-3'],
],


              [
                'attribute' => 'avatar',
                'format' => 'html',
                'value' => function ($model) {
                  if ($model->avatar) {
                      return Html::img(
                          Yii::$app->request->baseUrl . "/uploads/avatars/" . $model->avatar,
                          ['class' => 'rounded-circle', 'style' => 'width:40px; height:40px; object-fit:cover;']
                      );
                  }
                  return '<span class="badge bg-secondary">No Avatar</span>';
                },
                'headerOptions' => ['class' => 'p-3'],
                'contentOptions' => ['class' => 'p-3'],
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
