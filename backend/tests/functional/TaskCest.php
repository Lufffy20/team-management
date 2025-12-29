<?php

namespace backend\tests\functional;

use Yii;
use backend\tests\FunctionalTester;
use common\models\User;
use common\models\Task;

class TaskCest
{
    protected function loginAsAdmin(FunctionalTester $I)
    {
        $admin = new User([
            'username' => 'admin_' . uniqid(),
            'email'    => uniqid() . '@test.com',
            'status'   => User::STATUS_ACTIVE,
            'role'     => 1,
        ]);

        $admin->setPassword('password');
        $admin->generateAuthKey();
        $admin->save(false);

        $I->amLoggedInAs($admin);
    }

    public function indexPageWorks(FunctionalTester $I)
    {
        $this->loginAsAdmin($I);
        $I->amOnPage('/index-test.php/task/index');
        $I->seeResponseCodeIs(200);
    }

    public function createTaskWorks(FunctionalTester $I)
    {
        $this->loginAsAdmin($I);
        $I->amOnPage('/index-test.php/task/create');

        $I->submitForm('form', [
            'Task[title]' => 'Test Task',
        ]);

        $I->seeResponseCodeIs(200);
    }

    public function viewTaskWorks(FunctionalTester $I)
    {
        $this->loginAsAdmin($I);

        $task = new Task();
        $task->title = 'View Task';
        $task->save(false);

        $I->amOnPage('/index-test.php/task/view?id=' . $task->id);
        $I->see('View Task');
    }

    public function updateTaskWorks(FunctionalTester $I)
    {
        $this->loginAsAdmin($I);

        $task = new Task();
        $task->title = 'Old Task';
        $task->save(false);

        $I->amOnPage('/index-test.php/task/update?id=' . $task->id);
        $I->submitForm('form', [
            'Task[title]' => 'Updated Task',
        ]);

        $I->seeResponseCodeIs(200);
    }

   public function deleteTaskWorks(FunctionalTester $I)
{
    $this->loginAsAdmin($I);

    // Create task
    $task = new Task();
    $task->title = 'Delete Task';
    $task->save(false);

    // Direct POST with CSRF token (NO JS needed)
    $I->sendAjaxPostRequest(
        '/index-test.php/task/delete?id=' . $task->id,
        [
            Yii::$app->request->csrfParam => Yii::$app->request->getCsrfToken(),
        ]
    );

    // Assert deletion
    $I->dontSeeRecord(Task::class, [
        'id' => $task->id,
    ]);
}

}
