<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;

class UserSettings extends Model
{
    /**
     * Appearance
     */
    public $theme = 'light'; // light | dark

    /**
     * Validation rules
     */
    public function rules()
    {
        return [
            ['theme', 'in', 'range' => ['light', 'dark']],
        ];
    }

    /**
     * Load current user theme
     */
    public function loadFromUser()
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new UnauthorizedHttpException('User not logged in');
        }

        // Agar user table me theme column hai
        $this->theme = $user->theme ?? 'light';
    }

    /**
     * Save theme
     */
    // public function save()
    // {
    //     if (!$this->validate()) {
    //         return false;
    //     }

    //     $user = Yii::$app->user->identity;

    //     if (!$user) {
    //         throw new UnauthorizedHttpException('User not logged in');
    //     }

    //     // Agar user table me theme column hai
    //     $user->theme = $this->theme;

    //     return $user->save(false);
    // }
}
