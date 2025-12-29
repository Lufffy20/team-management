<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;
use common\models\User;
use common\models\Task;
use common\models\Team;
use common\models\Board;
use common\models\TeamMembers;

class ActivityCest
{
    public function _before(FunctionalTester $I)
    {
        // Ensure clean session
        $I->amOnPage('/site/logout');
    }

    /** 🔒 Guest should be redirected to login */
    public function activityRequiresLogin(FunctionalTester $I)
{
    $I->amOnPage('/site/logout');
    $I->amOnPage('/activity/index');

    $I->seeInCurrentUrl('/site/login');
    $I->see('Login'); // ya "Sign in"
}


    /** ✅ Logged-in user can see activity page */
    public function activityLoadsForLoggedInUser(FunctionalTester $I)
{
    $time = time();

    $userId = $I->haveRecord(\common\models\User::class, [
        'username' => 'testuser_' . $time,   // 👈 unique
        'email' => 'test' . $time . '@example.com',
        'status' => \common\models\User::STATUS_ACTIVE,
        'created_at' => $time,
        'updated_at' => $time,
    ]);

    $I->amLoggedInAs(\common\models\User::findOne($userId));
    $I->amOnPage('/activity/index');

    $I->seeResponseCodeIs(200);
}


    /** 📋 User sees tasks from own teams */
    public function activityShowsTeamTasks(FunctionalTester $I)
{
    $time = time();

    // 1️⃣ User
    $userId = $I->haveRecord(\common\models\User::class, [
        'username' => 'member1',
        'email' => 'member1@example.com',
        'status' => \common\models\User::STATUS_ACTIVE,
        'created_at' => $time,
        'updated_at' => $time,
    ]);

    // 2️⃣ Login BEFORE creating data (important for created_by)
    $I->amLoggedInAs(\common\models\User::findOne($userId));

    // 3️⃣ Team
    $teamId = $I->haveRecord(\common\models\Team::class, [
        'name' => 'Test Team',
        'created_at' => $time,
    ]);

    // 4️⃣ Team member
    $I->haveRecord(\common\models\TeamMembers::class, [
        'team_id' => $teamId,
        'user_id' => $userId,
        'role' => 'member',
        'created_at' => $time,
    ]);

    // 5️⃣ Board (team_id BELONGS HERE ✅)
    $boardId = $I->haveRecord(\common\models\Board::class, [
        'team_id' => $teamId,
        'title' => 'Test Board',
        'created_at' => $time,
    ]);

    // 6️⃣ Task (NO team_id here ❌)
    $I->haveRecord(\common\models\Task::class, [
        'title' => 'Activity Task',
        'board_id' => $boardId,   // ✅ correct relation
        'status' => 'open',
        'created_at' => $time,
        'updated_at' => $time,
    ]);

    // 7️⃣ Page visit
    $I->amOnPage('/activity/index');

    $I->seeResponseCodeIs(200);
    $I->see('Activity Task');
}

}
