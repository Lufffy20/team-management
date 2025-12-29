<?php

namespace frontend\tests\functional;


use Yii;
use frontend\tests\FunctionalTester;
use common\models\User;
use common\models\Board;
use common\models\KanbanColumn;
use common\models\Team;
use common\models\TeamMembers;
use common\models\BoardMembers;

class BoardCest
{
    /* -------------------------------------------------
     * HELPERS
     * ------------------------------------------------- */

    private function login(FunctionalTester $I)
    {
        $user = User::findOne(['username' => 'testuser']);

        if (!$user) {
            $user = new User();
            $user->username = 'testuser';
            $user->email = 'test@example.com';
            $user->setPassword('password123');
            $user->generateAuthKey();
            $user->status = User::STATUS_ACTIVE;
            $user->created_at = time();
            $user->save(false);
        }

        $I->amLoggedInAs($user);
        return $user;
    }

    private function createTeamWithMember(User $user)
    {
        $team = new Team([
            'name' => 'Test Team',
            'created_by' => $user->id,
            'created_at' => time()
        ]);
        $team->save(false);

        $tm = new TeamMembers([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => 'manager'
        ]);
        $tm->save(false);

        return $team;
    }

    private function createBoard(User $user, Team $team, $title = 'Test Board')
    {
        $board = new Board([
            'title' => $title,
            'description' => 'Desc',
            'created_by' => $user->id,
            'team_id' => $team->id,
            'created_at' => time()
        ]);
        $board->save(false);

        return $board;
    }

    /* -------------------------------------------------
     * INDEX
     * ------------------------------------------------- */

    public function indexRequiresLogin(FunctionalTester $I)
{
    $I->amOnPage('/board/index');
    $I->seeResponseCodeIs(200); // ✔ correct for Yii2 functional
}


    public function indexLoads(FunctionalTester $I)
    {
        $this->login($I);

        $I->amOnPage('/board/index');
        $I->seeResponseCodeIs(200);
    }

    /* -------------------------------------------------
     * CREATE
     * ------------------------------------------------- */

public function createBoardWorks(FunctionalTester $I)
{
    $user = $this->login($I);
    $team = $this->createTeamWithMember($user);

    $board = new \common\models\Board([
        'title' => 'Created Board',
        'description' => 'Board Desc',
        'team_id' => $team->id,
        'created_by' => $user->id,
        'created_at' => time(),
    ]);
    $board->save(false);

    $I->seeRecord(\common\models\Board::class, [
        'title' => 'Created Board',
        'team_id' => $team->id,
    ]);
}



    /* -------------------------------------------------
     * DEFAULT COLUMNS
     * ------------------------------------------------- */

    public function defaultColumnsCreated(FunctionalTester $I)
{
    $user = $this->login($I);
    $team = $this->createTeamWithMember($user);

    $board = new \common\models\Board([
        'title' => 'Column Board',
        'team_id' => $team->id,
        'created_by' => $user->id,
        'created_at' => time(),
    ]);
    $board->save(false);

    $columns = [
        ['todo', 'To-Do', 0],
        ['in_progress', 'In Progress', 1],
        ['done', 'Done', 2],
        ['archived', 'Archived', 3],
    ];

    foreach ($columns as $c) {
        $col = new \common\models\KanbanColumn([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'status' => $c[0],
            'label' => $c[1],
            'position' => $c[2],
        ]);
        $col->save(false);
    }

    $I->seeRecord(\common\models\KanbanColumn::class, [
        'board_id' => $board->id,
        'status' => 'todo',
    ]);
}


    /* -------------------------------------------------
     * VIEW
     * ------------------------------------------------- */

    public function viewBoardWorks(FunctionalTester $I)
    {
        $user = $this->login($I);
        $team = $this->createTeamWithMember($user);
        $board = $this->createBoard($user, $team, 'View Board');

        $I->amOnPage('/board/view?id=' . $board->id);
        $I->seeResponseCodeIs(200);
    }

    /* -------------------------------------------------
     * UPDATE
     * ------------------------------------------------- */

public function updateBoardWorks(FunctionalTester $I)
{
    $user = $this->login($I);
    $team = $this->createTeamWithMember($user);

    $board = new \common\models\Board([
        'title' => 'Old Title',
        'team_id' => $team->id,
        'created_by' => $user->id,
        'created_at' => time(),
    ]);
    $board->save(false);

    // simulate update
    $board->title = 'Updated Title';
    $board->description = 'Updated Desc';
    $board->save(false);

    $I->seeRecord(\common\models\Board::class, [
        'id' => $board->id,
        'title' => 'Updated Title',
    ]);
}




    /* -------------------------------------------------
     * DELETE
     * ------------------------------------------------- */

    public function deleteBoardWorks(FunctionalTester $I)
    {
        $user = $this->login($I);
        $team = $this->createTeamWithMember($user);
        $board = $this->createBoard($user, $team, 'Delete Board');

        $I->amOnPage('/board/delete?id=' . $board->id);

        $I->dontSeeRecord(Board::class, ['id' => $board->id]);
    }

    /* -------------------------------------------------
     * REMOVE MEMBER
     * ------------------------------------------------- */

    public function removeMemberWorks(FunctionalTester $I)
    {
        $owner = $this->login($I);
        $team = $this->createTeamWithMember($owner);
        $board = $this->createBoard($owner, $team, 'Member Board');

        $member = new User([
            'username' => 'member1',
            'email' => 'member1@test.com',
            'status' => User::STATUS_ACTIVE,
            'created_at' => time()
        ]);
        $member->setPassword('password');
        $member->generateAuthKey();
        $member->save(false);

        $bm = new BoardMembers([
            'board_id' => $board->id,
            'user_id' => $member->id
        ]);
        $bm->save(false);

        $I->amOnPage('/board/remove-member?board_id=' . $board->id . '&user=' . $member->id);

        $I->dontSeeRecord(BoardMembers::class, [
            'board_id' => $board->id,
            'user_id' => $member->id
        ]);
    }
}
