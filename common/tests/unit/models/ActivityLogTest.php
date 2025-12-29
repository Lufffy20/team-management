<?php

namespace common\tests\unit\models;

use common\models\ActivityLog;
use common\models\User;
use common\models\Team;
use common\models\Board;
use Codeception\Test\Unit;

class ActivityLogTest extends Unit
{
    protected function _before()
    {
        // Optional: cleanup if needed
    }

    protected function _after()
    {
        // Optional: cleanup if needed
    }

    /* =========================================================
     *  TEST: Validation fails without required fields
     * ========================================================= */
    public function testValidationFailsWithoutAction()
    {
        $model = new ActivityLog();

        $this->assertFalse($model->validate(), 'Model should not validate');
        $this->assertArrayHasKey('action', $model->errors);
    }

    /* =========================================================
     *  TEST: Validation passes with correct data
     * ========================================================= */
    public function testValidationPassesWithValidData()
    {
        $model = new ActivityLog([
            'action'     => 'Task Created',
            'details'    => 'New task added',
            'user_id'    => 1,
            'team_id'    => 1,
            'board_id'   => 1,
            'created_at'=> time(),
        ]);

        $this->assertTrue($model->validate());
    }

    /* =========================================================
     *  TEST: Save Activity Log
     * ========================================================= */
    public function testSaveActivityLog()
{
    /* =========================
     * CREATE USER
     * ========================= */
    $user = new User();
    $user->username   = 'testuser_' . uniqid();
    $user->email      = uniqid() . '@example.com';
    $user->setPassword('password123');
    $user->generateAuthKey();
    $user->status     = User::STATUS_ACTIVE;
    $user->created_at = time();
    $user->updated_at = time();

    $this->assertTrue(
        $user->save(),
        'User save failed: ' . json_encode($user->errors)
    );

    // 🔥 THIS LINE FIXES THE ISSUE
    \Yii::$app->user->setIdentity($user);


    /* =========================
     * CREATE TEAM
     * ========================= */
    $team = new Team();
    $team->name       = 'Test Team';
    $team->created_at = time();

    $this->assertTrue(
        $team->save(),
        'Team save failed: ' . json_encode($team->errors)
    );


    /* =========================
     * CREATE BOARD
     * ========================= */
    $board = new Board();
    $board->title       = 'Test Board';
    $board->team_id    = $team->id;
    $board->created_at = time();

    $this->assertTrue(
        $board->save(),
        'Board save failed: ' . json_encode($board->errors)
    );


    /* =========================
     * CREATE ACTIVITY LOG
     * ========================= */
    $log = new ActivityLog([
        'action'     => 'Task Updated',
        'details'    => 'Title changed',
        'user_id'    => $user->id,
        'team_id'    => $team->id,
        'board_id'   => $board->id,
        'created_at' => time(),
    ]);

    $this->assertTrue(
        $log->save(),
        'ActivityLog save failed: ' . json_encode($log->errors)
    );

    $this->assertNotNull($log->id);
}




    /* =========================================================
     *  TEST: User Relation
     * ========================================================= */
    public function testUserRelation()
{
    $user = new User();
    $user->username   = 'reluser_' . uniqid();
    $user->email      = uniqid() . '@example.com';
    $user->setPassword('password123');
    $user->generateAuthKey();
    $user->status     = User::STATUS_ACTIVE;
    $user->created_at = time();
    $user->updated_at = time();
    $this->assertTrue($user->save());

    \Yii::$app->user->setIdentity($user);

    $team = new Team();
    $team->name       = 'Relation Team';
    $team->created_at = time();
    $this->assertTrue($team->save());

    $board = new Board();
    $board->title     = 'Relation Board'; // adjust to your column
    $board->team_id   = $team->id;
    $board->created_at = time();
    $this->assertTrue($board->save());

    $log = new ActivityLog([
        'action'     => 'Relation Test',
        'user_id'    => $user->id,
        'team_id'    => $team->id,
        'board_id'   => $board->id,
        'created_at' => time(),
    ]);
    $this->assertTrue($log->save());

    // ✅ REAL ASSERTION (no skip)
    $this->assertInstanceOf(User::class, $log->user);
}


    /* =========================================================
     *  TEST: Team Relation
     * ========================================================= */
    public function testTeamRelation()
{
    // USER
    $user = new User();
    $user->username   = 'teamrel_' . uniqid();
    $user->email      = uniqid() . '@example.com';
    $user->setPassword('password123');
    $user->generateAuthKey();
    $user->status     = User::STATUS_ACTIVE;
    $user->created_at = time();
    $user->updated_at = time();
    $this->assertTrue($user->save());

    \Yii::$app->user->setIdentity($user);

    // TEAM
    $team = new Team();
    $team->name       = 'Team Relation';
    $team->created_at = time();
    $this->assertTrue($team->save());

    // ACTIVITY LOG
    $log = new ActivityLog([
        'action'     => 'Team relation test',
        'team_id'    => $team->id,
        'user_id'    => $user->id,
        'created_at' => time(),
    ]);
    $this->assertTrue($log->save());

    // ✅ ASSERT
    $this->assertInstanceOf(Team::class, $log->team);
}

    /* =========================================================
     *  TEST: Board Relation
     * ========================================================= */
    public function testBoardRelation()
{
    // USER
    $user = new User();
    $user->username   = 'boardrel_' . uniqid();
    $user->email      = uniqid() . '@example.com';
    $user->setPassword('password123');
    $user->generateAuthKey();
    $user->status     = User::STATUS_ACTIVE;
    $user->created_at = time();
    $user->updated_at = time();
    $this->assertTrue($user->save());

    \Yii::$app->user->setIdentity($user);

    // TEAM
    $team = new Team();
    $team->name       = 'Board Relation Team';
    $team->created_at = time();
    $this->assertTrue($team->save());

    // BOARD
    $board = new Board();
    $board->title     = 'Board Relation';
    $board->team_id   = $team->id;
    $board->created_at = time();
    $this->assertTrue($board->save());

    // ACTIVITY LOG
    $log = new ActivityLog([
        'action'     => 'Board relation test',
        'board_id'   => $board->id,
        'team_id'    => $team->id,
        'user_id'    => $user->id,
        'created_at' => time(),
    ]);
    $this->assertTrue($log->save());

    // ✅ ASSERT
    $this->assertInstanceOf(Board::class, $log->board);
}


    /* =========================================================
     *  TEST: Action length validation
     * ========================================================= */
    public function testActionMaxLength()
    {
        $model = new ActivityLog([
            'action' => str_repeat('a', 300),
        ]);

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('action', $model->errors);
    }
}
