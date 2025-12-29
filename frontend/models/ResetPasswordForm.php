<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidArgumentException;
use common\models\User;

/**
 * ResetPasswordForm model
 *
 * This model handles the final step of password reset.
 * It validates the reset token and allows the user
 * to set a new password.
 */
class ResetPasswordForm extends Model
{
    /**
     * New password entered by user.
     */
    public $password;

    /**
     * User instance resolved from reset token.
     *
     * @var User
     */
    private $_user;

    /**
     * Constructor.
     * Validates password reset token and loads related user.
     *
     * @param string $token Password reset token
     * @param array  $config Model configuration
     *
     * @throws InvalidArgumentException If token is empty or invalid
     */
    public function __construct($token, $config = [])
    {
        // Token must be a non-empty string
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }

        // Find user by valid password reset token
        $this->_user = User::findByPasswordResetToken($token);

        // If token is invalid or expired
        if (!$this->_user) {
            throw new InvalidArgumentException('Wrong password reset token.');
        }

        parent::__construct($config);
    }

    /**
     * Validation rules for new password.
     */
    public function rules()
    {
        return [
            // Password is required
            ['password', 'required'],

            // Minimum password length from params
            [
                'password',
                'string',
                'min' => Yii::$app->params['user.passwordMinLength']
            ],
        ];
    }

    /**
     * Resets the user's password.
     *
     * Steps:
     * - Hash and save new password
     * - Remove used reset token
     * - Generate new auth key (logout from all sessions)
     *
     * @return bool Whether password reset was successful
     */
    public function resetPassword()
    {
        $user = $this->_user;

        // Set new password (hashed)
        $user->setPassword($this->password);

        // Invalidate password reset token
        $user->removePasswordResetToken();

        // Regenerate auth key for security
        $user->generateAuthKey();

        // Save user without validation
        return $user->save(false);
    }
}
