<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\Team;
use common\models\TeamMembers;
use common\models\User;
use common\models\BoardMembers;
use common\models\Board;
use common\models\Task;

/**
 * TeamController
 *
 * Handles team creation, viewing, member management,
 * role updates, board assignments and deletion.
 */
class TeamController extends Controller
{
    /**
     * Access control
     * Only logged-in users can access team features
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // only authenticated users
                    ],
                ],
            ],
        ];
    }

    /**
     * Disable CSRF in test environment
     */
    public function beforeAction($action)
    {
        if (YII_ENV_TEST) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Team list
     * Shows only teams where logged-in user is a member
     */
    public function actionIndex()
    {
        $teams = TeamMembers::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->all();

        return $this->render('index', compact('teams'));
    }

    /**
     * Create new team
     * Automatically adds creator as team manager
     */
    public function actionCreate()
    {
        $model = new Team();

        if ($model->load(Yii::$app->request->post())) {

            // Set creator
            $model->created_by = Yii::$app->user->id;

            if ($model->save()) {

                // Auto add creator as manager
                $tm = new TeamMembers();
                $tm->team_id = $model->id;
                $tm->user_id = Yii::$app->user->id;
                $tm->role    = 'manager';
                $tm->save(false);

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * View single team
     * Shows members, boards and users
     */
    public function actionView($id)
    {
        $team = Team::findOne($id);
        if (!$team) {
            throw new \yii\web\NotFoundHttpException("Team not found");
        }

        $currentUser = Yii::$app->user->id;

        // Check user is member of team
        $isMember = TeamMembers::find()
            ->where(['team_id' => $id, 'user_id' => $currentUser])
            ->exists();

        if (!$isMember) {
            throw new \yii\web\ForbiddenHttpException(
                "You are not part of this team."
            );
        }

        // Fetch all team members
        $members = $team->members;

        /**
         * Sort members:
         * 1) Team owner first
         * 2) Logged-in user second
         * 3) Others after
         */
        usort($members, function ($a, $b) use ($team, $currentUser) {

            // Team owner always first
            if ($a->user_id == $team->created_by) return -1;
            if ($b->user_id == $team->created_by) return 1;

            // Logged-in user second
            if ($a->user_id == $currentUser) return -1;
            if ($b->user_id == $currentUser) return 1;

            return 0;
        });

        // Fetch only boards belonging to this team
        $boards = Board::find()
            ->where(['team_id' => $team->id])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        // All users (used in add member UI)
        $users = User::find()->all();

        return $this->render('view', [
            'team'    => $team,
            'members' => $members,
            'users'   => $users,
            'boards'  => $boards,
        ]);
    }

    /**
     * Change role of a team member
     * Only team owner allowed
     */
    public function actionChangeRole($id, $role)
    {
        $m    = TeamMembers::findOne($id);
        $team = Team::findOne($m->team_id);

        // Only team creator can change roles
        if ($team->created_by != Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException("Not Allowed");
        }

        $m->role = $role;
        $m->save(false);

        return $this->redirect(['view', 'id' => $m->team_id]);
    }

    /**
     * Show teams where user is member (members page)
     */
    public function actionMembers()
    {
        $members = TeamMembers::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->all();

        return $this->render('members', compact('members'));
    }

    /**
     * Add member page
     */
    public function actionAddMemberPage($id)
    {
        $team  = Team::findOne($id);
        $users = User::find()->all();

        // Show only boards created by logged-in user
        $boards = Board::find()
            ->where(['created_by' => Yii::$app->user->id])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render(
            'add-member',
            compact('team', 'users', 'boards')
        );
    }

    /**
     * Add member via email
     * Optionally assign boards and link board to team
     */
    public function actionAddMemberEmail()
    {
        $team_id   = Yii::$app->request->post('team_id');
        $email     = Yii::$app->request->post('email');
        $board_ids = Yii::$app->request->post('board_id', []);

        // Find user by email
        $user = User::findOne(['email' => $email]);
        if (!$user) {
            Yii::$app->session->setFlash('error', 'User not found');
            return $this->redirect(Yii::$app->request->referrer);
        }

        // Add user to team if not already member
        if (!TeamMembers::findOne([
            'team_id' => $team_id,
            'user_id' => $user->id
        ])) {
            $tm = new TeamMembers();
            $tm->team_id = $team_id;
            $tm->user_id = $user->id;
            $tm->save(false);
        }

        // Assign boards and link board to team
        if (!empty($board_ids)) {
            foreach ($board_ids as $bid) {

                // Assign team to board if missing
                $board = Board::findOne($bid);
                if ($board->team_id == null) {
                    $board->team_id = $team_id;
                    $board->save(false);
                }

                // Assign user to board
                if (!BoardMembers::findOne([
                    'board_id' => $bid,
                    'user_id'  => $user->id
                ])) {
                    $bm = new BoardMembers();
                    $bm->board_id = $bid;
                    $bm->user_id  = $user->id;
                    $bm->save(false);
                }
            }
        }

        Yii::$app->session->setFlash(
            'success',
            'Member added and boards linked successfully'
        );

        return $this->redirect(['/team/view', 'id' => $team_id]);
    }

    /**
     * Remove member from team
     */
    public function actionDeleteMember($user_id, $team_id)
    {
        // Prevent self-removal
        if ($user_id == Yii::$app->user->id) {
            Yii::$app->session->setFlash(
                'error',
                'You cannot remove yourself from the team.'
            );
            return $this->redirect(['/team/view', 'id' => $team_id]);
        }

        // Prevent removing team owner
        $team = Team::findOne($team_id);
        if ($team && $team->created_by == $user_id) {
            Yii::$app->session->setFlash(
                'error',
                'Team owner cannot be removed.'
            );
            return $this->redirect(['/team/view', 'id' => $team_id]);
        }

        // Delete member record
        TeamMembers::deleteAll([
            'user_id' => $user_id,
            'team_id' => $team_id
        ]);

        Yii::$app->session->setFlash(
            'success',
            'Member removed successfully.'
        );

        return $this->redirect(['/team/view', 'id' => $team_id]);
    }

    /**
     * Update member role and board access
     */
    public function actionUpdateMemberSettings()
    {
        $req = Yii::$app->request;

        if (!$req->isPost) {
            Yii::$app->session->setFlash('error', 'Invalid Request');
            return $this->goBack();
        }

        $team_id = $req->post('team_id');
        $user_id = $req->post('user_id');
        $role    = $req->post('role');
        $boards  = $req->post('boards', []);

        if (!$team_id || !$user_id) {
            Yii::$app->session->setFlash(
                'error',
                'Team/User ID missing'
            );
            return $this->goBack();
        }

        // Fetch team member record
        $member = TeamMembers::findOne([
            'team_id' => $team_id,
            'user_id' => $user_id
        ]);

        if (!$member) {
            Yii::$app->session->setFlash(
                'error',
                'Member record not found'
            );
            return $this->redirect(['/team/view', 'id' => $team_id]);
        }

        // Update role
        $member->role = $role;
        $member->save(false);

        // Remove old board assignments
        BoardMembers::deleteAll(['user_id' => $user_id]);

        // Assign new boards
        if (!empty($boards)) {
            foreach ($boards as $b) {
                $m = new BoardMembers();
                $m->user_id  = $user_id;
                $m->board_id = $b;
                $m->save(false);
            }
        }

        Yii::$app->session->setFlash(
            'success',
            'Member updated successfully'
        );

        return $this->redirect(['/team/view', 'id' => $team_id]);
    }

    /**
     * Edit member page
     */
    public function actionEditMember($user_id, $team_id)
    {
        // Fetch team member
        $member = TeamMembers::findOne([
            'user_id' => $user_id,
            'team_id' => $team_id
        ]);

        // Only boards created by logged-in manager
        $boards = Board::find()
            ->where(['created_by' => Yii::$app->user->id])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        // Already assigned boards
        $memberBoards = BoardMembers::find()
            ->where(['user_id' => $user_id])
            ->select('board_id')
            ->column();

        return $this->render('edit-member', [
            'teamId'       => $team_id,
            'userId'       => $user_id,
            'role'         => $member->role,
            'boards'       => $boards,
            'memberBoards' => $memberBoards,
        ]);
    }

    /**
     * Delete team completely
     */
   public function actionDelete($id)
{
    $team = Team::findOne($id);

    if (!$team) {
        Yii::$app->session->setFlash('error', 'Team not found');
        return $this->redirect(['/team/index']);
    }

    //  CURRENT USER ROLE CHECK
    $role = strtolower(
        TeamMembers::find()
            ->where([
                'team_id' => $id,
                'user_id' => Yii::$app->user->id
            ])
            ->select('role')
            ->scalar()
    );

    //  NOT ADMIN OR MANAGER → BLOCK
    if (!in_array($role, ['admin', 'manager'])) {
        throw new \yii\web\ForbiddenHttpException(
            'You are not allowed to delete this team.'
        );
    }

    //  DELETE ALL TEAM MEMBERS
    TeamMembers::deleteAll(['team_id' => $id]);

    //  DELETE TEAM
    $team->delete();

    Yii::$app->session->setFlash(
        'success',
        'Team deleted successfully'
    );

    return $this->redirect(['/team/index']);
}

}
