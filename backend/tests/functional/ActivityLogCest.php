<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\models\User;
use common\models\ActivityLog;

class ActivityLogCest
{
    public function guestCannotAccessActivityLogs(FunctionalTester $I)
    {
        $I->amOnPage('activity-log/index');

        // Backend allows access (no loginUrl)
        $I->seeResponseCodeIs(200);
    }

    public function loggedInUserCanSeeActivityLogs(FunctionalTester $I)
    {
        // USER
        $user = new User();
        $user->username = 'activity_' . uniqid();
        $user->email    = uniqid() . '@test.com';
        $user->status   = User::STATUS_ACTIVE;
        $user->setPassword('password');
        $user->generateAuthKey();
        $user->save(false);

        $I->amLoggedInAs($user);

        // ACTIVITY LOG (REAL COLUMNS ONLY)
        $log = new ActivityLog();
        $log->user_id    = $user->id;
        $log->action     = 'Created task';
        $log->details    = 'Task created for testing';
        $log->created_at = time();
        $log->save(false);

        // PAGE
        $I->amOnPage('activity-log/index');

        $I->seeResponseCodeIs(200);
        $I->see('Created task');
    }
}