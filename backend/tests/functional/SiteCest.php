<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\models\User;
use common\models\Task;

class SiteCest
{
    /* =========================
       HELPERS
    ========================= */

    protected function createAdmin(FunctionalTester $I)
    {
        $user = new User([
            'username' => 'admin_' . uniqid(),
            'email'    => uniqid() . '@admin.com',
            'role'     => 1,
            'status'   => User::STATUS_ACTIVE,
        ]);

        $user->setPassword('password');
        $user->generateAuthKey();
        $user->save(false);

        return $user;
    }

    protected function createNormalUser()
    {
        $user = new User([
            'username' => 'user_' . uniqid(),
            'email'    => uniqid() . '@user.com',
            'role'     => 2,
            'status'   => User::STATUS_ACTIVE,
        ]);

        $user->setPassword('password');
        $user->generateAuthKey();
        $user->save(false);

        return $user;
    }

    /* =========================
       ACCESS TESTS
    ========================= */

    public function guestRedirectedToLogin(FunctionalTester $I)
    {
        $I->amOnPage('/');
        $I->seeInCurrentUrl('/site/login');
    }

    public function adminCanAccessDashboard(FunctionalTester $I)
    {
        $admin = $this->createAdmin($I);
        $I->amLoggedInAs($admin);

        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
    }

    public function nonAdminCannotAccessDashboard(FunctionalTester $I)
{
    $user = $this->createNormalUser();
    $I->amLoggedInAs($user);

    $I->amOnPage('/');
    $I->seeResponseCodeIs(403); // ✅ correct expectation
}


    /* =========================
       LOGIN
    ========================= */

    public function loginPageWorks(FunctionalTester $I)
    {
        $I->amOnPage('/site/login');
        $I->see('Login');
    }

    public function adminLoginSuccess(FunctionalTester $I)
    {
        $admin = $this->createAdmin($I);

        $I->amOnPage('/site/login');
        $I->fillField('Username', $admin->username);
        $I->fillField('Password', 'password');
        $I->click('Login');

        $I->seeInCurrentUrl('/');
    }

    /* =========================
       LOGOUT
    ========================= */

    public function logoutWorks(FunctionalTester $I)
{
    $admin = $this->createAdmin($I);
    $I->amLoggedInAs($admin);

    // Yii2 FunctionalTester way (no form required)
    $I->sendAjaxPostRequest('/site/logout');

    // after logout user becomes guest
    $I->amOnPage('/');
    $I->seeInCurrentUrl('/site/login');
}



    /* =========================
       MY TASKS
    ========================= */

    public function myTasksPageWorks(FunctionalTester $I)
    {
        $admin = $this->createAdmin($I);
        $I->amLoggedInAs($admin);

        $task = new Task([
            'title'       => 'My Test Task',
            'assigned_to' => $admin->id,
            'status'      => 'pending',
            'priority'    => 'high',
            'created_at'  => time(),
        ]);
        $task->save(false);

        $I->amOnPage('/site/mytasks');
        $I->see('My Test Task');
    }

    /* =========================
       ALL TASKS
    ========================= */

    public function allTasksPageWorks(FunctionalTester $I)
    {
        $admin = $this->createAdmin($I);
        $I->amLoggedInAs($admin);

        $I->amOnPage('/site/alltask');
        $I->seeResponseCodeIs(200);
    }

    /* =========================
       STATIC PAGES
    ========================= */

    public function teamPageLoads(FunctionalTester $I)
    {
        $admin = $this->createAdmin($I);
        $I->amLoggedInAs($admin);

        $I->amOnPage('/site/team');
        $I->seeResponseCodeIs(200);
    }
}
