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
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
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
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
   public function actionIndex()
{
    $searchModel = new UserSearch();
    $dataProvider = $searchModel->search($this->request->queryParams);

    // ⭐ Enable pagination + page size set
    $dataProvider->pagination->pageSize = 5;

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}


    /**
     * Displays a single User model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
{
    $model = new User();
    $model->scenario = 'create';

    if ($model->load(Yii::$app->request->post())) {

        // Avatar file
        $model->avatarFile = UploadedFile::getInstance($model, 'avatarFile');

        // Validate model
        if ($model->validate()) {

            // Handle avatar upload
            if ($model->avatarFile) {
                $fileName = uniqid() . '.' . $model->avatarFile->extension;
                $uploadPath = Yii::getAlias('@webroot/uploads/avatars/' . $fileName);

                if (!is_dir(Yii::getAlias('@webroot/uploads/avatars'))) {
                    mkdir(Yii::getAlias('@webroot/uploads/avatars'), 0777, true);
                }

                $model->avatarFile->saveAs($uploadPath);
                $model->avatar = $fileName;
            }

            // Handle password
            if (!empty($model->password)) {
                $model->setPassword($model->password);
                $model->generateAuthKey();
            }

            $model->save(false);
            return $this->redirect(['view', 'id' => $model->id]);
        }
    }

    return $this->render('create', ['model' => $model]);
}


public function actionUpdate($id)
{
    $model = $this->findModel($id);
    $model->scenario = 'update';

    $oldAvatar = $model->avatar;

    if ($model->load(Yii::$app->request->post())) {

        // Handle avatar upload
        $model->avatarFile = UploadedFile::getInstance($model, 'avatarFile');

        if ($model->validate()) {

            if ($model->avatarFile) {

                $fileName = uniqid() . '.' . $model->avatarFile->extension;
                $uploadPath = Yii::getAlias('@webroot/uploads/avatars/' . $fileName);

                if (!is_dir(Yii::getAlias('@webroot/uploads/avatars'))) {
                    mkdir(Yii::getAlias('@webroot/uploads/avatars'), 0777, true);
                }

                $model->avatarFile->saveAs($uploadPath);
                $model->avatar = $fileName;

                // delete old avatar
                if ($oldAvatar && file_exists(Yii::getAlias('@webroot/uploads/avatars/' . $oldAvatar))) {
                    @unlink(Yii::getAlias('@webroot/uploads/avatars/' . $oldAvatar));
                }

            } else {
                $model->avatar = $oldAvatar; // keep old
            }

            // Password update (optional)
            if (!empty($model->password)) {
                $model->setPassword($model->password);
            }

            $model->save(false);

            // Update Stripe customer if name or email changed and customer exists
            if ($model->stripe_customer_id && ($model->username !== $model->getOldAttribute('username') || $model->email !== $model->getOldAttribute('email'))) {
                try {
                    $stripeService = new \common\components\StripeService();
                    $stripeService->updateCustomer(
                        $model->stripe_customer_id,
                        $model->username,
                        $model->email
                    );
                } catch (\Throwable $e) {
                    Yii::error(
                        'Failed to update Stripe customer in admin update: ' . $e->getMessage(),
                        __METHOD__
                    );
                    // Don't throw the exception as it shouldn't prevent the profile update
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }
    }

    return $this->render('update', ['model' => $model]);
}



    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionProfile()
{
    $userId = Yii::$app->user->id;

    if (!$userId) {
        throw new \yii\web\UnauthorizedHttpException("Please login first.");
    }

    $model = User::findOne($userId);

    if (!$model) {
        throw new NotFoundHttpException("User not found.");
    }

    $oldEmail = $model->email;
    $oldAvatar = $model->avatar;

    // Load form data
    if ($model->load(Yii::$app->request->post())) {

        // Check if avatar uploaded
        $model->avatarFile = UploadedFile::getInstance($model, 'avatarFile');

        // -------------------------------
        // 1) EMAIL CHANGE LOGIC
        // -------------------------------
        if ($model->email !== $oldEmail) {

            $newEmail = $model->email;

            // Store new email temporarily
            $model->pending_email = $newEmail;

            // old email stays for login
            $model->email = $oldEmail;

            // set verification token
            $model->verification_token = Yii::$app->security->generateRandomString() . '_' . time();

            $model->save(false);

            // Update Stripe customer if customer exists
            if ($model->stripe_customer_id) {
                try {
                    $stripeService = new \common\components\StripeService();
                    $stripeService->updateCustomer(
                        $model->stripe_customer_id,
                        $model->username,
                        $newEmail  // Use the new email for Stripe update
                    );
                } catch (\Throwable $e) {
                    Yii::error(
                        'Failed to update Stripe customer email: ' . $e->getMessage(),
                        __METHOD__
                    );
                    // Don't throw the exception as it shouldn't prevent the email change process
                }
            }

            // send email
            Yii::$app->mailer->compose('emailChange', ['user' => $model])
                ->setTo($newEmail)
                ->setFrom(['no-reply@yourdomain.com' => Yii::$app->name])
                ->setSubject('Verify Your New Email')
                ->send();

            Yii::$app->session->setFlash(
                'success',
                "A verification link has been sent to <b>$newEmail</b>. Please verify to update your email."
            );

            return $this->refresh();
        }

        // -------------------------------
        // 2) AVATAR UPLOAD LOGIC
        // -------------------------------
        if ($model->avatarFile) {

            $uploadDir = Yii::getAlias('@webroot/uploads/avatars/');

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if ($oldAvatar && file_exists($uploadDir . $oldAvatar)) {
                unlink($uploadDir . $oldAvatar);
            }

            $newFile = time() . '_' . uniqid() . '.' . $model->avatarFile->extension;
            $path = $uploadDir . $newFile;

            if ($model->avatarFile->saveAs($path)) {
                $model->avatar = $newFile;
            }
        }

        // -------------------------------
        // 3) NORMAL PROFILE UPDATE
        // -------------------------------
        if ($model->save(false)) {
            // Update Stripe customer if name or email changed and customer exists
            if ($model->stripe_customer_id && ($model->username !== $model->getOldAttribute('username') || $model->email !== $model->getOldAttribute('email'))) {
                try {
                    $stripeService = new \common\components\StripeService();
                    $stripeService->updateCustomer(
                        $model->stripe_customer_id,
                        $model->username,
                        $model->email
                    );
                } catch (\Throwable $e) {
                    Yii::error(
                        'Failed to update Stripe customer: ' . $e->getMessage(),
                        __METHOD__
                    );
                    // Don't throw the exception as it shouldn't prevent the profile update
                }
            }

            Yii::$app->session->setFlash('success', 'Profile updated successfully.');
            return $this->refresh();
        }
    }

    return $this->render('profile', [
        'model' => $model,
    ]);
}



public function actionDeleteAvatar()
{
    $model = User::findOne(Yii::$app->user->id);

    if (!$model) {
        throw new NotFoundHttpException("User not found");
    }

    $path = Yii::getAlias('@webroot/uploads/avatars/' . $model->avatar);

    if ($model->avatar && file_exists($path)) {
        unlink($path);
    }

    $model->avatar = null;
    $model->save(false);

    Yii::$app->session->setFlash('success', 'Profile photo removed successfully.');
    return $this->redirect(['profile']);
}


public function actionVerifyNewEmail($token)
{
    $user = User::findOne(['verification_token' => $token]);

    if (!$user || !$user->pending_email) {
        throw new NotFoundHttpException("Invalid verification link.");
    }

    $user->email = $user->pending_email;
    $user->pending_email = null;
    $user->verification_token = null;

    $user->save(false);

    // Update Stripe customer if customer exists
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
                'Failed to update Stripe customer after email verification: ' . $e->getMessage(),
                __METHOD__
            );
            // Don't throw the exception as it shouldn't prevent the email verification
        }
    }

    Yii::$app->session->setFlash('success', 'Your email has been updated successfully.');
    return $this->redirect(['/user/profile']);
}


}
