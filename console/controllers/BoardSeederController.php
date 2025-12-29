<?php

namespace console\controllers;

use yii\console\Controller;
use Faker\Factory;
use common\models\Board;
use common\models\User;
use common\models\Team;

class BoardSeederController extends Controller
{
    public function actionIndex($count = 5)
    {
        $faker = Factory::create('en_IN');

        /* ===============================
           🔑 REAL USERS
           =============================== */
        $userIds = User::find()->select('id')->column();
        if (empty($userIds)) {
            echo "❌ No users found. Create users first.\n";
            return;
        }

        /* ===============================
           🔑 REAL TEAMS
           =============================== */
        $teamIds = Team::find()->select('id')->column();
        if (empty($teamIds)) {
            echo "❌ No teams found. Create teams first.\n";
            return;
        }

        for ($i = 1; $i <= $count; $i++) {

            $board = new Board();

            // 🔥 Console safety (agar Board me BlameableBehavior ho)
            if ($board->hasMethod('detachBehavior')) {
                $board->detachBehavior('blameable');
            }

            // 🎲 Pick real relations
            $creatorId = $faker->randomElement($userIds);
            $teamId    = $faker->randomElement($teamIds);

            // 📝 CONTENT
            $board->title       = ucfirst($faker->words(3, true));
            $board->description = $faker->sentence(8);

            // 🔑 RELATIONS
            $board->created_by = $creatorId;
            $board->team_id    = $teamId;

            // 📅 TIMESTAMP
            $board->created_at = time();

            if (!$board->save(false)) {
                echo "❌ Failed to save board {$i}\n";
            }
        }

        echo "✅ {$count} Fake boards created successfully\n";
    }
}
