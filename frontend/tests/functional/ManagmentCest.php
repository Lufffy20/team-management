<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;
use common\fixtures\UserFixture;
use common\models\Task;

class ManagmentCest
{
    public function _fixtures()
    {
        return [
            'user' => UserFixture::class,
        ];
    }

    /**
     * 🔥 IMPORTANT
     * Reset auth state before EVERY test
     */
    protected function _before(FunctionalTester $I)
    {
        $I->logout(); // ✅ correct way
    }

    /* =========================
     * MY TASKS (AUTH REQUIRED)
     * ========================= */
    public function myTasksRequiresLogin(FunctionalTester $I)
{
    $I->stopFollowingRedirects();  
    $I->amOnPage('/managment/mytasks');
    $I->seeResponseCodeIs(302);
}


    public function myTasksShowsUserTasks(FunctionalTester $I)
    {
        $I->amLoggedInAs(1);
        $I->amOnPage('/managment/mytasks');
        $I->seeResponseCodeIs(200);
        $I->see('My Tasks');
    }

    /* =========================
     * CREATE TASK
     * ========================= */
    public function createTaskWorks(FunctionalTester $I)
{
    $I->amLoggedInAs(1);

    $I->sendAjaxPostRequest('/managment/create-task', [
        'Task' => [
            'title' => 'Test Task',
            'description' => 'Test description',
            'status' => 'todo',
        ]
    ]);

    $I->seeRecord(Task::class, [
        'title' => 'Test Task',
        'created_by' => 1,
    ]);
}





    /* =========================
     * UPDATE TASK
     * ========================= */
 public function updateTaskWorks(FunctionalTester $I)
{
    $I->amLoggedInAs(1);

    $task = new Task([
        'title' => 'Old Title',
        'status' => 'todo',
        'created_by' => 1,
        'assignee_id' => 1,
    ]);
    $task->save(false);

    $I->sendAjaxPostRequest('/managment/update-task?id=' . $task->id, [
        'Task' => [
            'title' => 'Updated Title',
        ]
    ]);

    $I->seeRecord(Task::class, [
        'id' => $task->id,
        'title' => 'Updated Title',
    ]);
}





    /* =========================
     * DELETE TASK
     * ========================= */
    public function deleteTaskWorks(FunctionalTester $I)
    {
        $I->amLoggedInAs(1);

        $task = new Task([
            'title' => 'Delete Me',
            'assignee_id' => 1,
            'created_by' => 1,
            'status' => 'open',
        ]);
        $task->save(false);

        $I->amOnPage('/managment/delete-task?id=' . $task->id);

        $I->dontSeeRecord(Task::class, [
            'id' => $task->id,
        ]);
    }

    /* =========================
     * VIEW TASK
     * ========================= */
    public function viewTaskWorks(FunctionalTester $I)
    {
        $I->amLoggedInAs(1);

        $task = new Task([
            'title' => 'View Task',
            'assignee_id' => 1,
            'created_by' => 1,
            'status' => 'open',
        ]);
        $task->save(false);

        $I->amOnPage('/managment/view-task?id=' . $task->id);
        $I->see('View Task');
    }

    /* =========================
     * PROFILE PAGE
     * ========================= */
    public function profileRequiresLogin(FunctionalTester $I)
{
    $I->stopFollowingRedirects();   // 🔥 ADD THIS
    $I->amOnPage('/managment/profile');
    $I->seeResponseCodeIs(302);
}


    public function profileLoadsForLoggedInUser(FunctionalTester $I)
    {
        $I->amLoggedInAs(1);

        $I->amOnPage('/managment/profile');
        $I->seeResponseCodeIs(200);
        $I->see('Profile');
    }
}
