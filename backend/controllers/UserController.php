<?php

namespace backend\controllers;

use Yii;
use yii\web\UploadedFile;
use common\models\User;
use common\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController
 *
 * Handles user management, profile update,
 * avatar upload, email verification, and Stripe sync.
 */
class UserController extends Controller
{
    /**
     * Defines controller behaviors.
     * Restricts delete action to POST requests only.
     *
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'], // delete allowed only via POST
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all users with search and pagination.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel  = new UserSearch(); // search model
        $dataProvider = $searchModel->search(
            $this->request->queryParams // GET params
        );

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider'=> $dataProvider,
        ]);
    }

    /**
     * Displays a single user.
     *
     * @param int $id User ID
     * @return string
     * @throws NotFoundHttpException if user not found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new user.
     * Handles avatar upload and password setup.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = 'create'; // create scenario

        if ($model->load(Yii::$app->request->post())) {

            // Handle avatar upload
            $model->avatarFile = UploadedFile::getInstance($model, 'avatarFile');

            if ($model->validate()) {

                // Save avatar if uploaded
                if ($model->avatarFile) {
                    $fileName   = uniqid() . '.' . $model->avatarFile->extension;
                    $uploadDir  = Yii::getAlias('@webroot/uploads/avatars/');
                    $uploadPath = $uploadDir . $fileName;

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $model->avatarFile->saveAs($uploadPath);
                    $model->avatar = $fileName;
                }

                // Set password and auth key
                if (!empty($model->password)) {
                    $model->setPassword($model->password);
                    $model->generateAuthKey();
                }

                $model->save(false);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing user.
     * Handles avatar replacement, password update,
     * and Stripe customer sync.
     *
     * @param int $id User ID
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        $oldAvatar = $model->avatar;

        if ($model->load(Yii::$app->request->post())) {

            // Avatar upload
            $model->avatarFile = UploadedFile::getInstance($model, 'avatarFile');

            if ($model->validate()) {

                if ($model->avatarFile) {

                    $fileName   = uniqid() . '.' . $model->avatarFile->extension;
                    $uploadDir  = Yii::getAlias('@webroot/uploads/avatars/');
                    $uploadPath = $uploadDir . $fileName;

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $model->avatarFile->saveAs($uploadPath);
                    $model->avatar = $fileName;

                    // Remove old avatar
                    if ($oldAvatar && file_exists($uploadDir . $oldAvatar)) {
                        unlink($uploadDir . $oldAvatar);
                    }

                } else {
                    // Keep existing avatar
                    $model->avatar = $oldAvatar;
                }

                // Optional password update
                if (!empty($model->password)) {
                    $model->setPassword($model->password);
                }

                $model->save(false);

                // Sync Stripe customer if name/email changed
                if (
                    $model->stripe_customer_id &&
                    (
                        $model->username !== $model->getOldAttribute('username') ||
                        $model->email !== $model->getOldAttribute('email')
                    )
                ) {
                    try {
                        $stripeService = new \common\components\StripeService();
                        $stripeService->updateCustomer(
                            $model->stripe_customer_id,
                            $model->username,
                            $model->email
                        );
                    } catch (\Throwable $e) {
                        Yii::error(
                            'Stripe update failed (admin update): ' . $e->getMessage(),
                            __METHOD__
                        );
                    }
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes a user.
     *
     * @param int $id User ID
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds user by primary key.
     *
     * @param int $id User ID
     * @return User
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Logged-in user profile page.
     * Handles email change, avatar upload, and Stripe sync.
     */
    public function actionProfile()
    {
        $userId = Yii::$app->user->id;

        if (!$userId) {
            throw new \yii\web\UnauthorizedHttpException('Please login first.');
        }

        $model = User::findOne($userId);
        if (!$model) {
            throw new NotFoundHttpException('User not found.');
        }

        $oldEmail  = $model->email;
        $oldAvatar = $model->avatar;

        if ($model->load(Yii::$app->request->post())) {

            $model->avatarFile = UploadedFile::getInstance($model, 'avatarFile');

            // 1) Email change flow
            if ($model->email !== $oldEmail) {

                $newEmail = $model->email;

                $model->pending_email      = $newEmail;
                $model->email              = $oldEmail;
                $model->verification_token =
                    Yii::$app->security->generateRandomString() . '_' . time();

                $model->save(false);

                // Update Stripe email if customer exists
                if ($model->stripe_customer_id) {
                    try {
                        $stripeService = new \common\components\StripeService();
                        $stripeService->updateCustomer(
                            $model->stripe_customer_id,
                            $model->username,
                            $newEmail
                        );
                    } catch (\Throwable $e) {
                        Yii::error(
                            'Stripe email update failed: ' . $e->getMessage(),
                            __METHOD__
                        );
                    }
                }

                // Send verification email
                Yii::$app->mailer
                    ->compose('emailChange', ['user' => $model])
                    ->setTo($newEmail)
                    ->setFrom(['no-reply@yourdomain.com' => Yii::$app->name])
                    ->setSubject('Verify Your New Email')
                    ->send();

                Yii::$app->session->setFlash(
                    'success',
                    "A verification link has been sent to <b>$newEmail</b>."
                );

                return $this->refresh();
            }

            // 2) Avatar upload
            if ($model->avatarFile) {

                $uploadDir = Yii::getAlias('@webroot/uploads/avatars/');

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if ($oldAvatar && file_exists($uploadDir . $oldAvatar)) {
                    unlink($uploadDir . $oldAvatar);
                }

                $newFile = time() . '_' . uniqid() . '.' . $model->avatarFile->extension;
                $model->avatarFile->saveAs($uploadDir . $newFile);
                $model->avatar = $newFile;
            }

            // 3) Save profile changes
            if ($model->save(false)) {
                Yii::$app->session->setFlash(
                    'success',
                    'Profile updated successfully.'
                );
                return $this->refresh();
            }
        }

        return $this->render('profile', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes logged-in user's avatar.
     */
    public function actionDeleteAvatar()
    {
        $model = User::findOne(Yii::$app->user->id);

        if (!$model) {
            throw new NotFoundHttpException('User not found');
        }

        $path = Yii::getAlias('@webroot/uploads/avatars/' . $model->avatar);

        if ($model->avatar && file_exists($path)) {
            unlink($path);
        }

        $model->avatar = null;
        $model->save(false);

        Yii::$app->session->setFlash(
            'success',
            'Profile photo removed successfully.'
        );

        return $this->redirect(['profile']);
    }

    /**
     * Verifies new email using token.
     *
     * @param string $token
     * @return \yii\web\Response
     */
    public function actionVerifyNewEmail($token)
    {
        $user = User::findOne(['verification_token' => $token]);

        if (!$user || !$user->pending_email) {
            throw new NotFoundHttpException('Invalid verification link.');
        }

        $user->email              = $user->pending_email;
        $user->pending_email      = null;
        $user->verification_token = null;
        $user->save(false);

        // Update Stripe after verification
        if ($user->stripe_customer_id) {
            try {
                $stripeService = new \common\components\StripeService();
                $stripeService->updateCustomer(
                    $user->stripe_customer_id,
                    $user->username,
                    $user->email
                );
            } catch (\Throwable $e) {
                Yii::error(
                    'Stripe update after email verification failed: ' . $e->getMessage(),
                    __METHOD__
                );
            }
        }

        Yii::$app->session->setFlash(
            'success',
            'Your email has been updated successfully.'
        );

        return $this->redirect(['/user/profile']);
    }
}
