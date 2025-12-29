<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Notification model
 *
 * This model represents the `notification` table.
 * It is used to store user notifications.
 */
class Notification extends ActiveRecord
{
    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return '{{%notification}}';
    }

    /**
     * Attaches behaviors to the model.
     * Automatically fills created_at on insert.
     */
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

    /**
     * Validation rules for Notification model.
     */
    public function rules()
    {
        return [
            // Required fields
            [['user_id', 'title', 'message'], 'required'],

            // Integer fields
            [['user_id', 'is_read'], 'integer'],

            // Message can be long text
            [['message'], 'string'],

            // Title length limit
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * Relation with User model.
     * One notification belongs to one user.
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
