<?php

namespace common\tests\unit\models;

use common\models\Subtask;
use common\models\Task;
use common\models\User;
use Codeception\Test\Unit;

class SubtaskTest extends Unit
{
    protected function _before()
    {
        // Clean tables before each test
        Subtask::deleteAll();
        Task::deleteAll();
        User::deleteAll();
    }

    /* =====================================
     * VALIDATION TESTS
     * ===================================== */

    public function testValidationFailsWithoutRequiredFields()
    {
        $subtask = new Subtask();

        $this->assertFalse($subtask->validate());
        $this->assertArrayHasKey('task_id', $subtask->errors);
        $this->assertArrayHasKey('title', $subtask->errors);
    }

    public function testValidationPassesWithValidData()
    {
        $task = $this->createTask();

        $subtask = new Subtask([
            'task_id' => $task->id,
            'title'   => 'Test Subtask',
            'is_done' => false,
        ]);

        $this->assertTrue($subtask->validate());
    }

    public function testIsDoneMustBeBoolean()
    {
        $task = $this->createTask();

        $subtask = new Subtask([
            'task_id' => $task->id,
            'title'   => 'Invalid is_done',
            'is_done' => 'yes', // invalid
        ]);

        $this->assertFalse($subtask->validate());
        $this->assertArrayHasKey('is_done', $subtask->errors);
    }

    /* =====================================
     * DATABASE TESTS
     * ===================================== */

    public function testSaveSubtask()
    {
        $task = $this->createTask();

        $subtask = new Subtask([
            'task_id' => $task->id,
            'title'   => 'Saved Subtask',
            'is_done' => false,
        ]);

        $this->assertTrue($subtask->save());
        $this->assertNotNull($subtask->id);
    }

    /* =====================================
     * RELATION TESTS
     * ===================================== */

    public function testTaskRelation()
    {
        $task = $this->createTask();

        $subtask = new Subtask([
            'task_id' => $task->id,
            'title'   => 'Relation Test',
        ]);
        $subtask->save();

        $this->assertInstanceOf(Task::class, $subtask->task);
        $this->assertEquals($task->id, $subtask->task->id);
    }

    /* =====================================
     * HELPER METHODS
     * ===================================== */

    protected function createTask()
    {
        $user = new User([
            'username' => 'testuser',
            'email'    => 'test@example.com',
        ]);
        $user->setPassword('password123');
        $user->generateAuthKey();
        $user->save(false);

        $task = new Task([
            'title'       => 'Test Task',
            'created_by'  => $user->id,
        ]);
        $task->save(false);

        return $task;
    }
}
