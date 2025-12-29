<?php

namespace common\tests\unit\models;

use Yii;
use Codeception\Test\Unit;
use common\models\BoardMembers;
use common\models\Board;
use common\models\User;
use common\models\Team;

class BoardMembersTest extends Unit
{
    protected function _before()
    {
        // Clean tables (FK order)
        BoardMembers::deleteAll();
        Board::deleteAll();
        Team::deleteAll();
        User::deleteAll();

        // Logout user if any
        Yii::$app->user->logout();
    }

    /* =====================================
     * VALIDATION TESTS
     * ===================================== */

    public function testValidationFailsWithoutRequiredFields()
    {
        $model = new BoardMembers();

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('board_id', $model->errors);
        $this->assertArrayHasKey('user_id', $model->errors);
    }

    public function testValidationFailsWithNonIntegerValues()
    {
        $model = new BoardMembers([
            'board_id' => 'abc',
            'user_id'  => 'xyz',
        ]);

        $this->assertFalse($model->validate());
    }

    public function testValidationPassesWithValidData()
    {
        $model = new BoardMembers([
            'board_id' => 1,
            'user_id'  => 1,
        ]);

        $this->assertTrue($model->validate());
    }

    /* =====================================
     * RELATION TESTS
     * ===================================== */

    public function testBoardRelation()
    {
        // Create user
        $user = new User([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => 'hash',
            'auth_key' => 'key',
        ]);
        $user->save(false);

        // 🔑 Fake login (for BlameableBehavior)
        Yii::$app->user->setIdentity($user);

        // Create team
        $team = new Team([
            'name' => 'Test Team',
        ]);
        $team->save(false);

        // Create board
        $board = new Board([
            'title' => 'Test Board',
            'team_id' => $team->id,
        ]);
        $board->save(false);

        // Create board member
        $member = new BoardMembers([
            'board_id' => $board->id,
            'user_id'  => $user->id,
        ]);
        $member->save(false);

        $this->assertInstanceOf(Board::class, $member->board);
        $this->assertEquals($board->id, $member->board->id);
    }

    public function testUserRelation()
    {
        // Create user
        $user = new User([
            'username' => 'memberuser',
            'email' => 'member@example.com',
            'password_hash' => 'hash',
            'auth_key' => 'key',
        ]);
        $user->save(false);

        // 🔑 Fake login
        Yii::$app->user->setIdentity($user);

        // Create team
        $team = new Team([
            'name' => 'Another Team',
        ]);
        $team->save(false);

        // Create board
        $board = new Board([
            'title' => 'Another Board',
            'team_id' => $team->id,
        ]);
        $board->save(false);

        // Create board member
        $member = new BoardMembers([
            'board_id' => $board->id,
            'user_id'  => $user->id,
        ]);
        $member->save(false);

        $this->assertInstanceOf(User::class, $member->user);
        $this->assertEquals($user->id, $member->user->id);
    }
}
