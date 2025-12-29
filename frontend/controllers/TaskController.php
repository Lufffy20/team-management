<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use common\models\Task;
use common\models\User;
use common\models\KanbanColumn;
use common\models\Subtask;
use common\models\Board;
use yii\web\NotFoundHttpException;
use common\components\Logger;
use yii\web\UploadedFile;
use common\models\TaskImage;
use common\models\TaskAttachment;
use common\models\ActivityLog;

class TaskController extends Controller
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


    public function beforeAction($action)
{
    if (Yii::$app->request->isAjax) {
        $this->enableCsrfValidation = false;
    }
    return parent::beforeAction($action);
}


    // ===============================
    // KANBAN BOARD
    // ===============================
public function actionKanban()
{

    if (Yii::$app->user->isGuest) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $session = Yii::$app->session;

    // If user selected board → save it
    if ($id = Yii::$app->request->get('board_id')) {
        $session->set('last_board', $id);
    }

    // Fetch all boards available to user
    $teamBoards = \common\models\TeamMembers::find()
        ->select('team_id')
        ->where(['user_id' => Yii::$app->user->id])
        ->column();

    $boards = Board::find()
        ->where(['created_by' => Yii::$app->user->id])
        ->orWhere(['team_id' => $teamBoards])
        ->all();

    //  If no boards exist → show create UI
    if (!$boards) {
        return $this->render('kanban'); // <-- एक नया empty page बनाओ simple
    }

    // Load board id from session OR default to first available
    $boardId = $session->get('last_board', $boards[0]->id); // FIXED HERE 🚀

    $board = Board::findOne($boardId);
    if (!$board) {
        $boardId = $boards[0]->id;  // FALLBACK
        $board = Board::findOne($boardId);
    }

    // Detect Role
    $role = "guest";
    if ($board->created_by == Yii::$app->user->id) {
        $role = "owner";
    } else {
        $tm = \common\models\TeamMembers::findOne([
            'team_id' => $board->team_id,
            'user_id' => Yii::$app->user->id
        ]);
        if ($tm) $role = $tm->role;
    }

    // Columns + Tasks
    $columns = KanbanColumn::find()->where(['board_id'=>$boardId])->orderBy(['position'=>SORT_ASC])->all();
    $tasks = Task::find()->where(['board_id'=>$boardId])->orderBy(['sort_order'=>SORT_ASC])->all();

    $grouped = [];
    foreach($tasks as $task) $grouped[$task->status][] = $task;

    return $this->render('kanban', [
    'boardId' => $boardId,   //  MUST BE EXACTLY THIS NAME
    'board'   => $board,
    'columns' => $columns,
    'tasks'   => $grouped,
    'role'    => $role
]);

}


    // ===============================
    // UPDATE STATUS (TASK DRAG)
    // ===============================
public function actionUpdateStatus()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $taskIds = Yii::$app->request->post('tasks');
    $boardId = Yii::$app->request->post('board_id');
    $status  = Yii::$app->request->post('status');
    $movedId = Yii::$app->request->post('moved_id');

    if (empty($taskIds) || !is_array($taskIds)) {
        return ['success' => false, 'message' => 'Tasks array missing'];
    }

    $board = \common\models\Board::findOne($boardId);
    if (!$board) {
        return ['success' => false, 'message' => 'Board not found'];
    }

    /* ===============================
        ACCESS CONTROL (FIXED)
       =============================== */

    //  Owner always allowed (solo OR team board)
    $isOwner = ($board->created_by == Yii::$app->user->id);

    //  Team member allowed (only if board has team)
    $isMember = false;
    if ($board->team_id) {
        $isMember = \common\models\TeamMembers::find()
            ->where([
                'team_id' => $board->team_id,
                'user_id' => Yii::$app->user->id
            ])
            ->exists();
    }

    //  Outsider blocked
    if (!$isOwner && !$isMember) {
        return ['success' => false, 'message' => 'Access denied'];
    }

    /* ===============================
    STATUS UPDATE (ONLY MOVED TASK)
       =============================== */
    if ($movedId && $status) {
        Task::updateAll(
            ['status' => $status],
            ['id' => $movedId, 'board_id' => $boardId]
        );
    }

    /* ===============================
        SORT ORDER UPDATE (DEST COLUMN)
       =============================== */
    foreach ($taskIds as $index => $taskId) {

        $task = Task::findOne([
            'id'       => $taskId,
            'board_id' => $boardId
        ]);

        if (!$task) {
            continue;
        }

        $task->sort_order = $index + 1;
        $task->save(false);
    }

    return ['success' => true];
}





   // ===============================
// CREATE NEW TASK (AJAX)
// ===============================
public function actionCreateAjax()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $model = new Task();
    $data  = Yii::$app->request->post();

    // BASIC DEFAULTS
    $model->created_by = Yii::$app->user->id;
    $model->board_id   = $data['Task']['board_id']
        ?? Yii::$app->session->get('last_board');

    $model->status = $data['Task']['status']
        ?? Task::STATUS_TODO;

    if ($model->load($data)) {

        // SORT ORDER (after load)
        $lastOrder = Task::find()
            ->where([
                'board_id' => $model->board_id,
                'status'   => $model->status
            ])
            ->max('sort_order');

        $model->sort_order = $lastOrder ? $lastOrder + 1 : 1;

        // ATTACHMENTS
        $model->attachmentFiles = UploadedFile::getInstances(
            $model,
            'attachmentFiles'
        );

        // VALIDATION
        if (!$model->validate()) {
            return [
                'success' => false,
                'errors'  => $model->getErrors()
            ];
        }

        // SAVE MODEL (validation already done)
        $model->save(false);

        // SAVE ATTACHMENTS
        if (!empty($model->attachmentFiles)) {
            foreach ($model->attachmentFiles as $file) {
                $fileName   = 'task_' . $model->id . '_' . uniqid() . '.' . $file->extension;
                $uploadPath = Yii::getAlias('@webroot/uploads/tasks/') . $fileName;

                if ($file->saveAs($uploadPath)) {
                    $att = new TaskAttachment();
                    $att->task_id = $model->id;
                    $att->file    = $fileName;
                    $att->save(false);
                }
            }
        }

        Logger::add(
            "Task Created",
            "Task #{$model->id}: {$model->title}",
            $model->board->team_id,
            $model->board_id
        );

        return [
            'success' => true,
            'status'  => $model->status,
            'html'    => $this->renderPartial('taskcard', [
                'model' => $model
            ]),
        ];
    }

    return ['success' => false, 'msg' => 'Load failed'];
}


    // ===============================
    // LOAD TASK DETAILS
    // ===============================
    public function actionViewAjax($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $task = Task::findOne($id);   // creator restriction removed

    if (!$task) {
        return ['success' => false, 'message' => 'Task not found'];
    }

    return [
        'success' => true,
        'html' => $this->renderPartial('taskdetails', ['model' => $task]),
    ];
}


    // ===============================
    // UPDATE TASK
    // ===============================
public function actionUpdate($id)
{
    $task = $this->findModel($id);

    // OLD ASSIGNEE (for comparison)
    $oldAssigneeId = $task->assignee_id;

    /* ===============================
    ACTIVITY FETCH (LATEST 5 ONLY)
    =============================== */
    $activities = ActivityLog::find()
        ->where([
            'team_id'  => $task->board->team_id,
            'board_id' => $task->board_id
        ])
        ->andWhere(['like', 'details', 'Task #' . $task->id])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(5)
        ->all();

    if ($task->load(Yii::$app->request->post())) {

        $task->imageFiles      = UploadedFile::getInstances($task, 'imageFiles');
        $task->attachmentFiles = UploadedFile::getInstances($task, 'attachmentFiles');

        if ($task->save(false)) {

            /* ===== IMAGE UPLOAD ===== */
            if (!empty($task->imageFiles)) {
                $task->uploadImages();
            }

            /* ===== ATTACHMENTS ===== */
            if (!empty($task->attachmentFiles)) {
                foreach ($task->attachmentFiles as $file) {

                    $name = 'task_' . $task->id . '_' . uniqid() . '.' . $file->extension;
                    $path = Yii::getAlias('@webroot/uploads/tasks/') . $name;

                    if ($file->saveAs($path)) {
                        $attach = new TaskAttachment();
                        $attach->task_id = $task->id;
                        $attach->file    = $name;
                        $attach->save(false);
                    }
                }
            }

            /* ===============================
            ASSIGNEE CHANGE → EMAIL
            =============================== */
            if ($oldAssigneeId != $task->assignee_id && $task->assignee_id) {

                // Activity for assignment
                Logger::add(
                    "Task Assigned",
                    "Task #{$task->id} assigned to {$task->assignee->username}",
                    $task->board->team_id,
                    $task->board_id
                );

                // Email notification
                $this->sendAssignmentMail($task);
            }

            /* ===== ACTIVITY LOG ===== */
            Logger::add(
                "Task Updated",
                "Task #{$task->id}: {$task->title}",
                $task->board->team_id,
                $task->board_id
            );

            Yii::$app->session->setFlash('success', 'Task updated successfully');
            return $this->redirect(['task/update', 'id' => $task->id]);
        }
    }

    return $this->render('update', [
        'model'      => $task,
        'activities' => $activities
    ]);
}



protected function sendAssignmentMail($task)
{
    $assignee = $task->assignee;
    $assigner = Yii::$app->user->identity; //assign

    if (!$assignee || !$assignee->email) {
        return;
    }

    Yii::$app->mailer->compose(
        'taskAssigned',
        [
            'task'     => $task,
            'assignee' => $assignee,
            'assigner' => $assigner, // 
        ]
    )
    ->setTo($assignee->email)
    ->setFrom([Yii::$app->params['adminEmail'] => 'Task Manager'])
    ->setSubject(
        '📝 Task Assigned by ' . $assigner->username
    )
    ->send();
}


protected function findModel($id)
{
    if (($model = Task::findOne($id)) !== null) {
        return $model;
    }

    throw new NotFoundHttpException('The requested task does not exist.');
}


    // ===============================
    // DELETE TASK
    // ===============================
    public function actionDelete($id)
{
    $model = $this->findModel($id);

    foreach ($model->images as $img) {

        if (!empty($img->image)) {
            $file = Yii::getAlias('@webroot/uploads/tasks/') . $img->image;

            if (is_file($file)) {
                @unlink($file); // safe delete
            }
        }

        $img->delete();
    }

    //logger
    Logger::add(
    "Task Deleted",
    "Task #{$id}: {$model->title}",
    $model->board->team_id,
    $model->board_id
);


    $boardId = $model->board_id;
    $model->delete();

    Yii::$app->session->setFlash('success', 'Task deleted successfully');

    return $this->redirect(['/task/kanban', 'board_id' => $boardId]);
}



    // ===============================
    // UPDATE COLUMN ORDER (COLUMN DRAG)
    // ===============================

   public function actionUpdateColumnOrder()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $data = json_decode(Yii::$app->request->getRawBody(), true);

    if (!isset($data['order']) || empty($data['order'])) {
        return ['success' => false, 'msg' => 'Invalid data'];
    }

    // Board fetch (first item se)
    $boardId = $data['order'][0]['board_id'] ?? null;
    if (!$boardId) {
        return ['success' => false, 'msg' => 'Board ID missing'];
    }

    $board = \common\models\Board::findOne($boardId);
    if (!$board) {
        return ['success' => false, 'msg' => 'Board not found'];
    }

    /* ===============================
        ACCESS CONTROL
       =============================== */

    // Owner always allowed
    $isOwner = ($board->created_by == Yii::$app->user->id);

    // Team member allowed only if team exists
    $isMember = false;
    if ($board->team_id) {
        $isMember = \common\models\TeamMembers::find()
            ->where([
                'team_id' => $board->team_id,
                'user_id' => Yii::$app->user->id
            ])
            ->exists();
    }

    if (!$isOwner && !$isMember) {
        return ['success' => false, 'msg' => 'Access denied'];
    }

    /* ===============================
    COLUMN POSITION UPDATE
       =============================== */

    foreach ($data['order'] as $item) {

        KanbanColumn::updateAll(
            ['position' => (int)$item['position']],
            [
                'board_id' => $boardId,
                'status'   => $item['status']
            ]
        );
    }

    return ['success' => true, 'msg' => 'Column order updated'];
}



    
    // subtask logic start header_register_callback

  /************* ADD SUBTASK *************/
public function actionAddSubtask($task_id)
{
    $subtask = new Subtask();
    $subtask->task_id = $task_id;
    $subtask->title   = Yii::$app->request->post('title');


    
    if ($subtask->save()) {
        Yii::$app->session->setFlash('success', 'Subtask added');
    }

    

    return $this->redirect(['task/update', 'id' => $task_id]);
}




public function actionToggleSubtask($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $m = Subtask::findOne($id);
    if (!$m) {
        return ['success' => false, 'msg' => 'Subtask not found'];
    }

    $m->is_done = ($m->is_done == 1) ? 0 : 1;

    if (!$m->save(false)) {
        return [
            'success' => false,
            'errors'  => $m->getErrors()
        ];
    }

    Logger::add(
    "Subtask Updated",
    "Subtask #{$id} → " . ($m->is_done ? "Completed" : "Pending"),
    $m->task->board->team_id,
    $m->task->board_id
);


    return [
        'success' => true,
        'is_done' => $m->is_done
    ];
}


/************* DELETE *************/
public function actionDeleteSubtask($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $m = Subtask::findOne($id);
        if(!$m) return ['success'=>false];

        // ACTIVITY LOG
        Logger::add(
    "Subtask Deleted",
    "Subtask #{$id}",
    $m->task->board->team_id,
    $m->task->board_id
);



        $m->delete();
        return ['success'=>true];
    }


    public function actionAll()
{
    $userId = Yii::$app->user->id;

    $query = \common\models\Task::find()
        ->where(['created_by' => $userId])
        ->orderBy(['created_at' => SORT_DESC]);

    $dataProvider = new \yii\data\ActiveDataProvider([
        'query' => $query,
        'pagination' => ['pageSize' => 10],
    ]);

    return $this->render('all', [
        'dataProvider' => $dataProvider
    ]);
}


public function actionDeleteImage($id)
{
    $img = \common\models\TaskImage::findOne($id);
    if (!$img) {
        throw new \yii\web\NotFoundHttpException();
    }

    // delete file
    $file = Yii::getAlias('@webroot/uploads/tasks/') . $img->image;
    if (file_exists($file)) {
        unlink($file);
    }

    $img->delete();

    // SAME PAGE PAR REDIRECT
    return $this->redirect(Yii::$app->request->referrer);
}

public function actionDeleteAttachment($id)
{
    $attach = TaskAttachment::findOne($id);
    if (!$attach) {
        throw new NotFoundHttpException();
    }

    $file = Yii::getAlias('@webroot/uploads/tasks/') . $attach->file;
    if (file_exists($file)) {
        unlink($file);
    }

    $taskId = $attach->task_id;
    $attach->delete();

    Yii::$app->session->setFlash('success', 'Attachment deleted');
    return $this->redirect(['task/update', 'id' => $taskId]);
}

public function actionAddComment($id)
{
    $task = $this->findModel($id);

    $commentText = trim(Yii::$app->request->post('comment'));

    if ($commentText) {

        $comment = new \common\models\TaskComment();
        $comment->task_id = $task->id;
        $comment->user_id = Yii::$app->user->id;
        $comment->comment = $commentText;
        $comment->save(false);

        /* ===============================
        COMMENT ACTIVITY
        =============================== */
        $user = Yii::$app->user->identity;

        Logger::add(
            "Comment Added",
            "Comment added on Task #{$task->id} by {$user->username}",
            $task->board->team_id,
            $task->board_id
        );

        /* ===============================
        COMMENT EMAIL NOTIFICATION
        =============================== */
        $this->sendCommentMail($task, $comment);
    }

    return $this->redirect(['task/update', 'id' => $task->id]);
}

protected function sendCommentMail($task, $comment)
{
    $assignee = $task->assignee;
    $commenter = $comment->user;

    // No assignee OR self comment
    if (!$assignee || !$assignee->email || $assignee->id == $commenter->id) {
        return;
    }

    Yii::$app->mailer->compose(
        'taskCommented',
        [
            'task'      => $task,
            'comment'   => $comment,
            'commenter' => $commenter,
        ]
    )
    ->setTo($assignee->email)
    ->setFrom([Yii::$app->params['adminEmail'] => 'Task Manager'])
    ->setSubject(
        '💬 New Comment by ' . $commenter->username . ' on Task: ' . $task->title
    )
    ->send();
}

public function actionUploadAttachment($id)
{
    $task = $this->findModel($id);

    $task->attachmentFiles = UploadedFile::getInstances($task, 'attachmentFiles');

    // ===== FILE COUNT CHECK (DB + NEW) =====
    $existingCount = TaskAttachment::find()
        ->where(['task_id' => $task->id])
        ->count();

    if (($existingCount + count($task->attachmentFiles)) > 5) {
        Yii::$app->session->setFlash(
            'error',
            'Max 5 attachments allowed per task.'
        );
        return $this->redirect(['task/update', 'id' => $task->id]);
    }

    // MODEL VALIDATION (total 10MB check)
    if (!$task->validate(['attachmentFiles'])) {
        Yii::$app->session->setFlash(
            'error',
            implode('<br>', $task->getFirstErrors())
        );
        return $this->redirect(['task/update', 'id' => $task->id]);
    }

    // ===== UPLOAD =====
    $task->uploadAttachments();
    Yii::$app->session->setFlash('success', 'Attachment uploaded');

    return $this->redirect(['task/update', 'id' => $task->id]);
}




public function actionView($id)
{
    $model = $this->findModel($id);

    //  Activity fetch
    $activities = ActivityLog::find()
        ->where([
            'model'    => 'task',
            'model_id' => $model->id
        ])
        ->orderBy(['created_at' => SORT_DESC])
        ->all();

    return $this->render('view', [
        'model' => $model,
        'activities' => $activities,
    ]);
}
}