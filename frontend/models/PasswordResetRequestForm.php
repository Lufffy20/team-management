<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * PasswordResetRequestForm model
 *
 * This model handles the password reset request process.
 * It validates the user email and sends a password reset email
 * if the user exists and is active.
 */
class PasswordResetRequestForm extends Model
{
    /**
     * User email for password reset.
     */
    public $email;

    /**
     * Validation rules for password reset request.
     */
    public function rules()
    {
        return [
            // Trim extra spaces from email
            ['email', 'trim'],

            // Email is required
            ['email', 'required'],

            // Email must be in valid format
            ['email', 'email'],

            // Email must exist in users table (only active users)
            [
                'email',
                'exist',
                'targetClass' => User::class,
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'There is no user with this email address.'
            ],
        ];
    }

    /**
     * Sends the password reset email to the user.
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

        // If user not found, stop process
        if (!$user) {
            return false;
        }

        // Generate new token if existing one is invalid or expired
        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();

            // If token could not be saved, stop process
            if (!$user->save(false)) {
                return false;
            }
        }

        // Send password reset email
        return Yii::$app->mailer
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
                Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'
            ])
            ->setTo($this->email)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();
    }
}
