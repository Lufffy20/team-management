<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use common\models\User;
use common\models\Task;
use common\models\Board;
use yii\helpers\ArrayHelper;
use common\models\TeamMembers;
use common\components\StripeService;




class ManagmentController extends Controller
{

    public function beforeAction($action)
    {
        if (YII_ENV_TEST) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    /**
     * {@inheritdoc}
     */
    public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,

            //  Only protect Managment actions
            'only' => [
                'mytasks',
                'create-task',
                'update-task',
                'delete-task',
                'view-task',
                'profile',
            ],

            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'], //  logged-in users only
                ],
            ],

            // IMPORTANT for Codeception (302 expected)
            'denyCallback' => function () {
                return Yii::$app->response->redirect(['/site/login']);
            },
        ],
    ];
}

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }




    // public function actionKanban()
    // {
    //     return $this->render('kanban');
    // }

public function actionMytasks($status = null)
{
    $userId = Yii::$app->user->id;
    $query = \common\models\Task::find()->where(['assignee_id' => $userId]);

    if ($status) {
        $query->andWhere(['status' => $status]);
    }

    $tasks = $query->orderBy(['due_date' => SORT_ASC])->all();

    return $this->render('mytasks', [
        'tasks' => $tasks,
        'status' => $status
    ]);
}

public function actionCreateTask()
{
    $model = new \common\models\Task();
    $model->created_by = Yii::$app->user->id;

    if ($model->load(Yii::$app->request->post())) {

        //  AUTO BOARD (TEST + NORMAL)
        if (empty($model->board_id)) {

            $team = \common\models\Team::find()->one();
            if (!$team) {
                $team = new \common\models\Team([
                    'name' => 'Default Team',
                    'created_by' => Yii::$app->user->id,
                ]);
                $team->save(false);
            }

            $board = \common\models\Board::find()
                ->where(['created_by' => Yii::$app->user->id])
                ->one();

            if (!$board) {
                $board = new \common\models\Board([
                    'title' => 'Default Board',
                    'created_by' => Yii::$app->user->id,
                    'team_id' => $team->id,
                ]);
                $board->save(false);
            }

            $model->board_id = $board->id;
        }

        if ($model->save()) {

            //  AJAX response
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => true];
            }

            //  NORMAL form response
            Yii::$app->session->setFlash('success', 'Task created successfully.');
            return $this->redirect(['mytasks']);
        }

        // AJAX validation errors
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => false, 'errors' => $model->errors];
        }
    }

    $boards = \common\models\Board::find()
        ->where(['created_by' => Yii::$app->user->id])
        ->all();

    return $this->render('task_form', [
        'model' => $model,
        'boards' => $boards,
    ]);
}



public function actionUpdateTask($id)
{
    $model = \common\models\Task::findOne($id);

    if (!$model) {
        throw new \yii\web\NotFoundHttpException("Task not found");
    }

    $boards = Board::find()
        ->orderBy(['title' => SORT_ASC])
        ->all();

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        Yii::$app->session->setFlash('success', 'Task updated successfully.');
        return $this->redirect(['managment/mytasks']);
    }

    return $this->render('task_form', [
        'model'  => $model,
        'boards' => $boards,
    ]);
}
public function actionDeleteTask($id)
{
    $model = \common\models\Task::findOne($id);

    if ($model) {
        $model->delete();
        Yii::$app->session->setFlash('success', 'Task deleted.');
    }

    return $this->redirect(['managment/mytasks']);
}


public function actionViewTask($id)
{
    $model = Task::findOne($id);

    if (!$model) {
        throw new NotFoundHttpException("Task not found");
    }

    return $this->render('task_view', [
        'model' => $model
    ]);
}


    public function actionProfile()
{
    /** @var \common\models\User $user */
    $user = Yii::$app->user->identity;

    if (!$user) {
        throw new NotFoundHttpException('User not found.');
    }

    if (Yii::$app->request->isPost) {

        $post = Yii::$app->request->post();

        if (isset($post['User']['username'])) {
            $user->username = trim($post['User']['username']);
        }

        $newEmail = isset($post['User']['email'])
            ? trim($post['User']['email'])
            : $user->email;

        if ($newEmail === $user->email) {

            // Save username (and other fields if any)
            $user->save(false);

            // Stripe update allowed (email verified already)
            if (!empty($user->stripe_customer_id)) {
                try {
                    $stripeService = new \common\components\StripeService();
                    $stripeService->updateCustomer(
                        $user->stripe_customer_id,
                        $user->username,
                        $user->email
                    );
                } catch (\Throwable $e) {
                    Yii::error(
                        'Stripe update failed on profile update: ' . $e->getMessage(),
                        __METHOD__
                    );
                }
            }

            Yii::$app->session->setFlash(
                'success',
                'Profile updated successfully.'
            );

            return $this->refresh();
        }

        // Check duplicate email
        $exists = \common\models\User::find()
            ->where(['email' => $newEmail])
            ->andWhere(['<>', 'id', $user->id])
            ->exists();

        if ($exists) {
            Yii::$app->session->setFlash(
                'error',
                'This email is already used by another account.'
            );
            return $this->refresh();
        }

        // Store pending email + token
        $user->pending_email = $newEmail;
        $user->verification_token =
            Yii::$app->security->generateRandomString() . '_' . time();

        $user->save(false);

        // Send verification mail
        Yii::$app->mailer->compose('emailChange', ['user' => $user])
            ->setTo($newEmail)
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
            ->setSubject('Verify Your New Email Address')
            ->send();

        Yii::$app->session->setFlash(
            'info',
            'A verification link has been sent to your new email address. Please verify to update your email.'
        );

        return $this->refresh();
    }

    return $this->render('profile', [
        'model' => $user,
    ]);
}

public function actionProfilePicture()
{
    $model = User::findOne(Yii::$app->user->id);
    $model->avatarFile = UploadedFile::getInstance($model, 'avatarFile');


    if (!$model->avatarFile) {
        Yii::$app->session->setFlash('error', 'Please select an image.');
        return $this->redirect(['profile']);
    }

    $model->scenario = 'upload';

    if (!$model->validate(['avatarFile'])) {
        Yii::$app->session->setFlash('error', current($model->getFirstErrors()));
        return $this->redirect(['profile']);
    }

    $uploadDir = Yii::getAlias('@webroot/uploads/avatars/');
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // Delete old
    if ($model->avatar) {
        $old = $uploadDir . $model->avatar;
        if (file_exists($old)) unlink($old);
    }

    $newFile = time() . '_' . uniqid() . '.' . $model->avatarFile->extension;
    $path = $uploadDir . $newFile;

    if ($model->avatarFile->saveAs($path)) {
        $model->avatar = $newFile;

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'Profile photo updated!');
        }
    } else {
        Yii::$app->session->setFlash('error', 'Upload failed.');
    }

    return $this->redirect(['profile']);
}




/**
 * DELETE PROFILE PHOTO
 */
public function actionDeleteAvatar()
{
    $userId = Yii::$app->user->id;

    if (!$userId) {
        throw new \yii\web\UnauthorizedHttpException("Please login first.");
    }

    $model = \common\models\User::findOne($userId);

    if ($model && $model->avatar) {

        $file = Yii::getAlias('@webroot/uploads/avatars/' . $model->avatar);

        if (file_exists($file)) {
            unlink($file);
        }

        $model->avatar = null;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'Profile photo removed.');
    }

    return $this->redirect(['profile']);
}
}
