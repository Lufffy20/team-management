<?php

namespace backend\tests\unit\models;

use backend\models\ResetPasswordForm;
use common\models\User;
use yii\base\InvalidArgumentException;
use Codeception\Test\Unit;
use Yii;

class ResetPasswordFormTest extends Unit
{
    protected function _before()
    {
        // Clean user table
        Yii::$app->db->createCommand()->delete(User::tableName())->execute();
    }

    /**
     * ❌ Constructor fails when token is empty
     */
    public function testConstructorWithEmptyToken()
    {
        $this->expectException(InvalidArgumentException::class);
        new ResetPasswordForm('');
    }

    /**
     * ❌ Constructor fails with invalid token
     */
    public function testConstructorWithInvalidToken()
    {
        $this->expectException(InvalidArgumentException::class);
        new ResetPasswordForm('invalid_token');
    }

    /**
     * ❌ Validation fails without password
     */
    public function testValidationFailsWithoutPassword()
    {
        $user = $this->createUser();

        $model = new ResetPasswordForm($user->password_reset_token);
        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('password', $model->errors);
    }

    /**
     * ❌ Validation fails with short password
     */
    public function testValidationFailsWithShortPassword()
    {
        $user = $this->createUser();

        $model = new ResetPasswordForm($user->password_reset_token);
        $model->password = '123';
        $this->assertFalse($model->validate());
    }

    /**
     * ✅ Password reset success
     */
    public function testResetPasswordSuccess()
    {
        $user = $this->createUser();

        $model = new ResetPasswordForm($user->password_reset_token);
        $model->password = 'newpassword123';

        $this->assertTrue($model->validate());
        $this->assertTrue($model->resetPassword());

        $user->refresh();

        // password_reset_token should be removed
        $this->assertEmpty($user->password_reset_token);

        // new password should be valid
        $this->assertTrue(Yii::$app->security->validatePassword(
            'newpassword123',
            $user->password_hash
        ));
    }

    /**
     * Helper: create user with reset token
     */
    private function createUser()
    {
        $user = new User();
        $user->username = 'test_user';
        $user->email = 'test@example.com';
        $user->setPassword('oldpassword');
        $user->generateAuthKey();
        $user->generatePasswordResetToken();
        $user->status = User::STATUS_ACTIVE;
        $user->created_at = time();
        $user->updated_at = time();
        $user->save(false);

        return $user;
    }
}
