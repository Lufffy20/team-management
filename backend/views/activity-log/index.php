<?php
use yii\helpers\Html;

$this->title = "Activity Logs";
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row">
        <div class="col-12">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0">Activity Logs</h3>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">

                    <?php if (empty($logs)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bx bx-file fs-1"></i>
                            <div class="mt-2">No activity logs available</div>
                        </div>
                    <?php else: ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%">User</th>
                                    <th style="width: 15%">Action</th>
                                    <th style="width: 30%">Details</th>
                                    <th style="width: 15%">Team</th>
                                    <th style="width: 15%">Board</th>
                                    <th style="width: 10%">Time</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($logs as $log): ?>

                                <?php
                                    $badge = "bg-primary";
                                    if (str_contains($log->action, 'Created')) $badge = "bg-success";
                                    if (str_contains($log->action, 'Updated')) $badge = "bg-warning text-dark";
                                    if (str_contains($log->action, 'Deleted')) $badge = "bg-danger";
                                    if (str_contains($log->action, 'Moved'))   $badge = "bg-info text-dark";
                                ?>

                                <tr>
                                    <!-- USER -->
                                    <td class="fw-semibold">
                                        <i class="bx bx-user-circle me-2 fs-5"></i>
                                        <?= Html::encode($log->user->username ?? 'System') ?>
                                    </td>

                                    <!-- ACTION -->
                                    <td>
                                        <span class="badge px-3 py-2 <?= $badge ?>">
                                            <?= Html::encode($log->action) ?>
                                        </span>
                                    </td>

                                    <!-- DETAILS -->
                                    <td style="max-width: 420px; white-space: normal;">
                                        <?= Html::encode($log->details) ?>
                                    </td>

                                    <!-- TEAM -->
                                    <td>
                                        <?= Html::encode($log->team->name ?? '-') ?>
                                    </td>

                                    <!-- BOARD -->
                                    <td>
                                        <?= Html::encode($log->board->title ?? '-') ?>
                                    </td>

                                    <!-- TIME -->
                                    <td class="text-muted">
                                        <?= Yii::$app->formatter->asDatetime(
                                            $log->created_at,
                                            'php:d M Y h:i A'
                                        ) ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>

                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

</div>
