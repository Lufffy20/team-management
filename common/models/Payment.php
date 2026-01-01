<?php

namespace common\models;

use yii\db\ActiveRecord;

class Payment extends ActiveRecord
{
    public static function tableName()
    {
        return 'payments';
    }

    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['stripe_session_id', 'stripe_payment_intent', 'currency', 'status'], 'string'],
            [['amount'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }
}
