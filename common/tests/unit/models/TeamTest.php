<?php

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\Team;
use common\models\User;
use common\models\TeamMembers;
use Yii;

class TeamTest extends Unit
{
    protected function _before()
    {
        TeamMembers::deleteAll();
        Team::deleteAll();
        User::deleteAll();
    }

    /* =====================================
     * HELPER
     * ===================================== */

    protected function createUser()
    {
        $user = new User([
            'username' => 'user_' . uniqid(),
            'email' => uniqid() . '@test.com',
            'password_hash' => Yii::$app->security->generatePasswordHash('password'),
        ]);
        $user->save(false);
        return $user;
    }

    /* =====================================
     * VALIDATION TESTS
     * ===================================== */

    public function testValidationFailsWithoutName()
    {
        $team = new Team([
            'created_by' => 1
        ]);

        $this->assertFalse($team->validate());
        $this->assertArrayHasKey('name', $team->errors);
    }

    public function testValidationPassesWithNameOnly()
    {
        $team = new Team([
            'name' => 'Test Team',
            'created_by' => 1
        ]);

        $this->assertTrue($team->validate());
    }

    public function testNameMaxLength()
    {
        $team = new Team([
            'name' => str_repeat('a', 256),
            'created_by' => 1
        ]);

        $this->assertFalse($team->validate());
        $this->assertArrayHasKey('name', $team->errors);
    }

    /* =====================================
     * TIMESTAMP BEHAVIOR
     * ===================================== */

    public function testTimestampIsSetOnSave()
{
    $user = $this->createUser();

    $team = new Team([
        'name' => 'Timestamp Team',
        'created_by' => $user->id,
    ]);

    // 🔥 ONLY disable blameable
    $team->detachBehavior('blameable');

    $this->assertTrue($team->save(false));
    $this->assertNotNull($team->created_at);
    $this->assertIsInt($team->created_at);
}


    /* =====================================
     * RELATIONS
     * ===================================== */

    public function testCreatorRelation()
{
    $user = $this->createUser();

    $team = new Team([
        'name' => 'Creator Team',
        'created_by' => $user->id
    ]);

    $team->detachBehavior('blameable');
    $team->save(false);

    $this->assertNotNull($team->creator);
    $this->assertEquals($user->id, $team->creator->id);
}


    public function testMembersRelation()
{
    $user = $this->createUser();

    $team = new Team([
        'name' => 'Members Team',
        'created_by' => $user->id
    ]);

    // 🔥 MISSING LINE (ADD THIS)
    $team->detachBehavior('blameable');

    $team->save(false);

    $member = new TeamMembers([
        'team_id' => $team->id,
        'user_id' => $user->id,
        'role' => 'member'
    ]);
    $member->save(false);

    $this->assertCount(1, $team->members);
}


    public function testUsersViaMembersRelation()
{
    $user = $this->createUser();

    $team = new Team([
        'name' => 'Via Team',
        'created_by' => $user->id
    ]);

    // 🔥 ADD THIS
    $team->detachBehavior('blameable');

    $team->save(false);

    $member = new TeamMembers([
        'team_id' => $team->id,
        'user_id' => $user->id,
        'role' => 'manager'
    ]);
    $member->save(false);

    $this->assertCount(1, $team->users);
}

    /* =====================================testTeamMembersList
     * CUSTOM METHOD
     * ===================================== */

   public function testTeamMembersList()
{
    $user = $this->createUser();

    $team = new Team([
        'name' => 'List Team',
        'created_by' => $user->id
    ]);

    // 🔥 ADD THIS
    $team->detachBehavior('blameable');

    $team->save(false);

    $member = new TeamMembers([
        'team_id' => $team->id,
        'user_id' => $user->id,
        'role' => 'manager'
    ]);
    $member->save(false);

    $list = $team->getTeamMembersList();

    $this->assertArrayHasKey($user->id, $list);
}

}
