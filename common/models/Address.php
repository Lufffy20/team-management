<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;


class Address extends ActiveRecord
{
    const TYPE_HOME     = 'home';
    const TYPE_BILLING  = 'billing';
    const TYPE_SHIPPING = 'shipping';

    public static function tableName()
    {
        return '{{%address}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['user_id', 'address_type'], 'required'],
            [['address', 'city', 'state', 'pincode'], 'required'],

            [['user_id'], 'integer'],
            [['address'], 'string'],
            [['city', 'state'], 'string', 'max' => 100],
            [['pincode'], 'string', 'min' => 4, 'max' => 10],

            ['address_type', 'in', 'range' => [
                self::TYPE_HOME,
                self::TYPE_BILLING,
                self::TYPE_SHIPPING,
            ]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'address_type' => 'Address Type',
            'address'      => 'Address',
            'city'         => 'City',
            'state'        => 'State',
            'pincode'      => 'Pincode',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getHomeAddress($userId)
    {
        return self::findOne(['user_id' => $userId, 'address_type' => self::TYPE_HOME]);
    }

    public static function getBillingAddress($userId)
    {
        return self::findOne(['user_id' => $userId, 'address_type' => self::TYPE_BILLING]);
    }

    public static function getShippingAddress($userId)
    {
        return self::findOne(['user_id' => $userId, 'address_type' => self::TYPE_SHIPPING]);
    }
}
