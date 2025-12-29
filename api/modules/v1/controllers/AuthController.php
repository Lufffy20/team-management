<?php
namespace frontend\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use common\models\User;

class AuthController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // JSON response
        $behaviors['contentNegotiator']['formats']['application/json']
            = Response::FORMAT_JSON;

        // Bearer auth
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['login', 'register'],
        ];

        return $behaviors;
    }

    /* ================= REGISTER ================= */
    public function actionRegister()
    {
        $user = new User();
        $user->scenario = 'api-register';

        if (!$user->load(Yii::$app->request->getBodyParams(), '')) {
            return ['status' => false, 'message' => 'Invalid request body'];
        }

        $user->setPassword($user->password);
        $user->generateAuthKey();
        $user->access_token = Yii::$app->security->generateRandomString(64);
        $user->status = User::STATUS_ACTIVE;

        if (!$user->validate()) {
            return ['status' => false, 'errors' => $user->getErrors()];
        }

        $user->save(false);

        return [
            'status' => true,
            'message' => 'User registered successfully',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ]
        ];
    }

    /* ================= LOGIN ================= */
    public function actionLogin()
    {
        $data = Yii::$app->request->getBodyParams();

        $user = User::find()
            ->where(['email' => $data['email'] ?? null])
            ->one();

        if (!$user || !$user->validatePassword($data['password'] ?? '')) {
            return ['status' => false, 'message' => 'Invalid credentials'];
        }

        $user->access_token = Yii::$app->security->generateRandomString(64);
        $user->save(false);

        return [
            'status' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $user->access_token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ]
            ]
        ];
    }

    /* ================= PROFILE ================= */
    public function actionProfile()
    {
        $user = Yii::$app->user->identity;

        return [
            'status' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ]
        ];
    }

    /* ================= LOGOUT ================= */
    public function actionLogout()
{
    if (Yii::$app->user->isGuest) {
        return ['status' => false, 'message' => 'User not logged in'];
    }

    $user = Yii::$app->user->identity;

}
}