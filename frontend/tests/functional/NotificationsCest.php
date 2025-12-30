<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;
use common\models\User;
use common\models\Notification;

class NotificationsCest
{
    public function _before(FunctionalTester $I)
    {
        // Create test user
        $user = new User([
            'username' => 'notifyuser',
            'email' => 'notify@test.com',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('password123');
        $user->generateAuthKey();
        $user->save(false);

        // Create notifications
        Notification::deleteAll(['user_id' => $user->id]);

        for ($i = 1; $i <= 3; $i++) {
            $n = new Notification();
            $n->user_id    = $user->id;
            $n->title      = "Test Notification $i";
            $n->message    = "Message $i";
            $n->is_read    = 0;
            $n->created_at = time();
            $n->save(false);
        }

        $I->amLoggedInAs($user);
    }

    /** 🔔 Notification list page */
    public function checkNotificationIndex(FunctionalTester $I)
    {
        $I->amOnRoute('notifications/index');

        $I->seeResponseCodeIs(200);
        $I->see('Test Notification 1');
        $I->see('Test Notification 2');
        $I->see('Test Notification 3');
    }

    /** 📩 Mark all as read */
    public function checkMarkAllRead(FunctionalTester $I)
{
    $I->amOnRoute('notifications/mark-all-read');

    // After redirect, we land on index (200 OK)
    $I->seeResponseCodeIs(200);

    $unread = Notification::find()
        ->where(['is_read' => 0])
        ->count();

    $I->assertEquals(0, $unread);
}
}
