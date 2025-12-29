<?php

use common\models\Team;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var backend\models\TeamSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Teams';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-xxl py-4"> 

    <div class="team-index card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>

            <?= Html::a('Create Team', ['create'], ['class' => 'btn btn-success']) ?>
        </div>

        <div class="card-body">

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

    'tableOptions' => ['class' => 'table table-bordered table-hover'],

    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        [
    'attribute' => 'id',
    'filter' => Html::activeTextInput(
        $searchModel,
        'id',
        ['class' => 'form-control', 'placeholder' => 'Search ID']
    ),
],


        [
    'attribute' => 'name',
    'filter' => Html::activeTextInput(
        $searchModel,
        'name',
        ['class' => 'form-control', 'placeholder' => 'Search team']
    ),
],


        [
    'attribute' => 'description',
    'filter' => Html::activeTextInput(
        $searchModel,
        'description',
        ['class' => 'form-control', 'placeholder' => 'Search description']
    ),
],


[
    'label' => 'Team Members',
    'format' => 'raw',
    'value' => function ($model) {

        if (empty($model->members)) {
            return '<span class="text-muted">No members</span>';
        }

        $names = [];

        foreach ($model->members as $member) {
            if ($member->user) {
                $names[] = Html::encode($member->user->username);
            }
        }

        return implode(', ', $names);
    },
],



        [
    'attribute' => 'created_by_username',
    'label' => 'Created By',
    'value' => fn($model) =>
        $model->creator ? $model->creator->username : 'N/A',
    'filter' => Html::activeTextInput(
        $searchModel,
        'created_by_username',
        ['class' => 'form-control', 'placeholder' => 'Search user']
    ),
],

        [
    'label' => 'Created At',
    'attribute' => 'created_at_date',
    'value' => fn($model) =>
        Yii::$app->formatter->asDatetime(
            $model->created_at,
            'php:d M Y, h:i A'
        ),
    'filter' => Html::activeInput(
        'date',
        $searchModel,
        'created_at_date',
        ['class' => 'form-control']
    ),
],


        [
            'class' => ActionColumn::class,
        ],
    ],
]); ?>
<?php Pjax::end(); ?>


        </div>
    </div>

</div>
