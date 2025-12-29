<?php

namespace console\controllers;

use yii\console\Controller;
use common\models\KanbanColumn;
use common\models\Board;

class KanbanColumnSeederController extends Controller
{
    public function actionIndex()
    {
        $boards = Board::find()
            ->select(['id', 'created_by'])
            ->asArray()
            ->all();

        if (empty($boards)) {
            echo "❌ No boards found\n";
            return;
        }

        $defaultColumns = [
            ['status' => 'todo',        'label' => 'To Do'],
            ['status' => 'in_progress', 'label' => 'In Progress'],
            ['status' => 'done',        'label' => 'Done'],
            ['status' => 'archived',    'label' => 'Archived'], // 🔥 NEW
        ];

        foreach ($boards as $board) {

            $position = 1;

            foreach ($defaultColumns as $col) {

                // ⛔ Skip if already exists
                $exists = KanbanColumn::find()
                    ->where([
                        'board_id' => $board['id'],
                        'status'   => $col['status']
                    ])
                    ->exists();

                if ($exists) {
                    $position++;
                    continue;
                }

                $column = new KanbanColumn();
                $column->board_id = $board['id'];
                $column->user_id  = $board['created_by'];
                $column->status   = $col['status'];
                $column->label    = $col['label'];
                $column->position = $position++;

                $column->save(false);
            }
        }

        echo "✅ Kanban columns (including ARCHIVE) ready\n";
    }
}
