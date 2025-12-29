<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;
use common\widgets\Alert;
use backend\assets\AppAsset;

AppAsset::register($this);

$this->title = "My Profile";
?>

<?= Alert::widget() ?>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- PAGE TITLE -->
  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Account /</span> My Profile
  </h4>

  <div class="row">

    <!-- LEFT: PROFILE PIC CARD -->
    <div class="col-md-4">
      <div class="card mb-4">
        <h5 class="card-header">Profile Picture</h5>

        <div class="card-body text-center">

          <!-- Current Avatar -->
          <div class="d-flex flex-column align-items-center mb-3">

            <?php if ($model->avatar): ?>
              <img src="<?= Yii::$app->request->baseUrl ?>/uploads/avatars/<?= $model->avatar ?>"
                    alt="avatar"
                    class="rounded-circle shadow-sm"
                    width="140" height="140"
                    style="object-fit: cover;">
            <?php else: ?>
              <img src="https://ui-avatars.com/api/?size=140&name=<?= $model->first_name . '+' . $model->last_name ?>"
                    class="rounded-circle shadow-sm"   
                    width="140" height="140">    
            <?php endif; ?>

            <small class="text-muted mt-2">Allowed: JPG, PNG, WEBP (Max 2MB)</small>
          </div>

          <!-- Upload Form -->
          <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data', 'class' => 'mt-3']
          ]); ?>

            <?= $form->field($model, 'avatarFile')->fileInput([
              'class' => 'form-control',
            ]) ?>

            <div class="d-flex justify-content-center gap-2 mt-3">

              <?= Html::submitButton(
                    '<i class="bi bi-cloud-arrow-up"></i> Upload',
                    ['class' => 'btn btn-primary']
                  ) ?>

            <?php if ($model->avatar): ?>
                <a href="<?= Url::to(['/user/delete-avatar']) ?>"
                class="btn btn-danger"
                onclick="return confirm('Are you sure you want to delete your profile photo?')">
                    <i class="bi bi-trash"></i> Delete
                </a>
            <?php endif; ?>

            </div>

          <?php ActiveForm::end(); ?>

        </div>
      </div>
    </div>

    <!-- RIGHT: PROFILE DETAILS -->
    <div class="col-md-8">
      <div class="card mb-4">
        <h5 class="card-header">Profile Details</h5>

        <div class="card-body">

          <?php $form = ActiveForm::begin(); ?>

          <div class="row">
            <div class="col-md-6 mb-3">
              <?= $form->field($model, 'first_name')->textInput([
                'class' => 'form-control',
                'placeholder' => 'Enter first name'
              ]) ?>
            </div>

            <div class="col-md-6 mb-3">
              <?= $form->field($model, 'last_name')->textInput([
                'class' => 'form-control',
                'placeholder' => 'Enter last name'
              ]) ?>
            </div>
          </div>

          <?= $form->field($model, 'username')->textInput([
            'class' => 'form-control',
            'placeholder' => 'Enter username'
          ]) ?>

          <!-- Email Field -->
          <div class="mb-3">
            <?= $form->field($model, 'email')->textInput([
              'class' => 'form-control',
              'placeholder' => 'Enter your email address'
            ]) ?>
          </div>

          <!-- Pending Email Alert (Frontend Style) -->
          <?php if ($model->pending_email): ?>
            <div class="alert alert-warning p-2 mt-2">
              Email verification pending for:
              <strong><?= $model->pending_email ?></strong><br>
              Please check your inbox to verify your new email.
            </div>
          <?php endif; ?>

          <div class="d-flex mt-4 gap-2">
            <?= Html::submitButton(
                  '<i class="bi bi-check-circle me-1"></i> Save Changes',
                  ['class' => 'btn btn-primary']
                ) ?>

            <a href="<?= Yii::$app->homeUrl ?>" class="btn btn-outline-secondary">
              Cancel
            </a>
          </div>

          <?php ActiveForm::end(); ?>

        </div>
      </div>
    </div>

  </div>
</div>
