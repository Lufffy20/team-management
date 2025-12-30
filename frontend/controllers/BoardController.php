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
 * 👉 Responsible for:
 * - Showing boards
 * - Creating boards
 * - Viewing board details
 * - Updating board info
 * - Deleting board and related data
 * - Managing board members
 */
class BoardController extends Controller
{
    /**
     * Access Control
     * ----------------
     * Only logged-in users can access any board action
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // 🔐 authenticated users only
                    ],
                ],
            ],
        ];
    }

    /**
     * BOARD LISTING
     * ----------------
     * Shows all boards available to logged-in user
     *
     * Logic:
     * 1️⃣ Fetch team IDs where user is a member
     * 2️⃣ Fetch boards created by user
     * 3️⃣ Fetch boards belonging to user's teams
     */
    public function actionIndex()
    {
        $userId = Yii::$app->user->id; // current logged-in user ID

        // 🔹 Fetch team IDs where user is a member
        $teamIds = \common\models\TeamMembers::find()
            ->select('team_id')
            ->where(['user_id' => $userId])
            ->column();

        // 🔹 Fetch boards (own + team boards)
        $boards = Board::find()
            ->where(['created_by' => $userId]) // boards created by user
            ->orWhere(['team_id' => $teamIds]) // boards from teams
            ->all();

        return $this->render('index', compact('boards'));
    }

    /**
     * CREATE BOARD
     * ----------------
     * Creates a new board and auto-generates default Kanban columns
     */
    public function actionCreate()
    {
        $model = new Board();
        $model->created_by = Yii::$app->user->id; // board owner
        $model->created_at = time();              // creation timestamp

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            /**
             * 🔹 Default Kanban Columns
             * Format: [status_key, label, position]
             */
            $defaultColumns = [
                ['todo',        'To-Do',        0],
                ['in_progress', 'In Progress',  1],
                ['done',        'Done',         2],
                ['archived',    'Archived',     3],
            ];

            // Create each default column
            foreach ($defaultColumns as $col) {
                $column = new KanbanColumn();
                $column->board_id = $model->id;          // link to board
                $column->user_id  = Yii::$app->user->id; // owner
                $column->status   = $col[0];             // column key
                $column->label    = $col[1];             // UI label
                $column->position = $col[2];             // order
                $column->save(false);                     // skip validation
            }

            return $this->redirect(['index']);
        }

        return $this->render('create', compact('model'));
    }

    /**
     * DELETE BOARD
     * ----------------
     * Deletes board along with all dependent data:
     * - Tasks
     * - Subtasks
     * - Comments
     * - Attachments
     * - Kanban Columns
     */
    public function actionDelete($id)
    {
        $board = Board::findOne($id);
        if (!$board) {
            throw new NotFoundHttpException('Board not found');
        }

        // 🔹 Fetch all tasks of the board
        $tasks = Task::find()
            ->where(['board_id' => $id])
            ->all();

        foreach ($tasks as $task) {

            // Delete subtasks
            Subtask::deleteAll(['task_id' => $task->id]);

            // Delete task comments
            TaskComment::deleteAll(['task_id' => $task->id]);

            // Delete task attachments (DB records only)
            TaskAttachment::deleteAll(['task_id' => $task->id]);

            // Delete task
            $task->delete();
        }

        // 🔹 Delete Kanban columns
        KanbanColumn::deleteAll(['board_id' => $id]);

        // 🔹 Finally delete board
        $board->delete();

        return $this->redirect(['index']);
    }

    /**
     * VIEW BOARD
     * ----------------
     * Displays board details along with members
     */
    public function actionView($id)
    {
        $board = Board::findOne($id);

        if (!$board) {
            throw new NotFoundHttpException('Board not found');
        }

        // 🔹 Fetch board members with user relation
        $members = \common\models\BoardMembers::find()
            ->where(['board_id' => $id])
            ->joinWith('user')
            ->all();

        /**
         * 🔹 Ensure board creator (manager) is always shown
         * even if not explicitly added in board_members table
         */
        $managerExists = false;
        foreach ($members as $m) {
            if ($m->user_id == $board->created_by) {
                $managerExists = true;
                break;
            }
        }

        // If manager missing → add manually on top
        if (!$managerExists) {
            $manager = new \common\models\BoardMembers();
            $manager->user_id  = $board->created_by;
            $manager->board_id = $board->id;

            // Attach user relation manually
            $manager->populateRelation(
                'user',
                \common\models\User::findOne($board->created_by)
            );

            array_unshift($members, $manager); // manager at top
        }

        return $this->render('view', [
            'board'   => $board,
            'members' => $members,
        ]);
    }

    /**
     * UPDATE BOARD
     * ----------------
     * Updates board title & description
     * Used for inline edit or form submit
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
     * REMOVE BOARD MEMBER
     * ----------------
     * Only board owner (creator) can remove members
     *
     * @param int $board_id
     * @param int $user
     */
    public function actionRemoveMember($board_id, $user)
    {
        $board = Board::findOne($board_id);

        if (!$board) {
            throw new NotFoundHttpException('Board not found');
        }

        // 🔐 Only board owner allowed
        if ($board->created_by != Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException(
                'You are not allowed to remove members from this board.'
            );
        }

        $member = \common\models\BoardMembers::findOne([
            'board_id' => $board_id,
            'user_id'  => $user,
        ]);

        if ($member) {
            $member->delete();
            Yii::$app->session->setFlash('success', 'Member removed successfully');
        } else {
            Yii::$app->session->setFlash('danger', 'Member not found');
        }

        return $this->redirect(['/board/view', 'id' => $board_id]);
    }
}
