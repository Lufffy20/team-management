<?php

namespace backend\tests\unit\models;

use backend\models\PasswordResetRequestForm;
use common\models\User;
use Codeception\Test\Unit;
use Yii;

class PasswordResetRequestFormTest extends Unit
{
    protected function _before()
    {
        Yii::$app->db->createCommand()->delete('user')->execute();
    }

    private function createUser($email = 'user@test.com')
    {
        $user = new User();
        $user->username = 'testuser';
        $user->email = $email;
        $user->status = User::STATUS_ACTIVE;
        $user->password_hash = Yii::$app->security->generatePasswordHash('password');
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->created_at = time();
        $user->updated_at = time();
        $user->save(false);

        return $user;
    }

    public function testValidationFailsWithoutEmail()
    {
        $model = new PasswordResetRequestForm();
        $this->assertFalse($model->validate());
    }

    public function testValidationFailsForInvalidEmail()
    {
        $model = new PasswordResetRequestForm();
        $model->email = 'invalid-email';
        $this->assertFalse($model->validate());
    }

    public function testValidationFailsIfUserNotExists()
    {
        $model = new PasswordResetRequestForm();
        $model->email = 'missing@test.com';
        $this->assertFalse($model->validate());
    }

    public function testValidationPassesForExistingUser()
    {
        $this->createUser();

        $model = new PasswordResetRequestForm();
        $model->email = 'user@test.com';

        $this->assertTrue($model->validate());
    }

    public function testSendEmailReturnsFalseWhenUserNotFound()
    {
        $model = new PasswordResetRequestForm();
        $model->email = 'missing@test.com';

        $this->assertFalse($model->sendEmail());
    }

    public function testSendEmailSuccess()
    {
        $user = $this->createUser();

        $this->assertEmpty($user->password_reset_token);

        $model = new PasswordResetRequestForm();
        $model->email = $user->email;

        $this->assertTrue($model->sendEmail());

        $user->refresh();
        $this->assertNotEmpty($user->password_reset_token);
    }
}
