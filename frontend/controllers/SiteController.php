<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use common\models\LoginForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\VerifyEmailForm;
use frontend\models\ResendVerificationEmailForm;
use common\models\User;
use common\models\Task;
use common\models\TeamMembers;
use yii\web\Response;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;

/**
 * SiteController
 *
 * Handles frontend dashboard and authentication related pages.
 */
class SiteController extends Controller
{
    /**
     * Access control and HTTP verb rules.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    // Guest users can access signup
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    // Logged-in users can logout
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            // Restrict logout to POST request
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
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Dashboard (Home Page)
     *
     * Shows:
     * - Task statistics
     * - Upcoming tasks
     * - Team-wise task overview
     * - Recent activity
     * - Team filter dropdown
     */
    public function actionIndex()
    {
        /* ================= AUTH CHECK ================= */
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        /* ================= BASIC VARIABLES ================= */
        $userId   = Yii::$app->user->id;                 // logged-in user
        $teamId   = Yii::$app->request->get('team_id');  // selected team (dropdown)
        $today    = date('Y-m-d');                        // today date
        $nextWeek = date('Y-m-d', strtotime('+7 days')); // next 7 days range

        /* ================= BASE QUERY =================
         * Fetch tasks where:
         * - user is creator OR
         * - user is member of the task's team
         * Using joins to support multi-team visibility
         */
        $baseQuery = Task::find()
            ->distinct()
            ->joinWith(['board.team.members'])
            ->where([
                'or',
                ['task.created_by' => $userId],
                ['team_members.user_id' => $userId],
            ]);

        // Apply team filter if selected
        if ($teamId) {
            $baseQuery->andWhere(['team.id' => $teamId]);
        }

        /* ================= TASK STATISTICS ================= */
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

        /* ================= UPCOMING TASKS =================
         * Tasks due within the next 7 days
         */
        $myTasks = (clone $baseQuery)
            ->with(['board.team', 'assignee'])
            ->andWhere(['between', 'task.due_date', $today, $nextWeek])
            ->orderBy(['task.due_date' => SORT_ASC])
            ->limit(5)
            ->all();

        /* ================= MINI TASK GRID =================
         * Recent tasks (no pagination)
         */
        $dataProvider = new ActiveDataProvider([
            'query' => (clone $baseQuery)
                ->orderBy(['task.created_at' => SORT_DESC])
                ->limit(5),
            'pagination' => false,
        ]);

        /* ================= TEAM OVERVIEW =================
         * Per-member task statistics
         */
        $teamStats = [];

        $teamMembers = TeamMembers::find()
            ->joinWith('user')
            ->where(['team_members.user_id' => $userId])
            ->all();

        foreach ($teamMembers as $tm) {

            $uid = $tm->user_id;

            // Member name
            $teamStats[$uid]['username'] = $tm->user->username;

            // Open tasks
            $teamStats[$uid]['openTasks'] = Task::find()
                ->distinct()
                ->where(['assigned_to' => $uid, 'status' => 'open'])
                ->count();

            // In-progress tasks
            $teamStats[$uid]['inProgress'] = Task::find()
                ->distinct()
                ->where(['assigned_to' => $uid, 'status' => 'in_progress'])
                ->count();

            // Completed in last 7 days
            $teamStats[$uid]['completedThisWeek'] = Task::find()
                ->distinct()
                ->where(['assigned_to' => $uid, 'status' => 'done'])
                ->andWhere(['>=', 'updated_at', strtotime('-7 days')])
                ->count();

            // Upcoming tasks
            $teamStats[$uid]['upcoming'] = Task::find()
                ->distinct()
                ->where(['assigned_to' => $uid])
                ->andWhere(['between', 'due_date', $today, $nextWeek])
                ->count();
        }

        /* ================= RECENT ACTIVITY =================
         * Recently updated tasks
         */
        $recent = (clone $baseQuery)
            ->with(['updatedBy', 'createdBy', 'board.team'])
            ->orderBy(['task.updated_at' => SORT_DESC])
            ->limit(5)
            ->all();

        /* ================= TEAM DROPDOWN =================
         * Teams where user is a member
         */
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


    /**
     * Dashboard statistics API (AJAX).
     *
     * Returns:
     * - Status counts (todo / in_progress / done)
     * - Team workload per member
     * - 7-day created vs completed timeline
     */
    public function actionStats()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Guest users get empty response
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

        // User is not part of any team
        if (empty($userTeamIds)) {
            return [
                'status' => ['todo' => 0, 'in_progress' => 0, 'done' => 0],
                'members' => [],
                'timeline' => ['days' => [], 'created' => [], 'completed' => []],
            ];
        }

        /* ================= TEAM FILTER VALIDATION ================= */
        if ($teamId && !in_array($teamId, $userTeamIds)) {
            return [];
        }

        $filterTeamIds = $teamId ? [$teamId] : $userTeamIds;

        /* ================= BASE TASK QUERY =================
         * Tasks:
         * - Belonging to selected teams
         * - Created by or assigned to logged-in user
         */
        $baseQuery = Task::find()
            ->alias('t')
            ->joinWith(['board b'])
            ->where(['b.team_id' => $filterTeamIds])
            ->andWhere([
                'or',
                ['t.created_by' => $userId],
                ['t.assignee_id' => $userId],
            ]);

        /* ================= STATUS PIE DATA ================= */
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
                'tasks' => (int) $taskCount,
            ];
        }

        /* ================= 7-DAY TIMELINE ================= */
        $days = [];
        $created = [];
        $completed = [];

        for ($i = 6; $i >= 0; $i--) {

            $date = date('Y-m-d', strtotime("-$i days"));
            $days[] = date('d M', strtotime($date));

            // Tasks created on this day
            $created[] = (clone $baseQuery)
                ->andWhere(['DATE(FROM_UNIXTIME(t.created_at))' => $date])
                ->count();

            // Tasks completed on this day
            $completed[] = (clone $baseQuery)
                ->andWhere(['t.status' => 'done'])
                ->andWhere(['DATE(FROM_UNIXTIME(t.updated_at))' => $date])
                ->count();
        }

        /* ================= FINAL JSON RESPONSE ================= */
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

    /**
     * Static About page.
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Login action.
     * Allows only role = 0 users.
     */
    public function actionLogin()
    {
        $this->layout = 'blank';

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {

            // Allow only normal users (role = 0)
            if (Yii::$app->user->identity->role == 0) {
                return $this->goHome();
            }

            // Otherwise logout immediately
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
     * Logout current user.
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * Contact page.
     */
    public function actionContact()
    {
        $model = new ContactForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash(
                    'success',
                    'Thank you for contacting us. We will respond as soon as possible.'
                );
            } else {
                Yii::$app->session->setFlash(
                    'error',
                    'There was an error sending your message.'
                );
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Signup action.
     */
    public function actionSignup()
    {
        $this->layout = 'blank';
        $model = new SignupForm();

        if ($model->load(Yii::$app->request->post()) && $model->signup()) {

            // Required exact message for tests
            Yii::$app->session->setFlash(
                'success',
                'Thank you for registration.'
            );

            return $this->refresh();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Request password reset.
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
                return $this->redirect(['site/login']);
            }

            Yii::$app->session->setFlash(
                'error',
                'Unable to reset password for the provided email address.'
            );
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Reset password using token.
     */
    public function actionResetPassword($token)
    {
        $this->layout = 'blank';

        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) &&
            $model->validate() &&
            $model->resetPassword()
        ) {
            Yii::$app->session->setFlash('success', 'New password saved.');
            return $this->redirect(['site/login']);
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address.
     */
    public function actionVerifyEmail($token)
    {
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

            // Case: email change verification
            if (!empty($user->pending_email)) {

                $user->email = $user->pending_email;
                $user->pending_email = null;
                $user->verification_token = null;
                $user->save(false);

                Yii::$app->session->setFlash(
                    'success',
                    'Your email has been updated successfully!'
                );

                return $this->redirect(['/managment/profile']);
            }

            // Case: signup verification
            Yii::$app->session->setFlash(
                'success',
                'Your email has been verified successfully.'
            );
            Yii::$app->session->setFlash('info', 'Congratulations!');

            return $this->redirect(['/site/login']);
        }

        throw new BadRequestHttpException('Wrong verify email token.');
    }

    /**
     * Resend verification email.
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if ($model->sendEmail()) {
                Yii::$app->session->setFlash(
                    'success',
                    'Please check your email inbox'
                );
                return $this->goHome();
            }

            Yii::$app->session->setFlash(
                'error',
                'Unable to resend verification email.'
            );
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model,
        ]);
    }
}