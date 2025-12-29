<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\models\User;
use common\models\Team;

class TeamCest
{
    /* ================= LOGIN ================= */
    protected function login(FunctionalTester $I)
    {
        $user = new User();
        $user->username = 'admin_' . uniqid();
        $user->email    = uniqid() . '@test.com';
        $user->status   = User::STATUS_ACTIVE;
        $user->role     = 1; // ADMIN (required for backend)

        $user->setPassword('password');
        $user->generateAuthKey();

        if (!$user->save(false)) {
            throw new \Exception('Admin user not saved');
        }

        $I->amLoggedInAs($user);
        return $user;
    }

    /* ================= INDEX ================= */
    public function indexPageWorks(FunctionalTester $I)
    {
        $this->login($I);

        $I->amOnPage('/team/index');
        $I->seeResponseCodeIs(200);
        $I->see('Teams');
    }

    /* ================= CREATE ================= */
public function createTeamWorks(FunctionalTester $I)
{
    $this->login($I);

    $I->sendAjaxPostRequest('/team/create', [
        'Team' => [
            'name' => 'Test Team',
        ],
    ]);

    $I->seeRecord(Team::class, [
        'name' => 'Test Team',
    ]);
}




    /* ================= VIEW ================= */
    public function viewTeamWorks(FunctionalTester $I)
    {
        $this->login($I);

        $team = new Team([
            'name' => 'View Team',
            'created_at' => time(),
        ]);
        $team->save(false);

        $I->amOnPage('/team/view?id=' . $team->id);
        $I->see('View Team');
    }

    /* ================= UPDATE ================= */
public function updateTeamWorks(FunctionalTester $I)
{
    $this->login($I);

    $team = new Team([
        'name' => 'Old Team',
    ]);
    $team->save(false);

    $I->sendAjaxPostRequest('/team/update?id=' . $team->id, [
        'Team' => [
            'name' => 'Updated Team',
        ],
    ]);

    $I->seeRecord(Team::class, [
        'id'   => $team->id,
        'name' => 'Updated Team',
    ]);
}




    /* ================= DELETE ================= */
public function deleteTeamWorks(FunctionalTester $I)
{
    $this->login($I);

    $team = new Team([
        'name' => 'Delete Team',
    ]);
    $team->save(false);

    $I->sendAjaxPostRequest('/team/delete?id=' . $team->id);

    $I->dontSeeRecord(Team::class, [
        'id' => $team->id,
    ]);
}

}
