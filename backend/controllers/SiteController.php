<?php

namespace backend\controllers;

use common\models\LoginForm;

use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use common\models\Task;
use common\models\Project;
use backend\models\PasswordResetRequestForm;
use backend\models\ResetPasswordForm;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;



/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [

                //  Guest allowed actions
                [
                    'actions' => [
                        'login',
                        'error',
                        'request-password-reset',
                        'reset-password'
                    ],
                    'allow' => true,
                    'roles' => ['?'],
                ],

                //  Logged-in admin only
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function () {
                        return Yii::$app->user->identity->role == 1;
                    }
                ],

                //  Logout for any logged-in user
                [
                    'actions' => ['logout'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ],

        'verbs' => [
            'class' => VerbFilter::class,
            'actions' => [
                'logout' => ['post'],
            ],
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
        ];
    }


    public function beforeAction($action)
{
    // ✅ Disable CSRF only for logout in test env
    if (YII_ENV_TEST && $action->id === 'logout') {
        $this->enableCsrfValidation = false;
    }

    return parent::beforeAction($action);
}

    /**
     * Displays homepage.
     *
     * @return string
     */
   public function actionIndex()
{
    $baseQuery = Task::find();

    /* ================= MAIN COUNTS ================= */
    $totalTasks      = (clone $baseQuery)->count();
    $doneCount       = (clone $baseQuery)->andWhere(['status' => 'done'])->count();
    $archivedCount   = (clone $baseQuery)->andWhere(['status' => 'archived'])->count();
    $inProgressCount = (clone $baseQuery)->andWhere(['status' => 'in_progress'])->count();

    /* ================= KANBAN SUMMARY ================= */
    $todoCount   = (clone $baseQuery)->andWhere(['status' => 'pending'])->count();
    $reviewCount = (clone $baseQuery)->andWhere(['status' => 'review'])->count();

    /* ================= STATUS CHART DATA (DONUT) ================= */
    $statusChart = [
        'todo'        => $todoCount,
        'in_progress' => $inProgressCount,
        'review'      => $reviewCount,
        'done'        => $doneCount,
        'archived'    => $archivedCount,
    ];

    /* ================= TASKS CREATED (LAST 7 DAYS) ================= */
    $weeklyStats = [];
    for ($i = 6; $i >= 0; $i--) {
        $start = strtotime(date('Y-m-d 00:00:00', strtotime("-$i days")));
        $end   = strtotime(date('Y-m-d 23:59:59', strtotime("-$i days")));

        $weeklyStats[] = [
            'day'   => date('D', strtotime("-$i days")),
            'count' => Task::find()
                ->where(['between', 'created_at', $start, $end])
                ->count(),
        ];
    }

    /* ================= PRIORITY BAR CHART ================= */
    $priorityStats = [
        'low'    => (clone $baseQuery)->andWhere(['priority' => 'low'])->count(),
        'medium' => (clone $baseQuery)->andWhere(['priority' => 'medium'])->count(),
        'high'   => (clone $baseQuery)->andWhere(['priority' => 'high'])->count(),
    ];

    /* ================= RECENT TASKS ================= */
    $recentTasks = Task::find()
        ->joinWith(['team', 'board'])
        ->orderBy(['task.created_at' => SORT_DESC])
        ->limit(5)
        ->all();

    return $this->render('index', compact(
        // Summary
        'totalTasks',
        'doneCount',
        'archivedCount',
        'inProgressCount',

        // Kanban
        'todoCount',
        'reviewCount',

        // Charts
        'statusChart',
        'weeklyStats',
        'priorityStats',

        // Recent
        'recentTasks'
    ));
}

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
{
    $this->layout = 'blank';

    if (!Yii::$app->user->isGuest) {
        return $this->goHome();
    }

    $model = new LoginForm();

    if ($model->load(Yii::$app->request->post()) && $model->login()) {

        // ✅ Allow login WITHOUT role restriction ONLY in test environment
        if (!YII_ENV_TEST) {

            // 🔥 Production: Only allow admin (role = 1)
            if (Yii::$app->user->identity->role != 1) {
                Yii::$app->user->logout();
                Yii::$app->session->setFlash('error', 'Access denied.');
                return $this->redirect(['login']);
            }
        }

        // Login success → redirect to dashboard
        return $this->goHome();
    }

    $model->password = '';

    return $this->render('login', [
        'model' => $model,
    ]);
}



    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionMytasks()
{
    $userId = Yii::$app->user->id;

    // Filters - always define to avoid undefined variable warning
    $status = Yii::$app->request->get('status', '');
    $priority = Yii::$app->request->get('priority', '');
    $due_date = Yii::$app->request->get('due_date', '');

    // Query user assigned tasks
    $query = Task::find()->where(['assigned_to' => $userId]);

    if (!empty($status)) {
        $query->andWhere(['status' => $status]);
    }

    if (!empty($priority)) {
        $query->andWhere(['priority' => $priority]);
    }

    if (!empty($due_date)) {
        $query->andWhere(['due_date' => $due_date]);
    }

    $tasks = $query->orderBy(['id' => SORT_DESC])->all();

    return $this->render('mytasks', [  // view name
        'tasks' => $tasks,
        'status' => $status,
        'priority' => $priority,
        'due_date' => $due_date,
    ]);
}

    public function actionAlltask()
{
    $searchModel = new \common\models\TaskSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    $users = \common\models\User::find()->all();

    return $this->render('alltask', [
        'searchModel'  => $searchModel,
        'dataProvider' => $dataProvider,
        'users'        => $users,
    ]);
}


 public function actionTeam()
    {
        return $this->render('team');
    }

public function actionResetPassword($token)
{

    $this->layout = 'blank';
    try {
        $model = new ResetPasswordForm($token);
    } catch (InvalidArgumentException $e) {
        throw new BadRequestHttpException($e->getMessage());
    }

    if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
        Yii::$app->session->setFlash('success', 'New password saved.');
        return $this->redirect(['login']);
    }

    return $this->render('resetPassword', [
        'model' => $model,
    ]);
}


public function actionRequestPasswordReset()
{
    $this->layout = 'blank';
    $model = new PasswordResetRequestForm();

    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        if ($model->sendEmail()) {
            Yii::$app->session->setFlash(
                'success',
                'Check your email for further instructions.'
            );

            // 🔥 SMALL IMPROVEMENT
            return $this->redirect(['login']);
        }

        Yii::$app->session->setFlash(
            'error',
            'Sorry, we are unable to reset password for the provided email.'
        );
    }

    return $this->render('requestPasswordResetToken', [
        'model' => $model,
    ]);
}

}
