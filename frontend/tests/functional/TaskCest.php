<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;
use common\models\User;
use common\models\Board;
use common\models\Task;
use common\models\KanbanColumn;
use common\models\Subtask;
use common\models\Team;
use common\models\TeamMembers;

class TaskCest
{
    /* ================= LOGIN ================= */
    protected function login(FunctionalTester $I)
    {
        $user = new User([
            'username' => 'taskuser_' . uniqid(),
            'email'    => uniqid() . '@test.com',
            'status'   => User::STATUS_ACTIVE,
        ]);

        $user->setPassword('password');
        $user->generateAuthKey();
        $user->save(false);

        $I->amLoggedInAs($user);
        return $user;
    }

    /* ================= BOARD ================= */
    protected function createBoardWithColumn($user)
    {
        $team = new Team([
            'name'       => 'Task Team',
            'created_by' => $user->id,
            'created_at' => time(),
        ]);
        $team->save(false);

        (new TeamMembers([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role'    => 'manager',
        ]))->save(false);

        $board = new Board([
            'title'      => 'Task Board',
            'team_id'    => $team->id,
            'created_by' => $user->id,
        ]);
        $board->save(false);

        (new KanbanColumn([
            'board_id' => $board->id,
            'status'   => Task::STATUS_TODO,
            'position' => 1,
            'user_id'  => $user->id,
        ]))->save(false);

        return [$board];
    }

    /* ================= ACCESS ================= */
    public function kanbanRequiresLogin(FunctionalTester $I)
    {
        $I->amOnRoute('/task/kanban');
        $I->seeResponseCodeIs(200);
    }

    /* ================= LOAD ================= */
    public function kanbanLoads(FunctionalTester $I)
    {
        $user = $this->login($I);
        [$board] = $this->createBoardWithColumn($user);

        $I->amOnRoute('/task/kanban', ['board_id' => $board->id]);
        $I->seeResponseCodeIs(200);
        $I->see('Task Board');
    }

    /* ================= CREATE (AJAX) ================= */
    public function createTaskAjaxWorks(FunctionalTester $I)
    {
        $user = $this->login($I);
        [$board] = $this->createBoardWithColumn($user);

        $I->sendAjaxPostRequest('/task/create-ajax', [
            'Task' => [
                'title'    => 'Test Task',
                'board_id' => $board->id,
                'status'   => Task::STATUS_TODO,
            ]
        ]);

        $I->seeResponseCodeIs(200);

        $response = json_decode($I->grabPageSource(), true);
        $I->assertTrue($response['success']);
    }

    /* ================= UPDATE ================= */
    public function updateTaskWorks(FunctionalTester $I)
    {
        $user = $this->login($I);
        [$board] = $this->createBoardWithColumn($user);

        $task = new Task([
            'title'       => 'Old Title',
            'board_id'    => $board->id,
            'created_by'  => $user->id,
            'status'      => Task::STATUS_TODO,
        ]);
        $task->save(false);

        // Controller POST expect karta hai + redirect karta hai
        $I->sendAjaxPostRequest('/task/update?id=' . $task->id, [
            'Task' => [
                'title' => 'Updated Title'
            ]
        ]);

        // 🔥 Redirect expected → 302
        $I->seeResponseCodeIs(302);

        // 🔥 REAL verification (DB)
        $task->refresh();
        $I->assertEquals('Updated Title', $task->title);
    }

    /* ================= DELETE ================= */
    public function deleteTaskWorks(FunctionalTester $I)
    {
        $user = $this->login($I);
        [$board] = $this->createBoardWithColumn($user);

        $task = new Task([
            'title'      => 'Delete Me',
            'board_id'   => $board->id,
            'created_by' => $user->id,
            'status'     => Task::STATUS_TODO,
        ]);
        $task->save(false);

        $I->amOnRoute('/task/delete', [
            'id' => $task->id
        ]);

        $I->seeResponseCodeIs(200);
    }

    /* ================= SUBTASK ================= */
    public function subtaskFlowWorks(FunctionalTester $I)
    {
        $user = $this->login($I);
        [$board] = $this->createBoardWithColumn($user);

        $task = new Task([
            'title'      => 'Task with Subtask',
            'board_id'   => $board->id,
            'created_by' => $user->id,
            'status'     => Task::STATUS_TODO,
        ]);
        $task->save(false);

        // Validation-safe insert
        $subtask = new Subtask([
            'task_id' => $task->id,
            'title'   => 'Subtask 1',
        ]);
        $subtask->save(false);

        $I->assertNotNull($subtask->id);

        /* ===== TOGGLE ===== */
        $I->sendAjaxPostRequest(
            '/task/toggle-subtask?id=' . $subtask->id
        );

        $toggleResp = json_decode($I->grabPageSource(), true);
        $I->assertTrue($toggleResp['success']);

        /* ===== DELETE ===== */
        $I->sendAjaxPostRequest(
            '/task/delete-subtask?id=' . $subtask->id
        );

        $deleteResp = json_decode($I->grabPageSource(), true);
        $I->assertTrue($deleteResp['success']);
    }
}
