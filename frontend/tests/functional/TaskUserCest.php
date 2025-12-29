<?php

namespace frontend\tests\functional;


use Yii;
use frontend\tests\FunctionalTester;
use common\models\User;
use common\models\Task;
use common\models\Team;
use common\models\TeamMembers;
use common\models\Board;

class TaskUserCest
{
    protected function login(FunctionalTester $I)
    {
        $user = User::findOne(['username' => 'testuser']);

        if (!$user) {
            $user = new User([
                'username' => 'testuser',
                'email' => 'testuser@test.com',
                'status' => User::STATUS_ACTIVE,
            ]);
            $user->setPassword('password');
            $user->generateAuthKey();
            $user->save(false);
        }

        $I->amLoggedInAs($user);
        return $user;
    }

    /**
     * Helper: create task inside team + board
     */
    protected function createTaskWithTeam(User $user, $title = 'Test Task')
    {
        $team = new Team(['name' => 'Test Team']);
        $team->save(false);

        (new TeamMembers([
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]))->save(false);

        $board = new Board([
            'title' => 'Test Board',
            'team_id' => $team->id,
        ]);
        $board->save(false);

        $task = new Task([
            'title' => $title,
            'board_id' => $board->id,
            'created_by' => $user->id,
            'assignee_id' => $user->id,
        ]);
        $task->save(false);

        return $task;
    }



    /** index loads for logged in user */
    public function indexLoads(FunctionalTester $I)
    {
        $this->login($I);

        $I->amOnRoute('/task-user/index');
        $I->seeResponseCodeIs(200);
        $I->see('Tasks');
    }

    /** User sees only team tasks */
    public function indexShowsOnlyTeamTasks(FunctionalTester $I)
    {
        $user = $this->login($I);
        $this->createTaskWithTeam($user, 'My Task');

        $I->amOnRoute('/task-user/index');
        $I->see('My Task');
    }

    /** View task allowed */
    public function viewTaskWorks(FunctionalTester $I)
    {
        $user = $this->login($I);
        $task = $this->createTaskWithTeam($user, 'View Task');

        $I->amOnRoute('/task-user/view', ['id' => $task->id]);
        $I->seeResponseCodeIs(200);
        $I->see('View Task');
    }

    /** Unauthorized user cannot view */
    public function viewTaskForbidden(FunctionalTester $I)
    {
        $user = $this->login($I);

        $otherUser = new User([
            'username' => 'otheruser',
            'email' => 'other@test.com',
            'status' => User::STATUS_ACTIVE,
        ]);
        $otherUser->setPassword('password');
        $otherUser->generateAuthKey();
        $otherUser->save(false);

        $task = $this->createTaskWithTeam($otherUser, 'Forbidden Task');

        $I->amOnRoute('/task-user/view', ['id' => $task->id]);
        $I->seeResponseCodeIs(404);
    }

    /** Delete task */
   public function deleteTaskWorks(FunctionalTester $I)
{
    $user = $this->login($I);
    $task = $this->createTaskWithTeam($user, 'Delete Task');

    $I->amOnRoute('/task-user/delete', ['id' => $task->id]);
    $I->dontSeeRecord(Task::class, ['id' => $task->id]);
}


}
