<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Notification model
 *
 * Represents the `notification` table.
 */
class Notification extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%notification}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules()
    {
        return [
            // Required fields
            [['user_id', 'title', 'message'], 'required'],

            // Integer fields
            [['user_id', 'is_read', 'created_at'], 'integer'],

            // Default values
            [['is_read'], 'default', 'value' => 0],

            // Message can be long text
            [['message'], 'string'],

            // Title length limit
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
