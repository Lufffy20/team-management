<?php

namespace console\controllers;

use yii\console\Controller;
use Faker\Factory;
use common\models\Team;
use common\models\User;

class TeamSeederController extends Controller
{
    public function actionIndex($count = 5)
    {
        $faker = Factory::create('en_IN');

        $userIds = User::find()->select('id')->column();
        if (empty($userIds)) {
            echo "❌ No users found\n";
            return;
        }

        for ($i = 1; $i <= $count; $i++) {

            $ownerId = $faker->randomElement($userIds);

            $team = new Team();

            // 🔥 SAME FIX AS TASK
            $team->detachBehavior('blameable');

            $team->name = ucfirst($faker->unique()->words(2, true)) . ' Team';
            $team->created_by = $ownerId;
            $team->created_at = time();

            if (!$team->save(false)) {
                echo "❌ Failed to save team {$i}\n";
            }
        }

        echo "✅ {$count} Teams created successfully\n";
    }
}
