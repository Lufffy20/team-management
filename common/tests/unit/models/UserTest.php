<?php

namespace common\tests\unit\models;

use common\models\User;
use Yii;
use yii\db\ActiveQuery;

class UserTest extends \Codeception\Test\Unit
{
    public function testValidationFailsWithoutRequiredFields()
    {
        $user = new User(['scenario' => 'create']);
        $this->assertFalse($user->validate());
    }

    public function testValidationPassesWithValidData()
    {
        $user = new User(['scenario' => 'create']);

        $user->first_name = 'Meet';
        $user->last_name  = 'Parmar';
        $user->username   = 'meet_' . uniqid();
        $user->email      = uniqid().'@example.com';
        $user->password   = 'password123';
        $user->confirm_password = 'password123';
        $user->status = User::STATUS_ACTIVE;
        $user->role   = User::ROLE_USER;

        $this->assertTrue($user->validate(false));
    }

    public function testPasswordHashing()
    {
        $user = new User();
        $user->setPassword('secret123');

        $this->assertTrue(
            Yii::$app->security->validatePassword(
                'secret123',
                $user->password_hash
            )
        );
    }

    public function testGenerateAuthKey()
    {
        $user = new User();
        $user->generateAuthKey();

        $this->assertNotEmpty($user->auth_key);
    }

    public function testGenerateAccessToken()
    {
        $user = new User();
        $user->generateAccessToken();

        $this->assertNotEmpty($user->access_token);
        $this->assertEquals(64, strlen($user->access_token));
    }

    public function testRoleHelpers()
    {
        $admin = new User();
        $admin->role = User::ROLE_ADMIN;

        $user = new User();
        $user->role = User::ROLE_USER;

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($user->isUser());
    }

    public function testRelations()
    {
        $user = new User();

        $this->assertInstanceOf(ActiveQuery::class, $user->getTeams());
        $this->assertInstanceOf(ActiveQuery::class, $user->getTeamMembers());
    }

    public function testDefaultAvatar()
    {
        $user = new User();
        $this->assertEquals('/images/default-avatar.png', $user->getAvatarUrl());
    }
}
