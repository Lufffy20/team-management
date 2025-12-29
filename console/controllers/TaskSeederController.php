<?php

namespace console\controllers;

use yii\console\Controller;
use Faker\Factory;
use common\models\Task;
use common\models\User;
use common\models\Board;

class TaskSeederController extends Controller
{
    public function actionIndex($count = 50)
    {
        $faker = Factory::create('en_IN');

        /* ===============================
           🔑 REAL USERS
           =============================== */
        $userIds = User::find()->select('id')->column();
        if (empty($userIds)) {
            echo "❌ No users found\n";
            return;
        }

        /* ===============================
           🔑 REAL BOARDS
           =============================== */
        $boards = Board::find()
            ->select(['id', 'team_id', 'created_by'])
            ->asArray()
            ->all();

        if (empty($boards)) {
            echo "❌ No boards found. Create boards first.\n";
            return;
        }

        for ($i = 1; $i <= $count; $i++) {

            // 🎯 Pick random board
            $board = $faker->randomElement($boards);

            $task = new Task();

            // 🔥 Console fix
            $task->detachBehavior('blameable');

            // 🎲 Users
            $creatorId  = $faker->randomElement($userIds);
            $assigneeId = $faker->randomElement($userIds);

            // 📝 CONTENT
            $task->title       = $faker->sentence(4);
            $task->description = $faker->paragraph(3);

            // 📌 STATUS & PRIORITY
            $task->status = $faker->randomElement([
                Task::STATUS_TODO,
                Task::STATUS_IN_PROGRESS,
                Task::STATUS_DONE,
            ]);

            $task->priority = $faker->randomElement([
                Task::PRIORITY_LOW,
                Task::PRIORITY_MEDIUM,
                Task::PRIORITY_HIGH,
            ]);

            // 🔑 BOARD → TEAM SAFE MAPPING
            $task->board_id = $board['id'];
            $task->team_id  = $board['team_id'];

            // 🔑 USER RELATIONS
            $task->user_id    = $creatorId;
            $task->created_by = $creatorId;
            $task->updated_by = $creatorId;

            $task->assigned_to = $assigneeId;
            $task->assignee_id = $assigneeId;

            // 📅 DATES
            $task->due_date = $faker
                ->dateTimeBetween('+1 day', '+10 days')
                ->getTimestamp();

            $task->created_at = time();
            $task->updated_at = time();

            $task->last_reminder_at = $faker->boolean(40)
                ? $faker->dateTimeBetween('-1 day', 'now')->getTimestamp()
                : null;

            // 🧲 SORT ORDER (per board + status)
            $lastOrder = Task::find()
                ->where([
                    'board_id' => $task->board_id,
                    'status'   => $task->status
                ])
                ->max('sort_order');

            $task->sort_order = $lastOrder ? $lastOrder + 1 : 1;

            // 💾 SAVE
            if (!$task->save(false)) {
                echo "❌ Failed to save task {$i}\n";
            }
        }

        echo "✅ {$count} Board-wise fake tasks inserted successfully\n";
    }
}
