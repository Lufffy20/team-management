<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class SignupCest
{
    protected $formId = '#form-signup';

    public function _before(FunctionalTester $I)
    {
        $I->amOnRoute('site/signup');
    }

    public function signupWithEmptyFields(FunctionalTester $I)
    {
        $I->see("Signup");
        $I->see("Create your account by filling the form below.");

        $I->submitForm($this->formId, []);

        $I->seeValidationError('First Name cannot be blank.');
        $I->seeValidationError('Last Name cannot be blank.');
        $I->seeValidationError('Username cannot be blank.');
        $I->seeValidationError('Email cannot be blank.');
        $I->seeValidationError('Password cannot be blank.');
        $I->seeValidationError('Confirm Password cannot be blank.');
    }

    public function signupWithWrongEmail(FunctionalTester $I)
    {
        $I->submitForm($this->formId, [
            'SignupForm[first_name]'      => 'Test',
            'SignupForm[last_name]'       => 'User',
            'SignupForm[username]'        => 'tester',
            'SignupForm[email]'           => 'wrong-email',
            'SignupForm[password]'        => 'Tester@123',
            'SignupForm[confirm_password]' => 'Tester@123',
        ]);

        $I->see('Email is not a valid email address.', '.invalid-feedback');
    }

    public function signupSuccessfully(FunctionalTester $I)
    {
        $I->submitForm($this->formId, [
            'SignupForm[first_name]'       => 'Test',
            'SignupForm[last_name]'        => 'User',
            'SignupForm[username]'         => 'tester',
            'SignupForm[email]'            => 'tester.email@example.com',
            'SignupForm[password]'         => 'Tester@123',
            'SignupForm[confirm_password]' => 'Tester@123',
        ]);

        $I->seeRecord('common\models\User', [
            'username' => 'tester',
            'email' => 'tester.email@example.com',
            'status' => \common\models\User::STATUS_INACTIVE
        ]);

        $I->seeEmailIsSent();
        $I->see("Thank you for registration.");
    }
}
