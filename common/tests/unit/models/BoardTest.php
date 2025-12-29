<?php

namespace common\tests\unit\models;

use Yii;
use Codeception\Test\Unit;
use common\models\Board;
use common\models\User;
use common\models\Team;
use common\models\Task;
use common\models\TeamMembers;

class BoardTest extends Unit
{
    protected User $loggedUser;

    protected function _before()
    {
        Task::deleteAll();
        TeamMembers::deleteAll();
        Board::deleteAll();
        Team::deleteAll();
        User::deleteAll();

        // 🔥 CREATE & LOGIN FAKE USER (IMPORTANT FOR BlameableBehavior)
        $this->loggedUser = new User([
            'username' => 'testuser_' . uniqid(),
            'email' => uniqid() . '@test.com',
            'password_hash' => Yii::$app->security->generatePasswordHash('123456'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'status' => User::STATUS_ACTIVE,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->loggedUser->save(false);

        // simulate logged-in user
        Yii::$app->user->setIdentity($this->loggedUser);
    }

    /* =========================
     * HELPERS
     * ========================= */

    private function createTeam(): Team
    {
        $team = new Team();
        $team->name = 'Test Team ' . uniqid();
        // ❌ created_by manually set mat karo
        // ✔ BlameableBehavior auto set karega
        $team->save(false);

        return $team;
    }

    private function createBoard(Team $team): Board
    {
        $board = new Board();
        $board->title   = 'Test Board ' . uniqid();
        $board->team_id = $team->id;   // REQUIRED
        $board->save(false);

        return $board;
    }

    /* =========================
     * VALIDATION TESTS
     * ========================= */

    public function testValidationFailsWithoutTitle()
    {
        $team = $this->createTeam();

        $board = new Board([
            'team_id' => $team->id,
        ]);

        $this->assertFalse($board->validate());
        $this->assertArrayHasKey('title', $board->errors);
    }

    public function testValidationPassesWithValidData()
    {
        $team = $this->createTeam();

        $board = new Board([
            'title'   => 'Valid Board',
            'team_id' => $team->id,
        ]);

        $this->assertTrue($board->validate());
    }

    /* =========================
     * RELATION TESTS
     * ========================= */

    public function testTeamRelation()
    {
        $team = $this->createTeam();
        $board = $this->createBoard($team);

        $this->assertInstanceOf(Team::class, $board->team);
    }

    public function testCreatedByRelation()
{
    $team = $this->createTeam();

    $board = new Board([
        'title'      => 'Board With Creator',
        'team_id'    => $team->id,
        'created_by' => $this->loggedUser->id, // 👈 IMPORTANT
    ]);
    $board->save(false);

    $this->assertInstanceOf(User::class, $board->createdBy);
    $this->assertEquals($this->loggedUser->id, $board->createdBy->id);
}


    public function testTasksRelation()
    {
        $team = $this->createTeam();
        $board = $this->createBoard($team);

        $task = new Task([
            'title'      => 'Test Task',
            'board_id'   => $board->id,
            'team_id'    => $team->id,
            'created_by' => $this->loggedUser->id,
        ]);
        $task->save(false);

        $this->assertCount(1, $board->tasks);
    }

    /* =========================
     * CUSTOM METHOD TESTS
     * ========================= */

    public function testGetTeamMembersList()
    {
        $team = $this->createTeam();
        $board = $this->createBoard($team);

        $member = new TeamMembers();
        $member->team_id = $team->id;
        $member->user_id = $this->loggedUser->id;
        $member->role = 'member';
        $member->save(false);

        $list = $board->getTeamMembersList();

        $this->assertArrayHasKey($this->loggedUser->id, $list);
    }

    public function testGetUserRole()
    {
        $team = $this->createTeam();
        $board = $this->createBoard($team);

        $member = new TeamMembers();
        $member->team_id = $team->id;
        $member->user_id = $this->loggedUser->id;
        $member->role = 'admin';
        $member->save(false);

        $this->assertEquals(
            'admin',
            $board->getUserRole($this->loggedUser->id)
        );
    }

    public function testGetUserRoleReturnsGuest()
    {
        $team = $this->createTeam();
        $board = $this->createBoard($team);

        $this->assertEquals(
            'guest',
            $board->getUserRole(999999)
        );
    }
}
