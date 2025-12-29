<?php

namespace common\tests\unit\models;

use common\models\TaskComment;
use common\models\User;
use common\models\Task;
use Codeception\Test\Unit;

class TaskCommentTest extends Unit
{
    protected function _before()
    {
        // clean tables
        TaskComment::deleteAll();
        User::deleteAll();
        Task::deleteAll();
    }

    /* =====================================
     * VALIDATION TESTS
     * ===================================== */

    public function testValidationFailsWithoutRequiredFields()
    {
        $model = new TaskComment();

        $this->assertFalse($model->validate());

        $this->assertArrayHasKey('task_id', $model->errors);
        $this->assertArrayHasKey('user_id', $model->errors);
        $this->assertArrayHasKey('comment', $model->errors);
    }

    public function testValidationFailsWithNonIntegerIds()
    {
        $model = new TaskComment([
            'task_id' => 'abc',
            'user_id' => 'xyz',
            'comment' => 'Test comment'
        ]);

        $this->assertFalse($model->validate());
    }

    public function testValidationPassesWithValidData()
    {
        $model = new TaskComment([
            'task_id' => 1,
            'user_id' => 1,
            'comment' => 'This is a test comment'
        ]);

        $this->assertTrue($model->validate());
    }

    /* =====================================
     * SAVE & BEFORE SAVE TEST
     * ===================================== */

    public function testSaveSetsCreatedAtAutomatically()
    {
        // Create user
        $user = new User([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => 'hash',
            'auth_key' => 'key'
        ]);
        $user->save(false);

        // Create task
        $task = new Task([
            'title' => 'Test Task',
            'created_by' => $user->id
        ]);
        $task->save(false);

        $comment = new TaskComment([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => 'Nice task'
        ]);

        $this->assertTrue($comment->save());

        $this->assertNotNull($comment->created_at);
        $this->assertIsInt($comment->created_at);
    }

    /* =====================================
     * RELATION TEST
     * ===================================== */

    public function testUserRelation()
    {
        $user = new User([
            'username' => 'relationuser',
            'email' => 'relation@example.com',
            'password_hash' => 'hash',
            'auth_key' => 'key'
        ]);
        $user->save(false);

        $comment = new TaskComment([
            'task_id' => 1,
            'user_id' => $user->id,
            'comment' => 'Relation test'
        ]);
        $comment->save(false);

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertEquals($user->id, $comment->user->id);
    }
}
