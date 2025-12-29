<?php

namespace console\controllers;

use yii\console\Controller;
use Faker\Factory as Faker;
use common\models\User;

class FakeUserController extends Controller
{
    public function actionGenerate($count = 10)
    {
        $faker = Faker::create();

        for ($i = 1; $i <= $count; $i++) {

            $user = new User();
            $user->first_name = $faker->firstName;
            $user->last_name = $faker->lastName;
            $user->username = $faker->userName . rand(100,999);
            $user->email = $faker->unique()->safeEmail;
            $user->setPassword('Test@123');
            $user->generateAuthKey();
            $user->status = 10; // active user

            if ($user->save()) {
                echo "Added: {$user->email} \n";
            } else {
                print_r($user->errors);
            }
        }

        echo "\nDONE — $count Fake Users Inserted Successfully! 🚀\n";
    }
}
