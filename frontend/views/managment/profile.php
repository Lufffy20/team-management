<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;
use common\widgets\Alert;
use frontend\assets\AppAsset;

AppAsset::register($this);
$this->registerJsFile(
  '@web/js/profile-address.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->title = "My Profile";
?>

<?= Alert::widget() ?>

<div class="container-xxl flex-grow-1 container-p-y">

  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Account /</span> My Profile
  </h4>

  <div class="row">

    <!-- LEFT: PROFILE PIC -->
    <div class="col-md-4">
      <div class="card mb-4">
        <h5 class="card-header">Profile Picture</h5>

        <div class="card-body text-center">

          <!-- AVATAR DISPLAY -->
          <img
            src="<?= Yii::$app->avatar->get($user) ?>"
            class="rounded-circle shadow-sm mb-3"
            width="140"
            height="140"
            style="object-fit: cover;"
            alt="Profile Photo">

          <!-- UPLOAD FORM -->
          <?php $form = ActiveForm::begin([
            'action' => Url::to(['/managment/profile-picture']),
            'options' => ['enctype' => 'multipart/form-data']
          ]); ?>

          <?= $form->field($user, 'avatarFile')->fileInput([
            'accept' => 'image/*'
          ])->label(false) ?>

          <div class="d-flex justify-content-center gap-2">
            <?= Html::submitButton('Upload', ['class' => 'btn btn-primary btn-sm']) ?>

            <?php if (!empty($user->avatar)): ?>
              <?= Html::a(
                'Delete',
                ['/managment/delete-avatar'],
                [
                  'class' => 'btn btn-outline-danger btn-sm',
                  'data' => [
                    'confirm' => 'Are you sure you want to delete your profile photo?',
                    'method' => 'post',
                  ],
                ]
              ) ?>
            <?php endif; ?>
          </div>

          <?php ActiveForm::end(); ?>

        </div>
      </div>
    </div>


    <!-- RIGHT -->
    <div class="col-md-8">

      <!-- PROFILE DETAILS -->
      <div class="card mb-4">
        <h5 class="card-header">Profile Details</h5>
        <div class="card-body">

          <?php $form = ActiveForm::begin(); ?>

          <?= $form->field($model, 'first_name') ?>
          <?= $form->field($model, 'last_name') ?>
          <?= $form->field($model, 'username') ?>
          <?= $form->field($model, 'email') ?>

          <?= $form->field($model, 'address')->textInput(['id' => 'user-address']) ?>
          <?= $form->field($model, 'city')->textInput(['id' => 'user-city']) ?>
          <?= $form->field($model, 'state')->textInput(['id' => 'user-state']) ?>
          <?= $form->field($model, 'pincode')->textInput(['id' => 'user-pincode']) ?>

          <?= Html::submitButton('Save Profile', ['class' => 'btn btn-primary']) ?>

          <?php ActiveForm::end(); ?>
        </div>
      </div>

      <!-- BILLING -->
      <div class="card mb-4">
        <h5 class="card-header">Billing Address</h5>
        <div class="card-body">

          <input type="checkbox" id="sameAsHomeBilling">
          <label for="sameAsHomeBilling">Same as Home Address</label>

          <?php $billingForm = ActiveForm::begin([
            'id' => 'billing-form',
            'action' => Url::to(['/managment/update-billing-address'])
          ]); ?>

          <?= $billingForm->field($billingAddress, 'address') ?>
          <?= $billingForm->field($billingAddress, 'city') ?>
          <?= $billingForm->field($billingAddress, 'state') ?>
          <?= $billingForm->field($billingAddress, 'pincode') ?>

          <?= Html::submitButton('Save Billing', ['class' => 'btn btn-primary']) ?>

          <?php ActiveForm::end(); ?>
        </div>
      </div>

      <!-- SHIPPING -->
      <div class="card mb-4">
        <h5 class="card-header">Shipping Address</h5>
        <div class="card-body">

          <input type="checkbox" id="sameAsHomeShipping">
          <label for="sameAsHomeShipping">Same as Home Address</label>

          <?php $shippingForm = ActiveForm::begin([
            'id' => 'shipping-form',
            'action' => Url::to(['/managment/update-shipping-address'])
          ]); ?>

          <?= $shippingForm->field($shippingAddress, 'address') ?>
          <?= $shippingForm->field($shippingAddress, 'city') ?>
          <?= $shippingForm->field($shippingAddress, 'state') ?>
          <?= $shippingForm->field($shippingAddress, 'pincode') ?>

          <?= Html::submitButton('Save Shipping', ['class' => 'btn btn-primary']) ?>

          <?php ActiveForm::end(); ?>
        </div>
      </div>

    </div>
  </div>
</div>