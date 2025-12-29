<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\User $user */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl([
    'site/verify-email',
    'token' => $user->verification_token
]);
?>
<p>Hello <?= Html::encode($user->username) ?>,</p>

<p>Click below link to verify your email:</p>

<p><a href="<?= $verifyLink ?>">Verify Email</a></p>

<p>Thank you!</p>
