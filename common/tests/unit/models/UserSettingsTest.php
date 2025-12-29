<?php

namespace common\tests\unit\models;

use Yii;
use Codeception\Test\Unit;
use common\models\UserSettings;
use common\models\User;
use yii\web\UnauthorizedHttpException;

class UserSettingsTest extends Unit
{
    /**
     * Test default theme value
     */
    public function testDefaultTheme()
    {
        $model = new UserSettings();
        $this->assertEquals('light', $model->theme);
    }

    /**
     * Test validation passes for valid themes
     */
    public function testThemeValidationPasses()
    {
        $model = new UserSettings();

        $model->theme = 'light';
        $this->assertTrue($model->validate());

        $model->theme = 'dark';
        $this->assertTrue($model->validate());
    }

    /**
     * Test validation fails for invalid theme
     */
    public function testThemeValidationFails()
    {
        $model = new UserSettings();
        $model->theme = 'blue';

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('theme', $model->errors);
    }

    /**
     * Test loadFromUser throws exception when user not logged in
     */
    public function testLoadFromUserWithoutLogin()
    {
        Yii::$app->user->logout();

        $this->expectException(UnauthorizedHttpException::class);

        $model = new UserSettings();
        $model->loadFromUser();
    }

    /**
     * Test loadFromUser loads theme from logged-in user
     */
    public function testLoadFromUserWithLoggedInUser()
{
    $user = new User([
        'id' => 999,
        'username' => 'testuser',
        'email' => 'test@example.com',
    ]);

    Yii::$app->user->login($user);

    $model = new UserSettings();
    $model->loadFromUser();

    // since user has no theme column, fallback should be light
    $this->assertEquals('light', $model->theme);
}

}
