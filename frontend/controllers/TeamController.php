<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Team;
use common\models\TeamMembers;
use common\models\User;
use common\models\BoardMembers;
use common\models\Board;
use common\models\Task;
use yii\filters\AccessControl;

class TeamController extends Controller
{

public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'], // 🔒 only logged-in users
                ],
            ],
        ],
    ];
}

public function beforeAction($action)
{
    if (YII_ENV_TEST) {
        $this->enableCsrfValidation = false;
    }
    return parent::beforeAction($action);
}


    // Show only teams where logged user is member
    public function actionIndex(){
    $teams = TeamMembers::find()->where(['user_id'=>Yii::$app->user->id])->all();
    return $this->render('index', compact('teams'));
}


    // Create team + auto manager
    public function actionCreate()
{
    $model = new Team(); // ✅ Task ❌ hatao

    if ($model->load(Yii::$app->request->post())) {
        $model->created_by = Yii::$app->user->id;

        if ($model->save()) {

            // Auto add creator as manager
            $tm = new TeamMembers();
            $tm->team_id = $model->id;
            $tm->user_id = Yii::$app->user->id;
            $tm->role = 'manager';
            $tm->save(false);

            return $this->redirect(['view', 'id' => $model->id]);
        }
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}

public function actionView($id)
{
    $team = Team::findOne($id);
    if (!$team) {
        throw new \yii\web\NotFoundHttpException("Team not found");
    }

    $currentUser = Yii::$app->user->id;

    // ⭐ CHECK: User must be manager OR member of this team
    $isMember = TeamMembers::find()
        ->where(['team_id' => $id, 'user_id' => $currentUser])
        ->exists();

    if (!$isMember) {
        throw new \yii\web\ForbiddenHttpException("❌ You are not part of this team.");
    }

    // All members of this team
    $members = $team->members;

    // ⭐ SORTING: Manager first → current user second → others
    usort($members, function($a, $b) use ($team, $currentUser) {

        // Manager always first
        if ($a->user_id == $team->created_by) return -1;
        if ($b->user_id == $team->created_by) return 1;

        // Current logged-in user second
        if ($a->user_id == $currentUser) return -1;
        if ($b->user_id == $currentUser) return 1;

        return 0;
    });

    // Fetch ONLY boards belonging to this team
    $boards = Board::find()
        ->where(['team_id' => $team->id])
        ->orderBy(['id' => SORT_DESC])
        ->all();

    // All users (optional for UI)
    $users = User::find()->all();

    return $this->render('view', [
        'team'    => $team,
        'members' => $members,
        'users'   => $users,
        'boards'  => $boards
    ]);
}






    public function actionChangeRole($id,$role){
        $m = TeamMembers::findOne($id);
        $team = Team::findOne($m->team_id);

        if($team->created_by != Yii::$app->user->id)
            throw new \yii\web\ForbiddenHttpException("Not Allowed ❌");

        $m->role = $role;
        $m->save();

        return $this->redirect(['view','id'=>$m->team_id]);
    }

    public function actionMembers()
{
    $members = TeamMembers::find()
                ->where(['user_id' => Yii::$app->user->id])  // user ke teams ke members
                ->all();

    return $this->render('members', compact('members'));
}

public function actionAddMemberPage($id)
{
    $team = Team::findOne($id);
    $users = User::find()->all();

    // All boards show where user has access / ownership
$boards = Board::find()
    ->where(['created_by' => Yii::$app->user->id])
    ->orderBy(['id' => SORT_DESC])
    ->all();


    return $this->render('add-member', compact('team','users','boards'));
}




public function actionAddMemberEmail()
{
    $team_id = Yii::$app->request->post('team_id');
    $email = Yii::$app->request->post('email');
    $board_ids = Yii::$app->request->post('board_id', []);

    $user = User::findOne(['email' => $email]);
    if (!$user) {
        Yii::$app->session->setFlash('error','❌ User not found!');
        return $this->redirect(Yii::$app->request->referrer);
    }

    // 🔷 Add User In Team (If Not Already)
    if (!TeamMembers::findOne(['team_id'=>$team_id,'user_id'=>$user->id])) {
        $tm = new TeamMembers();
        $tm->team_id = $team_id;
        $tm->user_id = $user->id;
        $tm->save(false);
    }

    // 🔥 Assign Boards + auto link board to team
    if(!empty($board_ids)){
        foreach($board_ids as $bid){

            // 1️⃣ If board has no team → assign team
            $board = Board::findOne($bid);
            if($board->team_id == null){
                $board->team_id = $team_id;
                $board->save(false);
            }

            // 2️⃣ Assign User to Board (if not already)
            if (!BoardMembers::findOne(['board_id'=>$bid,'user_id'=>$user->id])) {
                $bm = new BoardMembers();
                $bm->board_id = $bid;
                $bm->user_id  = $user->id;
                $bm->save(false);
            }
        }
    }

    Yii::$app->session->setFlash('success','🎉 Member added + boards linked to team successfully!');
    return $this->redirect(['/team/view','id'=>$team_id]);
}


public function actionDeleteMember($user_id,$team_id){

    // ❗ Prevent self-delete
    if($user_id == Yii::$app->user->id){
        Yii::$app->session->setFlash('error','You cannot remove yourself from the team.');
        return $this->redirect(['/team/view','id'=>$team_id]);
    }

    // 🛑 Prevent deleting team owner
    $team = \common\models\Team::findOne($team_id);
    if($team && $team->created_by == $user_id){
        Yii::$app->session->setFlash('error','Team owner cannot be removed.');
        return $this->redirect(['/team/view','id'=>$team_id]);
    }

    // ✔ Delete allowed members
    \common\models\TeamMembers::deleteAll(['user_id'=>$user_id,'team_id'=>$team_id]);
    
    Yii::$app->session->setFlash('success','Member removed successfully.');
    return $this->redirect(['/team/view','id'=>$team_id]);
}


public function actionUpdateMemberSettings()
{
    $req = Yii::$app->request;

    if(!$req->isPost){
        Yii::$app->session->setFlash('error','Invalid Request');
        return $this->goBack();
    }

    $team_id = $req->post('team_id');
    $user_id = $req->post('user_id');
    $role    = $req->post('role');
    $boards  = $req->post('boards', []);

    // DEBUG CHECK (IMPORTANT)
    if(!$team_id || !$user_id){
        Yii::$app->session->setFlash('error','Team/User ID is not coming from form');
        return $this->goBack();  // Means hidden values modal me nahi ja rahe
    }

    // Fetch team member
    $member = \common\models\TeamMembers::findOne([
        'team_id' => $team_id,
        'user_id' => $user_id
    ]);

    if(!$member){
        Yii::$app->session->setFlash('error','Member record not found');
        return $this->redirect(['/team/view','id'=>$team_id]);
    }

    /** ⬛ ROLE UPDATE */
    $member->role = $role;
    $member->save(false);  // ROLE UPDATED

    /** ⬛ DELETE OLD BOARD Assignments */
    \common\models\BoardMembers::deleteAll(['user_id'=>$user_id]);

    /** ⬛ INSERT NEW BOARDS */
    if(!empty($boards)){
        foreach($boards as $b){
            $m = new \common\models\BoardMembers();
            $m->user_id = $user_id;
            $m->board_id = $b;
            $m->save(false);
        }
    }

    Yii::$app->session->setFlash('success','Member updated successfully');
    return $this->redirect(['/team/view','id'=>$team_id]);
}

    public function actionEditMember($user_id, $team_id){

    // Fetch team member
    $member = \common\models\TeamMembers::findOne([
        'user_id' => $user_id,
        'team_id' => $team_id
    ]);

    // 🔥 Show ALL boards of logged-in MANAGER (not member)
   // Only boards of logged-in user (NOT full website)
    $boards = Board::find()
        ->where(['created_by' => Yii::$app->user->id]) // <<< filtering here
        ->orderBy(['id' => SORT_DESC])
        ->all();

    // Already assigned boards
    $memberBoards = \common\models\BoardMembers::find()
                    ->where(['user_id' => $user_id])
                    ->select('board_id')
                    ->column();

    return $this->render('edit-member',[
        'teamId'       => $team_id,
        'userId'       => $user_id,
        'role'         => $member->role,
        'boards'       => $boards,
        'memberBoards' => $memberBoards
    ]);
}

public function actionDelete($id){
    $team = Team::findOne($id);

    if(!$team){
        Yii::$app->session->setFlash('error','Team not found');
        return $this->redirect(['/team/index']);
    }

    // 🔥 Delete members associated with team first
    \common\models\TeamMembers::deleteAll(['team_id'=>$id]);

    // 🔥 Now delete team
    $team->delete();

    Yii::$app->session->setFlash('success','Team deleted successfully');
    return $this->redirect(['/team/index']);
}


}
