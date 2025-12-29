<?php

namespace console\controllers;

use yii\console\Controller;
use common\models\Board;
use common\models\TeamMembers;
use common\models\BoardMembers;

class BoardMemberSeederController extends Controller
{
    public function actionIndex()
    {
        $boards = Board::find()->all();

        if (empty($boards)) {
            echo "❌ No boards found\n";
            return;
        }

        foreach ($boards as $board) {

            echo "\n➡️ Board {$board->id} (team {$board->team_id})\n";

            if (!$board->team_id) {
                echo "⚠️ Board has no team_id, skipping\n";
                continue;
            }

            $teamMembers = TeamMembers::find()
                ->where(['team_id' => $board->team_id])
                ->all();

            if (empty($teamMembers)) {
                echo "⚠️ No team members for team {$board->team_id}\n";
                continue;
            }

            foreach ($teamMembers as $tm) {

                $exists = BoardMembers::find()
                    ->where([
                        'board_id' => $board->id,
                        'user_id'  => $tm->user_id
                    ])
                    ->exists();

                if ($exists) {
                    echo "⏭️ user {$tm->user_id} already added\n";
                    continue;
                }

                $bm = new BoardMembers();
                $bm->board_id   = $board->id;
                $bm->user_id    = $tm->user_id;
                $bm->created_at = time();

                if ($bm->save(false)) {
                    echo "✅ added user {$tm->user_id}\n";
                } else {
                    echo "❌ failed for user {$tm->user_id}\n";
                }
            }
        }

        echo "\n🎉 Board members seeding completed\n";
    }
}
