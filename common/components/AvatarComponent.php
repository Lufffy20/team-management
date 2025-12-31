<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\helpers\Url;

class AvatarComponent extends Component
{
    /**
     * Get avatar URL for any user model
     */
    public function get($user): string
    {
        //Uploaded avatar
        if (!empty($user->avatar)) {

            $baseUrl = Yii::$app->params['avatarBaseUrls'][$user->role]
                ?? Yii::$app->params['frontendUrl'];

            return $baseUrl
                . '/uploads/avatars/' . rawurlencode($user->avatar);
        }

        //DiceBear fallback
        $seed = $user->id
            ?? $user->username
            ?? trim(($user->first_name ?? '') . '-' . ($user->last_name ?? ''));

        return 'https://api.dicebear.com/9.x/avataaars/svg?seed='
            . urlencode($seed);
    }
}
