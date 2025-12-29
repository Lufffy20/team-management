<?php
use yii\helpers\Html;

/** @var $user common\models\User */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl([
    'site/verify-email',   // MUST be exactly this
    'token' => $user->verification_token
]);

?>

<p>Hello <?= Html::encode($user->username) ?>,</p>

<p>Please click the link below to verify your new email address:</p>

<p><a href="<?= $verifyLink ?>">Verify New Email</a></p>

<p>If you did not request this change, please ignore this email.</p>
