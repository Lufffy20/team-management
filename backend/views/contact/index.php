<?php

use frontend\models\ContactMessage;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\ContactMessageSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Contact Messages';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container"> <!-- 🔥 Added container -->

    <div class="contact-message-index">

        <h1 class="mt-4"><?= Html::encode($this->title) ?></h1>

        <p>
            <?= Html::a('Create Contact Message', ['create'], ['class' => 'btn btn-success']) ?>
        </p>

        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'id',
                'name',
                'email:email',
                'subject',
                'body:ntext',
                // 'created_at',
                [
                    'class' => ActionColumn::className(),
                    'urlCreator' => function ($action, ContactMessage $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    }
                ],
            ],
        ]); ?>

    </div>

</div>
