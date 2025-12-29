<?php

namespace frontend\controllers;

use Yii;
use common\models\Board;
use common\models\KanbanColumn;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use common\models\Task;
use common\models\Subtask;
use common\models\TaskComment;
use common\models\TaskAttachment;

class BoardController extends Controller
{

    
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ],
    ];
}

public function actionIndex()
{
    $userId = Yii::$app->user->id;

    // User jitni teams me member hai
    $teamIds = \common\models\TeamMembers::find()
        ->select('team_id')
        ->where(['user_id' => $userId])
        ->column();

    // Un teams ke sare boards + jo user ne banaye
    $boards = Board::find()
        ->where(['created_by' => $userId])     // board thats is create by user
        ->orWhere(['team_id' => $teamIds])     // team boards
        ->all();

    return $this->render('index', compact('boards'));
}



    public function actionCreate()
    {
        $model = new Board();
        $model->created_by = Yii::$app->user->id;
        $model->created_at = time();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            //Create default columns for this board
            $defaultColumns = [
                ['todo', 'To-Do', 0],
                ['in_progress', 'In Progress', 1],
                ['done', 'Done', 2],
                ['archived', 'Archived', 3],
            ];

            foreach ($defaultColumns as $col) {
    $column = new KanbanColumn();
    $column->board_id = $model->id;        
    $column->user_id  = Yii::$app->user->id;
    $column->status   = $col[0];
    $column->label    = $col[1];
    $column->position = $col[2];
    $column->save(false);
}


            return $this->redirect(['index']);
        }

        return $this->render('create', compact('model'));
    }

    public function actionDelete($id)
{
    $board = Board::findOne($id);
    if (!$board) {
        throw new NotFoundHttpException('Board not found');
    }

    // Find all tasks of this board
    $tasks = Task::find()->where(['board_id' => $id])->all();

    foreach ($tasks as $task) {

        // Delete subtasks
        Subtask::deleteAll(['task_id' => $task->id]);

        // Delete task comments
        TaskComment::deleteAll(['task_id' => $task->id]);

        // Delete attachments (DB)
        TaskAttachment::deleteAll(['task_id' => $task->id]);

        // Delete task itself
        $task->delete();
    }

    // Delete columns
    KanbanColumn::deleteAll(['board_id' => $id]);

    // Delete board
    $board->delete();

    return $this->redirect(['index']);
}


    public function actionView($id) {
    $board = Board::findOne($id);

    // board ke members fetch
    $members = \common\models\BoardMembers::find()
                ->where(['board_id' => $id])
                ->joinWith('user') 
                ->all();

    return $this->render('view', [
        'board' => $board,
        'members' => $members
    ]);
}


public function actionUpdate() {
    $id = Yii::$app->request->post('id');
    $board = Board::findOne($id);

    $board->title = Yii::$app->request->post('title');
    $board->description = Yii::$app->request->post('description');
    $board->save();

    return $this->redirect(['/board/index', 'board_id' => $board->id]);
}

public function actionRemoveMember($board_id, $user)
{
    $member = \common\models\BoardMembers::findOne([
        'board_id' => $board_id,
        'user_id' => $user
    ]);

    if ($member) {
        $member->delete();
        Yii::$app->session->setFlash('success', 'Member removed successfully');
    } else {
        Yii::$app->session->setFlash('danger', 'Member not found');
    }

    return $this->redirect(['/board/view', 'id'=>$board_id]);
}


}
