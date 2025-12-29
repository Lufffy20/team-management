<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\models\User;
use common\models\Task;
use common\models\TeamMembers;
use Faker\Factory as Faker;
use yii\data\ActiveDataProvider;
use yii\web\Response;


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
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
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
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
public function actionIndex()
{
    if (Yii::$app->user->isGuest) {
        return $this->redirect(['site/login']);
    }

    $userId   = Yii::$app->user->id;
    $teamId   = Yii::$app->request->get('team_id'); // 🔥 dropdown se aayega
    $today    = date('Y-m-d');
    $nextWeek = date('Y-m-d', strtotime('+7 days'));

    /* ================= BASE QUERY (MULTI-TEAM + FILTER SAFE) ================= */
    $baseQuery = Task::find()
        ->distinct()
        ->joinWith(['board.team.members'])
        ->where([
            'or',
            ['task.created_by' => $userId],
            ['team_members.user_id' => $userId],
        ]);

    // Team dropdown filter
    if ($teamId) {
        $baseQuery->andWhere(['team.id' => $teamId]);
    }

    /* ================= STATS ================= */
    $totalTasks = (clone $baseQuery)->count();

    $inProgress = (clone $baseQuery)
        ->andWhere(['task.status' => 'in_progress'])
        ->count();

    $dueToday = (clone $baseQuery)
        ->andWhere(['task.due_date' => $today])
        ->count();

    $completedWeekly = (clone $baseQuery)
        ->andWhere(['task.status' => 'done'])
        ->andWhere(['>=', 'task.updated_at', strtotime('-7 days')])
        ->count();

    /* ================= UPCOMING TASKS ================= */
    $myTasks = (clone $baseQuery)
        ->with(['board.team', 'assignee'])
        ->andWhere(['between', 'task.due_date', $today, $nextWeek])
        ->orderBy(['task.due_date' => SORT_ASC])
        ->limit(5)
        ->all();

    /* ================= MINI GRID ================= */
    $dataProvider = new ActiveDataProvider([
        'query' => (clone $baseQuery)
            ->orderBy(['task.created_at' => SORT_DESC])
            ->limit(5),
        'pagination' => false,
    ]);

    /* ================= TEAM OVERVIEW (MEMBER STATS) ================= */
    $teamStats = [];

    $teamMembers = TeamMembers::find()
        ->joinWith('user')
        ->where(['team_members.user_id' => $userId])
        ->all();

    foreach ($teamMembers as $tm) {

        $uid = $tm->user_id;

        $teamStats[$uid]['username'] = $tm->user->username;

        $teamStats[$uid]['openTasks'] = Task::find()
            ->distinct()
            ->where(['assigned_to' => $uid, 'status' => 'open'])
            ->count();

        $teamStats[$uid]['inProgress'] = Task::find()
            ->distinct()
            ->where(['assigned_to' => $uid, 'status' => 'in_progress'])
            ->count();

        $teamStats[$uid]['completedThisWeek'] = Task::find()
            ->distinct()
            ->where(['assigned_to' => $uid, 'status' => 'done'])
            ->andWhere(['>=', 'updated_at', strtotime('-7 days')])
            ->count();

        $teamStats[$uid]['upcoming'] = Task::find()
            ->distinct()
            ->where(['assigned_to' => $uid])
            ->andWhere(['between', 'due_date', $today, $nextWeek])
            ->count();
    }

    /* ================= RECENT ACTIVITY ================= */
    $recent = (clone $baseQuery)
        ->with(['updatedBy', 'createdBy', 'board.team'])
        ->orderBy(['task.updated_at' => SORT_DESC])
        ->limit(5)
        ->all();

    /* ================= TEAMS FOR DROPDOWN ================= */
    $teams = \common\models\Team::find()
        ->innerJoinWith('members')
        ->where(['team_members.user_id' => $userId])
        ->all();

    return $this->render('index', compact(
        'totalTasks',
        'inProgress',
        'dueToday',
        'completedWeekly',
        'myTasks',
        'teamStats',
        'recent',
        'dataProvider',
        'teams'
    ));
}

public function actionStats()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    if (Yii::$app->user->isGuest) {
        return [];
    }

    $userId = Yii::$app->user->id;
    $teamId = Yii::$app->request->get('team_id');

    /* ================= USER TEAM IDS ================= */
    $userTeamIds = TeamMembers::find()
        ->select('team_id')
        ->where(['user_id' => $userId])
        ->column();

    if (empty($userTeamIds)) {
        return [
            'status' => ['todo' => 0, 'in_progress' => 0, 'done' => 0],
            'members' => [],
            'timeline' => ['days' => [], 'created' => [], 'completed' => []],
        ];
    }

    /* ================= VALIDATE TEAM FILTER ================= */
    if ($teamId && !in_array($teamId, $userTeamIds)) {
        return [];
    }

    $filterTeamIds = $teamId ? [$teamId] : $userTeamIds;

    /* ================= BASE QUERY ================= */
    $baseQuery = Task::find()
        ->alias('t')
        ->joinWith(['board b'])
        ->where(['b.team_id' => $filterTeamIds])
        ->andWhere([
            'or',
            ['t.created_by' => $userId],
            ['t.assignee_id' => $userId],
        ]);

    /* ================= STATUS PIE ================= */
    $status = [
        'todo' => (clone $baseQuery)->andWhere(['t.status' => 'todo'])->count(),
        'in_progress' => (clone $baseQuery)->andWhere(['t.status' => 'in_progress'])->count(),
        'done' => (clone $baseQuery)->andWhere(['t.status' => 'done'])->count(),
    ];

    /* ================= TEAM WORKLOAD ================= */
    $members = [];

    $teamMembers = TeamMembers::find()
        ->alias('tm')
        ->joinWith('user u')
        ->where(['tm.team_id' => $filterTeamIds])
        ->groupBy('tm.user_id')
        ->all();

    foreach ($teamMembers as $tm) {

        $taskCount = Task::find()
            ->alias('t')
            ->joinWith('board b')
            ->where([
                't.assignee_id' => $tm->user_id,
                'b.team_id' => $filterTeamIds,
            ])
            ->andWhere(['in', 't.status', ['todo', 'in_progress']])
            ->count();

        $members[] = [
            'name'  => $tm->user->username,
            'tasks' => (int)$taskCount,
        ];
    }

    /* ================= 7-DAY TIMELINE ================= */
    $days = [];
    $created = [];
    $completed = [];

    for ($i = 6; $i >= 0; $i--) {

        $date = date('Y-m-d', strtotime("-$i days"));
        $days[] = date('d M', strtotime($date));

        $created[] = (clone $baseQuery)
            ->andWhere(['DATE(FROM_UNIXTIME(t.created_at))' => $date])
            ->count();

        $completed[] = (clone $baseQuery)
            ->andWhere(['t.status' => 'done'])
            ->andWhere(['DATE(FROM_UNIXTIME(t.updated_at))' => $date])
            ->count();
    }

    /* ================= RESPONSE ================= */
    return [
        'status' => $status,
        'members' => $members,
        'timeline' => [
            'days' => $days,
            'created' => $created,
            'completed' => $completed,
        ],
    ];
}


    
    public function actionAbout()
{
    return $this->render('about');
}


    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {

        $this->layout = 'blank';
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {

    if (Yii::$app->user->identity->role == 0) {
        return $this->goHome();  
    }

    Yii::$app->user->logout();
    Yii::$app->session->setFlash('error', 'Access denied.');
    return $this->redirect(['login']);
}

 $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    


    /**
     * Logs out the current user.
     *
     * @return mixed
     */
     public function actionLogout()
{
    Yii::$app->user->logout();
    return $this->goHome();
}


    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
{
    $this->layout = 'blank';
    $model = new SignupForm();

    if ($model->load(Yii::$app->request->post()) && $model->signup()) {

        // ⭐ TEST EXPECTS THIS EXACT MESSAGE
        Yii::$app->session->setFlash('success', 'Thank you for registration.');

        return $this->refresh();  // Test also expects same-page refresh
    }

    return $this->render('signup', [
        'model' => $model,
    ]);
}

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $this->layout = 'blank';
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->redirect(['site/login']);
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
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

            return $this->redirect(['site/login']);
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
   public function actionVerifyEmail($token)
{
    // 🔥 Test Case: Empty Token
    if (empty($token)) {
        throw new BadRequestHttpException('Verify email token cannot be blank.');
    }

    try {
        $model = new VerifyEmailForm($token);
    } catch (\Exception $e) {
        throw new BadRequestHttpException('Wrong verify email token.');
    }

    if ($model->verifyEmail()) {

        $user = $model->getUser();

        /** CASE 1 — Email change */
        if (!empty($user->pending_email)) {
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
                        'Failed to update Stripe customer after email verification in SiteController: ' . $e->getMessage(),
                        __METHOD__
                    );
                    // Don't throw the exception as it shouldn't prevent the email verification
                }
            }

            Yii::$app->session->setFlash('success', 'Your email has been updated successfully!');
            return $this->redirect(['/managment/profile']);
        }

        /** CASE 2 — Normal Signup Verification */
        Yii::$app->session->setFlash('success', 'Your email has been verified successfully.');
        Yii::$app->session->setFlash('info', 'Congratulations!');
        return $this->redirect(['/site/login']);
    }

    //  For invalid/expired/already-used
    throw new BadRequestHttpException('Wrong verify email token.');
}



    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
{
    $model = new ResendVerificationEmailForm();
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        if ($model->sendEmail()) {
            Yii::$app->session->setFlash('success', 'Please check your email inbox');
            return $this->goHome();
        }
        Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
    }

    return $this->render('resendVerificationEmail', [
        'model' => $model
    ]);
}

}
