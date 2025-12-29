<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm model
 *
 * This model handles user login using username and password.
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    /**
     * Cached user instance.
     */
    private $_user;

    /**
     * Validation rules for login form.
     */
    public function rules()
    {
        return [
            // Username and password are required
            [['username', 'password'], 'required'],

            // RememberMe must be boolean
            ['rememberMe', 'boolean'],

            // Password validation using custom method
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This is an inline validator for password field.
     *
     * @param string $attribute attribute currently being validated
     * @param array  $params additional parameters
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            // Invalid username or password
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in the user if validation passes.
     *
     * @return bool whether login was successful
     */
    public function login()
    {
        // Validate form data
        if (!$this->validate()) {
            return false;
        }

        // Block login if user email is not verified
        if ($this->getUser()->status == User::STATUS_INACTIVE) {
            $this->addError('username', 'Please verify your email before login.');
            return false;
        }

        // Login user with or without remember me
        return Yii::$app->user->login(
            $this->getUser(),
            $this->rememberMe ? 3600 * 24 * 30 : 0
        );
    }

    /**
     * Finds user by username.
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
