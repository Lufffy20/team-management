<?php

namespace backend\models;

use yii\base\Model;
use yii\base\InvalidArgumentException;
use common\models\User;

/**
 * ResetPasswordForm model (Backend)
 *
 * This model handles resetting the user's password
 * using a valid password reset token.
 */
class ResetPasswordForm extends Model
{
    /**
     * New password entered by the user.
     *
     * @var string
     */
    public $password;

    /**
     * User resolved from password reset token.
     *
     * @var User|null
     */
    private $_user;

    /**
     * Constructor.
     * Validates the reset token and loads the related user.
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

        // Invalid or expired token
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

            // Minimum password length
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Resets the user's password.
     *
     * Steps:
     * - Hash and save new password
     * - Remove password reset token
     *
     * @return bool Whether password reset was successful
     */
    public function resetPassword()
    {
        $user = $this->_user;

        // Set new password (hashed)
        $user->setPassword($this->password);

        // Invalidate reset token
        $user->removePasswordResetToken();

        // Save user without validation
        return $user->save(false);
    }
}
