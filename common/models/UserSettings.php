<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;

/**
 * UserSettings model
 *
 * This model is used to manage user-specific settings
 * like UI appearance (theme).
 */
class UserSettings extends Model
{
    /**
     * Appearance setting
     * Possible values: light | dark
     */
    public $theme = 'light';

    /**
     * Validation rules
     */
    public function rules()
    {
        return [
            // Theme must be either light or dark
            ['theme', 'in', 'range' => ['light', 'dark']],
        ];
    }

    /**
     * Loads current user's settings.
     * Fetches theme value from logged-in user.
     */
    public function loadFromUser()
    {
        $user = Yii::$app->user->identity;

        // User must be logged in
        if (!$user) {
            throw new UnauthorizedHttpException('User not logged in');
        }

        // If theme column exists in user table
        $this->theme = $user->theme ?? 'light';
    }

    /**
     * Save theme to user table
     * (Currently commented as optional implementation)
     */
    // public function save()
    // {
    //     if (!$this->validate()) {
    //         return false;
    //     }
    //
    //     $user = Yii::$app->user->identity;
    //
    //     if (!$user) {
    //         throw new UnauthorizedHttpException('User not logged in');
    //     }
    //
    //     // Save theme in user table
    //     $user->theme = $this->theme;
    //
    //     return $user->save(false);
    // }
}
