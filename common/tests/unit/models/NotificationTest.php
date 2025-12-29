<?php

namespace common\tests\unit\models;

use common\models\Notification;
use common\models\User;
use Codeception\Test\Unit;

class NotificationTest extends Unit
{
    protected function _before()
    {
        // Clean tables before each test
        Notification::deleteAll();
        User::deleteAll();
    }

    /* =====================================
     * VALIDATION TESTS
     * ===================================== */

    public function testValidationFailsWithoutRequiredFields()
    {
        $model = new Notification();

        $this->assertFalse($model->validate());

        $this->assertArrayHasKey('user_id', $model->errors);
        $this->assertArrayHasKey('title', $model->errors);
        $this->assertArrayHasKey('message', $model->errors);
    }

    public function testValidationPassesWithValidData()
    {
        $user = new User([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => 'hash'
        ]);
        $user->save(false);

        $model = new Notification([
            'user_id' => $user->id,
            'title'   => 'Test Notification',
            'message' => 'This is a test message',
            'is_read' => 0,
        ]);

        $this->assertTrue($model->validate());
    }

    /* =====================================
     * SAVE TEST
     * ===================================== */

    public function testSaveNotification()
    {
        $user = new User([
            'username' => 'saveuser',
            'email' => 'save@example.com',
            'password_hash' => 'hash'
        ]);
        $user->save(false);

        $notification = new Notification([
            'user_id' => $user->id,
            'title'   => 'Saved Notification',
            'message' => 'Saved message',
            'is_read' => 0,
        ]);

        $this->assertTrue($notification->save());
        $this->assertNotNull($notification->id);
        $this->assertNotNull($notification->created_at);
    }

    /* =====================================
     * RELATION TEST
     * ===================================== */

    public function testUserRelation()
    {
        $user = new User([
            'username' => 'relationuser',
            'email' => 'relation@example.com',
            'password_hash' => 'hash'
        ]);
        $user->save(false);

        $notification = new Notification([
            'user_id' => $user->id,
            'title'   => 'Relation Test',
            'message' => 'Testing user relation',
        ]);
        $notification->save(false);

        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($user->id, $notification->user->id);
    }

    /* =====================================
     * DATA TYPE TESTS
     * ===================================== */

    public function testTitleMaxLength()
    {
        $notification = new Notification([
            'title' => str_repeat('a', 300)
        ]);

        $this->assertFalse($notification->validate(['title']));
    }

    public function testIsReadIsInteger()
    {
        $notification = new Notification([
            'is_read' => 'not-an-integer'
        ]);

        $this->assertFalse($notification->validate(['is_read']));
    }
}
