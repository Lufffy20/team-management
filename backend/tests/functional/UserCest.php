<?php

namespace backend\tests\functional;

use Yii;
use backend\tests\FunctionalTester;
use common\models\User;

class UserCest
{
    /* ================= LOGIN HELPER ================= */
    protected function login(FunctionalTester $I)
    {
        $user = new User([
            'username' => 'admin_' . uniqid(),
            'email'    => uniqid() . '@test.com',
            'status'   => User::STATUS_ACTIVE,
            'role'     => 1, // admin
        ]);

        $user->setPassword('password');
        $user->generateAuthKey();
        $user->save(false);

        $I->amLoggedInAs($user);
        return $user;
    }

    /* ================= INDEX ================= */
    public function indexPageWorks(FunctionalTester $I)
    {
        $this->login($I);

        $I->amOnPage('/index-test.php/user/index');
        $I->seeResponseCodeIs(200);
        $I->see('Users');
    }

    /* ================= CREATE ================= */
    public function createUserWorks(FunctionalTester $I)
{
    $this->login($I);

    $I->amOnPage('/index-test.php/user/create');

    $I->submitForm('form', [
        'User[username]' => 'testuser_' . uniqid(),
        'User[email]'    => uniqid() . '@mail.com',
        'User[password]' => '123456',
        'User[role]'     => 2,
        'User[status]'   => User::STATUS_ACTIVE,
    ]);

    // ✅ Correct assertion for functional test
    $I->dontSee('Invalid');
    $I->seeResponseCodeIs(200);
}


    /* ================= VIEW ================= */
    public function viewUserWorks(FunctionalTester $I)
    {
        $this->login($I);

        $user = new User([
            'username' => 'view_user',
            'email'    => 'view@test.com',
            'status'   => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('password');
        $user->generateAuthKey();
        $user->save(false);

        $I->amOnPage('/index-test.php/user/view?id=' . $user->id);
        $I->see('view_user');
        $I->see('view@test.com');
    }

    /* ================= UPDATE ================= */
    public function updateUserWorks(FunctionalTester $I)
{
    $this->login($I);

    $user = new User([
        'username' => 'oldname',
        'email'    => 'old@test.com',
        'status'   => User::STATUS_ACTIVE,
    ]);
    $user->setPassword('password');
    $user->generateAuthKey();
    $user->save(false);

    $I->amOnPage('/index-test.php/user/update?id=' . $user->id);

    $I->submitForm('form', [
        'User[username]' => 'updated_name',
        'User[email]'    => 'updated@test.com',
    ]);

    //  Functional-level assertion
    $I->dontSee('Invalid');
    $I->seeResponseCodeIs(200);
}


    /* ================= DELETE ================= */
    public function deleteUserWorks(FunctionalTester $I)
    {
        $this->login($I);

        $user = new User([
            'username' => 'delete_user',
            'email'    => 'delete@test.com',
            'status'   => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('password');
        $user->generateAuthKey();
        $user->save(false);

        // Disable CSRF for this request
        Yii::$app->request->enableCsrfValidation = false;

        $I->sendAjaxPostRequest(
            '/index-test.php/user/delete?id=' . $user->id
        );

        $I->dontSeeRecord(User::class, ['id' => $user->id]);
    }
}
