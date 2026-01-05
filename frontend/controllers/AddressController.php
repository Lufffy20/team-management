<?php

namespace frontend\controllers;

use Yii;
use common\models\Address;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AddressController extends Controller
{
    /**
     * List all addresses
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Address::find()->where([
                'user_id' => Yii::$app->user->id
            ]),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * View single address
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Create address
     */
    public function actionCreate()
    {
        $model = new Address();
        $model->user_id = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Address added successfully');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Update address
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Address updated successfully');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Delete address
     */
    public function actionDelete()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');

        $model = Address::find()
            ->where([
                'id' => $id,
                'user_id' => Yii::$app->user->id
            ])
            ->one();

        if (!$model) {
            throw new \yii\web\NotFoundHttpException('Address not found');
        }

        $model->delete();

        return ['success' => true];
    }

    protected function findModel($id)
    {
        if (($model = Address::find()
            ->where([
                'id' => $id,
                'user_id' => Yii::$app->user->id,
            ])
            ->one()) !== null) {

            return $model;
        }

        throw new NotFoundHttpException('The requested address does not exist.');
    }
}
