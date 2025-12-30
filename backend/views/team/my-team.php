<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">My Teams</h4>

        <a href="<?= Url::to(['teammembers/create']) ?>" class="btn btn-primary btn-sm">
            + Add Teammembers
        </a>
    </div>

    <?php if (empty($teams)): ?>
        <div class="alert alert-warning">
            You have not created any teams yet.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($teams as $team): ?>
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm h-100">
                        <div class="card-body d-flex flex-column">

                            <h5 class="mb-2">
                                <?= Html::encode($team->name) ?>
                            </h5>

                            <p class="text-muted small flex-grow-1">
                                <?= $team->description
                                    ? Html::encode($team->description)
                                    : 'No description available.' ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <a href="<?= Url::to(['team/view', 'id' => $team->id]) ?>"
                                class="btn btn-sm btn-outline-primary">
                                    View Team
                                </a>

                                <span class="badge bg-label-secondary">
                                    ID: <?= $team->id ?>
                                </span>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
