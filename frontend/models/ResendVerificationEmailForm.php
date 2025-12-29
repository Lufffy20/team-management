<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * ResendVerificationEmailForm model
 *
 * This model is responsible for resending the email verification link
 * to users who have registered but have not yet verified their email.
 */
class ResendVerificationEmailForm extends Model
{
    /**
     * User email for resending verification link.
     *
     * @var string
     */
    public $email;

    /**
     * Validation rules for resend verification form.
     */
    public function rules()
    {
        return [
            // Trim extra spaces from email
            ['email', 'trim'],

            // Email is required
            ['email', 'required'],

            // Email must be a valid format
            ['email', 'email'],

            // Email must exist for inactive users only
            [
                'email',
                'exist',
                'targetClass' => User::class,
                'filter' => ['status' => User::STATUS_INACTIVE],
                'message' => 'There is no user with this email address.'
            ],
        ];
    }

    /**
     * Sends verification email again to inactive user.
     *
     * @return bool Whether the email was sent successfully
     */
    public function sendEmail()
    {
        // Find inactive user by email
        $user = User::findOne([
            'email'  => $this->email,
            'status' => User::STATUS_INACTIVE,
        ]);

        // If user not found, stop process
        if ($user === null) {
            return false;
        }

        // Send verification email
        return Yii::$app
            ->mailer
            ->compose(
                [
                    'html' => 'emailVerify-html',
                    'text' => 'emailVerify-text',
                ],
                [
                    'user' => $user, // Pass user data to email templates
                ]
            )
            ->setFrom([
                Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'
            ])
            ->setTo($this->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }
}
