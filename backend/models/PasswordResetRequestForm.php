<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * PasswordResetRequestForm model (Backend)
 *
 * This model handles password reset requests from the backend panel.
 * It validates the email address and sends a password reset email
 * to active users.
 */
class PasswordResetRequestForm extends Model
{
    /**
     * Email address of the user requesting password reset.
     *
     * @var string
     */
    public $email;

    /**
     * Validation rules for password reset request.
     */
    public function rules()
    {
        return [
            // Trim extra spaces
            ['email', 'trim'],

            // Email is required
            ['email', 'required'],

            // Must be a valid email format
            ['email', 'email'],

            // Email must exist for active users only
            [
                'email',
                'exist',
                'targetClass' => User::class,
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'There is no user with this email.'
            ],
        ];
    }

    /**
     * Sends password reset email to the user.
     *
     * @return bool Whether the email was sent successfully
     */
    public function sendEmail()
    {
        // Find active user by email
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email'  => $this->email,
        ]);

        // Stop if user not found
        if (!$user) {
            return false;
        }

        // Generate new reset token if current one is invalid or expired
        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
        }

        // Save user with new token
        if (!$user->save(false)) {
            return false;
        }

        // Send password reset email
        return Yii::$app
            ->mailer
            ->compose(
                [
                    'html' => 'passwordResetToken-html',
                    'text' => 'passwordResetToken-text'
                ],
                [
                    'user' => $user // Pass user data to email templates
                ]
            )
            ->setFrom([
                Yii::$app->params['supportEmail'] => Yii::$app->name
            ])
            ->setTo($this->email)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();
    }
}
