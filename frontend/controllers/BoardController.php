<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use common\models\Board;
use common\models\KanbanColumn;
use common\models\Task;
use common\models\Subtask;
use common\models\TaskComment;
use common\models\TaskAttachment;

/**
 * BoardController
 *
 * Handles board listing, creation, viewing,
 * updating, deletion, and member management.
 */
class BoardController extends Controller
{
    /**
     * Access control.
     * Only logged-in users can access board actions.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // authenticated users only
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all boards visible to the logged-in user.
     *
     * Logic:
     * 1) Get teams where user is a member
     * 2) Fetch boards created by the user
     * 3) Fetch boards belonging to those teams
     */
    public function actionIndex()
    {
        $userId = Yii::$app->user->id; // current user ID

        /* ================= USER TEAMS ================= */
        $teamIds = \common\models\TeamMembers::find()
            ->select('team_id')
            ->where(['user_id' => $userId])
            ->column();

        /* ================= USER BOARDS ================= */
        $boards = Board::find()
            ->where(['created_by' => $userId]) // boards created by user
            ->orWhere(['team_id' => $teamIds]) // boards from user's teams
            ->all();

        return $this->render('index', compact('boards'));
    }

    /**
     * Creates a new board.
     * Also creates default Kanban columns for the board.
     */
    public function actionCreate()
    {
        $model = new Board();
        $model->created_by = Yii::$app->user->id; // creator
        $model->created_at = time();              // timestamp

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            /* ================= DEFAULT KANBAN COLUMNS ================= */
            $defaultColumns = [
                ['todo',        'To-Do',        0],
                ['in_progress', 'In Progress',  1],
                ['done',        'Done',         2],
                ['archived',    'Archived',     3],
            ];

            foreach ($defaultColumns as $col) {
                $column = new KanbanColumn();
                $column->board_id = $model->id;              // board reference
                $column->user_id  = Yii::$app->user->id;     // owner
                $column->status   = $col[0];                 // status key
                $column->label    = $col[1];                 // display label
                $column->position = $col[2];                 // column order
                $column->save(false);
            }

            return $this->redirect(['index']);
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Deletes a board and all related data.
     *
     * Deletes:
     * - Tasks
     * - Subtasks
     * - Task comments
     * - Task attachments
     * - Kanban columns
     */
    public function actionDelete($id)
    {
        $board = Board::findOne($id);
        if (!$board) {
            throw new NotFoundHttpException('Board not found');
        }

        /* ================= BOARD TASKS ================= */
        $tasks = Task::find()
            ->where(['board_id' => $id])
            ->all();

        foreach ($tasks as $task) {

            // Delete subtasks
            Subtask::deleteAll(['task_id' => $task->id]);

            // Delete comments
            TaskComment::deleteAll(['task_id' => $task->id]);

            // Delete attachments (database records)
            TaskAttachment::deleteAll(['task_id' => $task->id]);

            // Delete task itself
            $task->delete();
        }

        /* ================= BOARD COLUMNS ================= */
        KanbanColumn::deleteAll(['board_id' => $id]);

        /* ================= BOARD ================= */
        $board->delete();

        return $this->redirect(['index']);
    }

    /**
     * Displays a single board with its members.
     *
     * @param int $id Board ID
     */
    public function actionView($id)
    {
        $board = Board::findOne($id);

        // Fetch board members with user data
        $members = \common\models\BoardMembers::find()
            ->where(['board_id' => $id])
            ->joinWith('user')
            ->all();

        return $this->render('view', [
            'board'   => $board,
            'members' => $members,
        ]);
    }

    /**
     * Updates board title and description.
     * Used for inline or form-based updates.
     */
    public function actionUpdate()
    {
        $id = Yii::$app->request->post('id');
        $board = Board::findOne($id);

        $board->title       = Yii::$app->request->post('title');
        $board->description = Yii::$app->request->post('description');
        $board->save();

        return $this->redirect([
            '/board/index',
            'board_id' => $board->id,
        ]);
    }

    /**
     * Removes a member from a board.
     *
     * @param int $board_id Board ID
     * @param int $user     User ID
     */
    public function actionRemoveMember($board_id, $user)
    {
        $member = \common\models\BoardMembers::findOne([
            'board_id' => $board_id,
            'user_id'  => $user,
        ]);

        if ($member) {
            $member->delete();
            Yii::$app->session->setFlash(
                'success',
                'Member removed successfully'
            );
        } else {
            Yii::$app->session->setFlash(
                'danger',
                'Member not found'
            );
        }

        return $this->redirect([
            '/board/view',
            'id' => $board_id,
        ]);
    }
}
