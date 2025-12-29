<?php

namespace common\tests\unit\models;

use Yii;
use common\models\Task;
use common\models\User;
use common\models\Board;
use common\models\TaskImage;
use common\models\TaskAttachment;
use Codeception\Test\Unit;

class TaskTest extends Unit
{
    protected function _before()
    {
        // clean related tables (safe for isolated tests)
        Task::deleteAll();
        TaskImage::deleteAll();
        TaskAttachment::deleteAll();
        Board::deleteAll();
        User::deleteAll();
    }

    /* =========================
     * VALIDATION TESTS
     * ========================= */

    public function testValidationFailsWithoutRequiredFields()
    {
        $task = new Task();

        $this->assertFalse($task->validate());
        $this->assertArrayHasKey('title', $task->errors);
        $this->assertArrayHasKey('board_id', $task->errors);
    }

    public function testValidationPassesWithValidData()
    {
        $task = new Task([
            'title'    => 'Test Task',
            'board_id' => 1,
            'status'   => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM,
        ]);

        $this->assertTrue($task->validate());
    }

    public function testInvalidStatusFailsValidation()
    {
        $task = new Task([
            'title'    => 'Invalid Status',
            'board_id' => 1,
            'status'   => 'wrong_status',
        ]);

        $this->assertFalse($task->validate());
        $this->assertArrayHasKey('status', $task->errors);
    }

    public function testInvalidPriorityFailsValidation()
    {
        $task = new Task([
            'title'    => 'Invalid Priority',
            'board_id' => 1,
            'priority' => 'urgent',
        ]);

        $this->assertFalse($task->validate());
        $this->assertArrayHasKey('priority', $task->errors);
    }

    /* =========================
     * CONSTANTS TEST
     * ========================= */

    public function testStatusesHelper()
    {
        $statuses = Task::statuses();

        $this->assertArrayHasKey(Task::STATUS_TODO, $statuses);
        $this->assertArrayHasKey(Task::STATUS_IN_PROGRESS, $statuses);
        $this->assertArrayHasKey(Task::STATUS_DONE, $statuses);
        $this->assertArrayHasKey(Task::STATUS_ARCHIVED, $statuses);
    }

    public function testPrioritiesHelper()
    {
        $priorities = Task::priorities();

        $this->assertArrayHasKey(Task::PRIORITY_LOW, $priorities);
        $this->assertArrayHasKey(Task::PRIORITY_MEDIUM, $priorities);
        $this->assertArrayHasKey(Task::PRIORITY_HIGH, $priorities);
    }

    /* =========================
     * RELATION TESTS
     * ========================= */

   public function testBoardRelation()
{
    // ---------------------------
    // Create User
    // ---------------------------
    $user = new \common\models\User([
        'username' => 'team_owner',
        'email'    => 'team@test.com',
        'password_hash' => Yii::$app->security->generatePasswordHash('123456'),
    ]);
    $user->save(false);

    // ---------------------------
    // Create Team (DISABLE Blameable)
    // ---------------------------
    $team = new \common\models\Team();
    $team->detachBehavior('blameable'); // 🔥 IMPORTANT
    $team->name = 'Test Team';
    $team->created_by = $user->id;
    $team->save(false);

    // ---------------------------
    // Create Board (DISABLE Blameable)
    // ---------------------------
    $board = new \common\models\Board();
    $board->detachBehavior('blameable'); // 🔥 IMPORTANT
    $board->title = 'Test Board';
    $board->team_id = $team->id;
    $board->created_by = $user->id;
    $board->save(false);

    // ---------------------------
    // Create Task
    // ---------------------------
    $task = new \common\models\Task([
        'title'    => 'Board Task',
        'board_id' => $board->id,
    ]);
    $task->save(false);

    // ---------------------------
    // Assert
    // ---------------------------
    $this->assertInstanceOf(\common\models\Board::class, $task->board);
    $this->assertEquals($board->id, $task->board->id);
}




    public function testCreatorRelation()
{
    $user = new User([
        'username' => 'creator',
        'email'    => 'creator@test.com',
        'password_hash' => Yii::$app->security->generatePasswordHash('123456'),
    ]);
    $user->save(false);

    $task = new Task([
        'title'    => 'Creator Task',
        'board_id' => 1,
    ]);
    $task->save(false);

    // manually assign creator
    $task->created_by = $user->id;
    $task->save(false);

    $this->assertInstanceOf(User::class, $task->creator);
    $this->assertEquals($user->id, $task->creator->id);
}


    /* =========================
     * CUSTOM VALIDATOR TEST
     * ========================= */

    public function testTotalAttachmentSizeValidation()
{
    $task = new Task([
        'title'    => 'Attachment Test',
        'board_id' => 1,
    ]);

    // Fake file-like objects (NO FileValidator involved)
    $file1 = (object)['size' => 6 * 1024 * 1024];
    $file2 = (object)['size' => 6 * 1024 * 1024];

    $task->attachmentFiles = [$file1, $file2];

    // call ONLY custom validator
    $task->validateTotalAttachmentSize('attachmentFiles', []);

    $this->assertArrayHasKey('attachmentFiles', $task->errors);
}


    /* =========================
     * SAVE TEST
     * ========================= */

    public function testTaskSave()
    {
        $task = new Task([
            'title'    => 'Save Task',
            'board_id' => 1,
        ]);

        $this->assertTrue($task->save());
        $this->assertNotNull($task->id);
    }
}
