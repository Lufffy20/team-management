<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'View Address';
?>

<h3><?= Html::encode($this->title) ?></h3>

<p>
    <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Delete', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data' => [
            'confirm' => 'Are you sure?',
            'method' => 'post',
        ],
    ]) ?>
</p>

<?= DetailView::widget([
    'model' => $model,
    'attributes' => [
        'address_type',
        'address',
        'city',
        'state',
        'pincode',
    ],
]) ?>