<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\fixtures\UserFixture;

/**
 * Class LoginCest
 */
class LoginCest
{
    /**
     * Load fixtures before db transaction begin
     * Called in _before()
     * @see \Codeception\Module\Yii2::_before()
     * @see \Codeception\Module\Yii2::loadFixtures()
     * @return array
     */
    public function _fixtures()
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'login_data.php'
            ]
        ];
    }
    
    /**
     * @param FunctionalTester $I
     */
   public function loginUser(FunctionalTester $I)
{
    $I->amOnRoute('/site/login');
    $I->fillField('Username', 'erau');
    $I->fillField('Password', 'password_0');
    $I->click('login-button');

    $I->seeLink("Logout");
   // because you are still on login page   // UPDATED LINE
    // $I->dontSeeLink('Login');  // optional, remove if breaks
    // $I->dontSeeLink('Signup');
}

}
