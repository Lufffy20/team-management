<?php

namespace common\components;

use Yii;
use yii\base\Component;

class AvatarComponent extends Component
{
    /**
     * Get avatar URL for any user model
     */
    public function get($user): string
    {
        if (!empty($user->avatar)) {
            return Yii::$app->request->hostInfo
                . '/uploads/avatars/'
                . rawurlencode($user->avatar);
        }

        $seed = $user->id ?? $user->username ?? 'user';

        return 'https://api.dicebear.com/9.x/avataaars/svg?seed='
            . urlencode($seed);
    }
}
