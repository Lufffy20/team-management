<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Settings';
?>

<div class="container-fluid">
    <h4 class="mb-4 fw-bold">Settings</h4>

    <?php $form = ActiveForm::begin(); ?>

    <div class="card">
        <div class="card-header fw-semibold">
            Appearance
        </div>

        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Dark Mode</h6>
                <small class="text-muted">
                    Enable dark theme for better night experience
                </small>
            </div>

            <!-- HORIZONTAL TOGGLE -->
            <div class="form-check form-switch">
                <?= Html::activeCheckbox(
                    $model,
                    'theme',
                    [
                        'class' => 'form-check-input',
                        'id' => 'darkModeToggle',
                        'label' => false,
                        'value' => 'dark',
                        'checked' => ($model->theme === 'dark')
                    ]
                ) ?>
            </div>
        </div>
    </div>

    <div class="text-end mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary px-4']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>


<style>
    body.dark-mode {
    background-color: #121212;
    color: #e0e0e0;
}

body.dark-mode .card {
    background-color: #1e1e1e;
    border-color: #2c2c2c;
}

body.dark-mode .card-header {
    background-color: #1e1e1e;
    border-bottom: 1px solid #2c2c2c;
}

body.dark-mode .nav-link,
body.dark-mode .sidebar {
    background-color: #1a1a1a;
    color: #ddd;
}

</style>


<script>
document.getElementById('darkModeToggle')?.addEventListener('change', function () {
    if (this.checked) {
        document.body.classList.add('dark-mode');
    } else {
        document.body.classList.remove('dark-mode');
    }
});
</script>
