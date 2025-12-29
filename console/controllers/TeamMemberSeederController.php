<?php

namespace console\controllers;

use yii\console\Controller;
use common\models\Team;
use common\models\User;
use common\models\TeamMembers;

class TeamMemberSeederController extends Controller
{
    public function actionIndex()
    {
        $teams   = Team::find()->all();
        $userIds = User::find()->select('id')->column();

        if (empty($teams) || empty($userIds)) {
            echo "❌ Teams or Users missing\n";
            return;
        }

        foreach ($teams as $team) {

            // ⛔ skip if already seeded
            $exists = TeamMembers::find()
                ->where(['team_id' => $team->id])
                ->exists();

            if ($exists) continue;

            // 👑 Team owner as manager
            $owner = new TeamMembers();
            $owner->team_id = $team->id;
            $owner->user_id = $team->created_by;
            $owner->role    = 'manager';
            $owner->save(false);

            // 👥 Add 2–4 random members
            $members = array_diff($userIds, [$team->created_by]);
            shuffle($members);

            $limit = rand(2, 4);
            for ($i = 0; $i < $limit && isset($members[$i]); $i++) {

                $tm = new TeamMembers();
                $tm->team_id = $team->id;
                $tm->user_id = $members[$i];
                $tm->role    = 'member';
                $tm->save(false);
            }

            echo "✅ Team members added for Team #{$team->id}\n";
        }
    }
}
