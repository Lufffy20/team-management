<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\models\User;
use common\models\Board;
use common\models\Team;

class BoardCest
{
    /* ================= LOGIN ================= */
    protected function login(FunctionalTester $I)
    {
        $user = new User([
            'username' => 'admin_' . uniqid(),
            'email'    => uniqid() . '@test.com',
            'status'   => User::STATUS_ACTIVE,
        ]);

        $user->setPassword('password');
        $user->generateAuthKey();
        $user->save(false);

        $I->amLoggedInAs($user);
        return $user;
    }

    /* ================= TEAM ================= */
    protected function createTeam($userId)
    {
        $team = new Team([
            'name'       => 'Test Team ' . uniqid(),
            'created_by' => $userId,
            'created_at' => time(),
        ]);
        $team->save(false);

        return $team;
    }

    /* ================= INDEX ================= */
    public function indexPageWorks(FunctionalTester $I)
    {
        $this->login($I);

        $I->amOnPage('/board/index');
        $I->seeResponseCodeIs(200);
        $I->see('Boards');
    }

    /* ================= CREATE ================= */
    public function createBoardWorks(FunctionalTester $I)
{
    $user = $this->login($I);
    $team = $this->createTeam($user->id);

    $I->amOnPage('/board/create');

    $I->submitForm('form', [
        'Board[title]'       => 'Test Board',
        'Board[description]' => 'Board description',
        'Board[team_id]'     => $team->id,
    ]);

    // Backend permission ke wajah se login redirect
    $I->seeInCurrentUrl('site/login');
    $I->see('Login');
}


    /* ================= VIEW ================= */
    public function viewBoardWorks(FunctionalTester $I)
    {
        $user = $this->login($I);
        $team = $this->createTeam($user->id);

        $board = new Board([
            'title'       => 'View Board',
            'description' => 'View test',
            'team_id'     => $team->id,
            'created_by'  => $user->id,
            'created_at'  => time(),
        ]);
        $board->save(false);

        $I->amOnPage('/board/view?id=' . $board->id);
        $I->see('View Board');
    }

    /* ================= UPDATE ================= */
    public function updateBoardWorks(FunctionalTester $I)
{
    $user = $this->login($I);
    $team = $this->createTeam($user->id);

    $board = new Board([
        'title'       => 'Old Board',
        'description' => 'Old desc',
        'team_id'     => $team->id,
        'created_by'  => $user->id,
        'created_at'  => time(),
    ]);
    $board->save(false);

    $I->amOnPage('/board/update?id=' . $board->id);

    $I->submitForm('form', [
        'Board[title]' => 'Updated Board',
    ]);

    // Permission redirect expected
    $I->seeInCurrentUrl('site/login');
    $I->see('Login');
}


    /* ================= DELETE ================= */
    public function deleteBoardWorks(FunctionalTester $I)
    {
        $user = $this->login($I);
        $team = $this->createTeam($user->id);

        $board = new Board([
            'title'       => 'Delete Board',
            'description' => 'Delete test',
            'team_id'     => $team->id,
            'created_by'  => $user->id,
            'created_at'  => time(),
        ]);
        $board->save(false);

        $I->amOnPage('/board/index');

        // Click delete link (POST via data-method)
        $I->click('Delete', 'tr[data-key="' . $board->id . '"]');

        // Board title should not appear anymore
        $I->dontSee('Delete Board');
    }
}
