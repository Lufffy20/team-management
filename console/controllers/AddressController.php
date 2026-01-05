<?php

namespace console\controllers;

use yii\console\Controller;
use Faker\Factory;
use common\models\Address;

class AddressController extends Controller
{
    /**
     * Generate fake addresses
     * Usage: php yii address/fake 10
     */
    public function actionFake($count = 10)
    {
        $faker = \Faker\Factory::create('en_IN');

        $userId = 11;

        for ($i = 0; $i < $count; $i++) {

            $model = new \common\models\Address();
            $model->user_id = $userId;

            $model->address_type = $faker->randomElement([
                \common\models\Address::TYPE_HOME,
                \common\models\Address::TYPE_BILLING,
                \common\models\Address::TYPE_SHIPPING,
            ]);

            $model->address = $faker->streetAddress;
            $model->city = $faker->city;
            $model->state = $faker->state;
            $model->pincode = $faker->postcode;

            $model->save(false);
        }

        echo "✅ {$count} fake addresses created for user_id = {$userId}\n";
    }
}
