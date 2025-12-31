<?php

namespace backend\controllers;

use Yii;
use common\models\LoginForm;
use common\models\Task;
use common\models\Project;
use backend\models\PasswordResetRequestForm;
use backend\models\ResetPasswordForm;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;

/**
 * SiteController
 *
 * Handles authentication, dashboard, tasks, and password reset actions.
 */
class SiteController extends Controller
{
    /**
     * Defines access control and HTTP verb rules.
     */
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [

                // Guest allowed actions
                [
                    'actions' => ['login', 'error'],
                    'allow'   => true,
                ],

                // Logged-in admin only (role = 1)
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action) {
                        return Yii::$app->user->identity->role == 1;
                    },
                ],

                // Logout allowed for any logged-in user
                [
                    'actions' => ['logout'],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ],

        // HTTP verb restrictions
        'verbs' => [
            'class' => VerbFilter::class,
            'actions' => [
                'logout' => ['post'],
            ],
        ],
    ];
}

    /**
     * Declares external actions.
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Runs before every action.
     * Disables CSRF validation for logout in test environment only.
     */
    public function beforeAction($action)
    {
        if (YII_ENV_TEST && $action->id === 'logout') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Displays the dashboard (homepage).
     * Shows task statistics, charts, and recent tasks.
     *
     * @return string
     */
    public function actionIndex()
    {
        $baseQuery = Task::find();

        /* Main task counts */
        $totalTasks      = (clone $baseQuery)->count();
        $doneCount       = (clone $baseQuery)->andWhere(['status' => 'done'])->count();
        $archivedCount   = (clone $baseQuery)->andWhere(['status' => 'archived'])->count();
        $inProgressCount = (clone $baseQuery)->andWhere(['status' => 'in_progress'])->count();

        /* Kanban summary */
        $todoCount   = (clone $baseQuery)->andWhere(['status' => 'pending'])->count();
        $reviewCount = (clone $baseQuery)->andWhere(['status' => 'review'])->count();

        /* Status chart (donut chart data) */
        $statusChart = [
            'todo'        => $todoCount,
            'in_progress' => $inProgressCount,
            'review'      => $reviewCount,
            'done'        => $doneCount,
            'archived'    => $archivedCount,
        ];

        /* Tasks created in the last 7 days */
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

        /* Priority-based statistics */
        $priorityStats = [
            'low'    => (clone $baseQuery)->andWhere(['priority' => 'low'])->count(),
            'medium' => (clone $baseQuery)->andWhere(['priority' => 'medium'])->count(),
            'high'   => (clone $baseQuery)->andWhere(['priority' => 'high'])->count(),
        ];

        /* Recently created tasks */
        $recentTasks = Task::find()
            ->joinWith(['team', 'board'])
            ->orderBy(['task.created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('index', compact(
            'totalTasks',
            'doneCount',
            'archivedCount',
            'inProgressCount',
            'todoCount',
            'reviewCount',
            'statusChart',
            'weeklyStats',
            'priorityStats',
            'recentTasks'
        ));
    }

    /**
     * Login action.
     * Only admin users are allowed in production.
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

            // In production, allow only admin users
            if (!YII_ENV_TEST) {
                if (Yii::$app->user->identity->role != 1) {
                    Yii::$app->user->logout();
                    Yii::$app->session->setFlash('error', 'Access denied.');
                    return $this->redirect(['login']);
                }
            }

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

    /**
     * Displays tasks assigned to the logged-in user.
     * Supports filtering by status, priority, and due date.
     */
    public function actionMytasks()
    {
        $userId = Yii::$app->user->id;

        $status   = Yii::$app->request->get('status', '');
        $priority = Yii::$app->request->get('priority', '');
        $due_date = Yii::$app->request->get('due_date', '');

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

        return $this->render('mytasks', [
            'tasks'    => $tasks,
            'status'   => $status,
            'priority' => $priority,
            'due_date' => $due_date,
        ]);
    }

    /**
     * Displays all tasks with search and filters.
     */
    public function actionAlltask()
    {
        $searchModel  = new \common\models\TaskSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $users        = \common\models\User::find()->all();

        return $this->render('alltask', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'users'        => $users,
        ]);
    }

    /**
     * Displays team page.
     */
    public function actionTeam()
    {
        return $this->render('team');
    }

    /**
     * Resets user password using token.
     *
     * @param string $token
     * @return string|Response
     */
    public function actionResetPassword($token)
    {
        $this->layout = 'blank';

        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if (
            $model->load(Yii::$app->request->post()) &&
            $model->validate() &&
            $model->resetPassword()
        ) {
            Yii::$app->session->setFlash('success', 'New password saved.');
            return $this->redirect(['login']);
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Sends password reset email.
     *
     * @return string|Response
     */
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
