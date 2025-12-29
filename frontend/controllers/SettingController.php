<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\UserSettings;

class SettingController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // login required
                    ],
                ],
            ],
        ];
    }

    /**
     * Settings page (Dark Mode only)
     * URL: /setting/index
     */
    public function actionIndex()
    {
        $model = new UserSettings();
        $model->loadFromUser();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Theme updated successfully.');
            return $this->refresh();
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
