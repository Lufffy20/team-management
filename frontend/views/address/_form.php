<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Address;

/** @var yii\widgets\ActiveForm $form */
?>

<div class="address-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'address_type')->dropDownList([
        Address::TYPE_HOME => 'Home',
        Address::TYPE_BILLING => 'Billing',
        Address::TYPE_SHIPPING => 'Shipping',
    ], ['prompt' => 'Select Address Type']) ?>

    <?= $form->field($model, 'address')->textarea(['rows' => 3]) ?>

    <?= $form->field($model, 'city')->textInput() ?>

    <?= $form->field($model, 'state')->textInput() ?>

    <?= $form->field($model, 'pincode')->textInput() ?>

    <div class="form-group mt-3">
        <?= Html::submitButton(
            $model->isNewRecord ? 'Save Address' : 'Update Address',
            ['class' => 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>