<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\UserSettings;

/**
 * SettingController
 *
 * Handles user preference settings.
 * Currently supports theme (dark mode) configuration.
 */
class SettingController extends Controller
{
    /**
     * Access control.
     * Only logged-in users can access settings.
     */
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
     * Displays and updates user settings.
     *
     * URL: /setting/index
     *
     * Flow:
     * 1) Load current user settings
     * 2) Save changes on form submit
     * 3) Refresh page to apply changes
     */
    public function actionIndex()
    {
        $model = new UserSettings();

        // Load settings linked to current user
        $model->loadFromUser();

        // Save settings on form submit
        if (
            $model->load(Yii::$app->request->post()) &&
            $model->save()
        ) {
            Yii::$app->session->setFlash(
                'success',
                'Theme updated successfully.'
            );
            return $this->refresh();
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
