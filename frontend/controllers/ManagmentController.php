<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use common\models\User;
use common\models\Task;
use common\models\Board;
use common\models\TeamMembers;
use common\components\StripeService;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;

/**
 * ManagmentController
 *
 * Handles user task management, profile updates,
 * email verification, avatar upload, and related actions.
 */
class ManagmentController extends Controller
{
    /**
     * Disable CSRF validation in test environment.
     */
    public function beforeAction($action)
    {
        if (YII_ENV_TEST) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Access control rules.
     * Only logged-in users can access management actions.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,

                // Protected actions
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
                        'roles' => ['@'], // logged-in users only
                    ],
                ],

                // Redirect guests to login (important for tests)
                'denyCallback' => function () {
                    return Yii::$app->response->redirect(['/site/login']);
                },
            ],
        ];
    }

    /**
     * External actions.
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

    /**
     * SHOW MY TASKS
     * --------------------------------------------------
     * Displays tasks that are assigned to the
     * currently logged-in user.
     *
     * 🔹 Optional: status filter (todo / in_progress / done etc.)
     */
    public function actionMytasks($status = null)
    {
        // Logged-in user ID
        $userId = Yii::$app->user->id;

        // Base query → only tasks assigned to current user
        $query = Task::find()
            ->where(['assignee_id' => $userId]); // 🎯 assigned tasks only

        // Optional status filter
        if ($status) {
            $query->andWhere(['status' => $status]); // filter by status
        }

        // Fetch tasks (nearest due date first)
        $tasks = $query
            ->orderBy(['due_date' => SORT_ASC])
            ->all();

        return $this->render('mytasks', [
            'tasks'  => $tasks,
            'status' => $status,
        ]);
    }

    /**
     * CREATE TASK
     * --------------------------------------------------
     * Creates a new task for the logged-in user.
     *
     * 🔹 If board is NOT selected:
     *    - Ensure a default team exists
     *    - Ensure a board exists where user is a member
     *    - Auto-create board + membership if required
     *
     * 🔹 Supports:
     *    - Normal form submit
     *    - AJAX submit
     */
    public function actionCreateTask()
    {
        $model = new Task();
        $model->created_by = Yii::$app->user->id; // task creator

        if ($model->load(Yii::$app->request->post())) {

            /* =================================================
         * AUTO BOARD ASSIGNMENT
         * -------------------------------------------------
         * Runs only when board_id is empty
         * ================================================= */
            if (empty($model->board_id)) {

                // 1️⃣ Ensure at least one team exists
                $team = \common\models\Team::find()
                    ->where(['created_by' => Yii::$app->user->id])
                    ->one();

                if (!$team) {
                    $team = new \common\models\Team([
                        'name'       => 'Default Team',
                        'created_by' => Yii::$app->user->id,
                    ]);
                    $team->save(false); // skip validation
                }

                // 2️⃣ Find board where user is already a MEMBER
                $board = Board::find()
                    ->innerJoin('board_members bm', 'bm.board_id = board.id')
                    ->where(['bm.user_id' => Yii::$app->user->id])
                    ->one();

                // 3️⃣ If no board found → create default board
                if (!$board) {

                    $board = new Board([
                        'title'       => 'Default Board',
                        'created_by'  => Yii::$app->user->id,
                        'team_id'     => $team->id,
                    ]);
                    $board->save(false);

                    /**
                     * 🔥 IMPORTANT
                     * Board creator MUST also be a board member,
                     * otherwise task assignment & visibility breaks
                     */
                    Yii::$app->db->createCommand()->insert('board_members', [
                        'board_id' => $board->id,
                        'user_id'  => Yii::$app->user->id,
                    ])->execute();
                }

                // Assign resolved board to task
                $model->board_id = $board->id;
            }

            /* =================================================
         * SAVE TASK
         * ================================================= */
            if ($model->save()) {

                // AJAX response
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return ['success' => true];
                }

                // Normal form response
                Yii::$app->session->setFlash('success', 'Task created successfully.');
                return $this->redirect(['mytasks']);
            }

            // AJAX validation error response
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'errors'  => $model->errors,
                ];
            }
        }

        /**
         * 🔹 Board dropdown data
         * Only boards where user is a MEMBER
         */
        $boards = Board::find()
            ->innerJoin('board_members bm', 'bm.board_id = board.id')
            ->where(['bm.user_id' => Yii::$app->user->id])
            ->all();

        return $this->render('task_form', [
            'model'  => $model,
            'boards' => $boards,
        ]);
    }

    /**
     * UPDATE TASK
     * --------------------------------------------------
     * Updates an existing task.
     */
    public function actionUpdateTask($id)
    {
        // Fetch task
        $model = Task::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Task not found');
        }

        // Fetch boards for dropdown
        $boards = Board::find()
            ->orderBy(['title' => SORT_ASC])
            ->all();

        // Save updates
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Task updated successfully.');
            return $this->redirect(['managment/mytasks']);
        }

        return $this->render('task_form', [
            'model'  => $model,
            'boards' => $boards,
        ]);
    }

    /**
     * Deletes a task.
     */
    public function actionDeleteTask($id)
    {
        $model = Task::findOne($id);

        if ($model) {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Task deleted.');
        }

        return $this->redirect(['managment/mytasks']);
    }

    /**
     * Displays a single task.
     */
    public function actionViewTask($id)
    {
        $model = Task::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Task not found');
        }

        return $this->render('task_view', [
            'model' => $model,
        ]);
    }

    /**
     * User profile update.
     * Handles username change, email change with verification,
     * and Stripe customer update.
     */
    public function actionProfile()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        if (Yii::$app->request->isPost) {

            $post = Yii::$app->request->post();

            // Update username
            if (isset($post['User']['username'])) {
                $user->username = trim($post['User']['username']);
            }

            $newEmail = isset($post['User']['email'])
                ? trim($post['User']['email'])
                : $user->email;

            /* EMAIL NOT CHANGED */
            if ($newEmail === $user->email) {

                $user->save(false);

                // Update Stripe customer
                if (!empty($user->stripe_customer_id)) {
                    try {
                        $stripeService = new StripeService();
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

            /* EMAIL CHANGED */
            $exists = User::find()
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

            // Store pending email and verification token
            $user->pending_email = $newEmail;
            $user->verification_token =
                Yii::$app->security->generateRandomString() . '_' . time();

            $user->save(false);

            // Send verification email
            Yii::$app->mailer
                ->compose('emailChange', ['user' => $user])
                ->setTo($newEmail)
                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
                ->setSubject('Verify Your New Email Address')
                ->send();

            Yii::$app->session->setFlash(
                'info',
                'A verification link has been sent to your new email address.'
            );

            return $this->refresh();
        }

        return $this->render('profile', [
            'model' => $user,
        ]);
    }

    /**
     * Upload or update profile picture.
     */
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
            Yii::$app->session->setFlash(
                'error',
                current($model->getFirstErrors())
            );
            return $this->redirect(['profile']);
        }

        $uploadDir = Yii::getAlias('@common/uploads/avatars/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Remove old avatar
        if ($model->avatar) {
            $old = $uploadDir . $model->avatar;
            if (file_exists($old)) {
                unlink($old);
            }
        }

        $newFile = time() . '_' . uniqid() . '.' . $model->avatarFile->extension;

        if ($model->avatarFile->saveAs($uploadDir . $newFile)) {
            $model->avatar = $newFile;
            $model->save(false);
            Yii::$app->session->setFlash('success', 'Profile photo updated!');
        } else {
            Yii::$app->session->setFlash('error', 'Upload failed.');
        }

        return $this->redirect(['profile']);
    }

    /**
     * Deletes profile avatar.
     */
    public function actionDeleteAvatar()
    {
        $userId = Yii::$app->user->id;

        if (!$userId) {
            throw new \yii\web\UnauthorizedHttpException('Please login first.');
        }

        $model = User::findOne($userId);

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
