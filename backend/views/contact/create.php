<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\ContactMessage $model */

$this->title = 'Create Contact';
$this->params['breadcrumbs'][] = ['label' => 'Contact Messages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container">   <!-- 🔥 Added container -->

    <div class="contact-message-create">

        <h1 class="mt-4"><?= Html::encode($this->title) ?></h1>

        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>

    </div>

</div>
