<?php

namespace frontend\tests\unit\models;

use common\fixtures\UserFixture;
use frontend\models\SignupForm;

class SignupFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;


    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ]
        ]);
    }

    public function testCorrectSignup()
    {
        $model = new SignupForm([
            'first_name'       => 'John',
            'last_name'        => 'Doe',
            'username'         => 'some_username',
            'email'            => 'some_email@example.com',
            'password'         => 'Some_pass1!',
            'confirm_password' => 'Some_pass1!',
        ]);

        $user = $model->signup();
        verify($user)->notEmpty();

        /** @var \common\models\User $user */
        $user = $this->tester->grabRecord('common\models\User', [
            'username' => 'some_username',
            'email'    => 'some_email@example.com',
            'status'   => \common\models\User::STATUS_INACTIVE
        ]);

        $this->tester->seeEmailIsSent();
        $mail = $this->tester->grabLastSentEmail();

        verify($mail)->instanceOf('yii\mail\MessageInterface');
        verify($mail->getTo())->arrayHasKey('some_email@example.com');
        verify($mail->getFrom())->arrayHasKey(\Yii::$app->params['supportEmail']);
        verify($mail->getSubject())->equals('Verify your email - ' . \Yii::$app->name);
        verify($mail->toString())->stringContainsString($user->verification_token);
    }

    public function testNotCorrectSignup()
    {
        $model = new SignupForm([
            'first_name'       => 'A',
            'last_name'        => 'B',
            'username'         => 'okirlin',  
            'email'            => 'brady.renner@rutherford.com',
            'password'         => 'Some_pass1!',
            'confirm_password' => 'Some_pass1!',
        ]);

        verify($model->signup())->empty();
        verify($model->getErrors('username'))->notEmpty();
        verify($model->getErrors('email'))->notEmpty();

        verify($model->getFirstError('username'))
            ->equals('This username has already been taken.');
        verify($model->getFirstError('email'))
            ->equals('This email address has already been taken.');
    }
}
