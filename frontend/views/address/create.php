<?php

use yii\helpers\Html;

$this->title = 'Add Address';
?>

<h3><?= Html::encode($this->title) ?></h3>

<?= $this->render('_form', [
    'model' => $model,
]) ?>