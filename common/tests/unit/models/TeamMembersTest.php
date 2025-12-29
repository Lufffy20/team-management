<?php

namespace common\tests\unit\models;


use Yii;
use common\models\TeamMembers;
use common\models\User;
use common\models\Team;
use Codeception\Test\Unit;

class TeamMembersTest extends Unit
{
    /**
     * Validation: create scenario me email required
     */
    public function testEmailRequiredOnCreate()
    {
        $model = new TeamMembers(['scenario' => 'create']);

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('email', $model->errors);
    }

    /**
     * Validation: required fields missing
     */
    public function testValidationFailsWithoutRequiredFields()
    {
        $model = new TeamMembers();

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('team_id', $model->errors);
        $this->assertArrayHasKey('user_id', $model->errors);
        $this->assertArrayHasKey('role', $model->errors);
    }

    /**
     * Validation: integer check
     */
    public function testValidationFailsWithNonIntegerIds()
    {
        $model = new TeamMembers([
            'team_id' => 'abc',
            'user_id' => 'xyz',
            'role'    => 'member',
        ]);

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('team_id', $model->errors);
        $this->assertArrayHasKey('user_id', $model->errors);
    }

    /**
     * Email validation: user not exists
     */
    public function testEmailNotExists()
    {
        $model = new TeamMembers(['scenario' => 'create']);
        $model->email = 'notfound@example.com';

        $model->validate(['email']);

        $this->assertArrayHasKey('email', $model->errors);
    }

    /**
     * Email validation: user exists & user_id auto bind
     */
    public function testEmailExistsAndUserIdBind()
    {
        $user = new User([
            'username' => 'testuser',
            'email'    => 'test@example.com',
            'password_hash' => 'hash',
            'auth_key' => 'key',
        ]);
        $this->assertTrue($user->save(false));

        $model = new TeamMembers(['scenario' => 'create']);
        $model->email = 'test@example.com';

        $model->validate(['email']);

        $this->assertEquals($user->id, $model->user_id);
    }

    /**
     * Save model successfully
     */
    public function testSaveTeamMember()
{
    $user = new User([
        'username' => 'member1',
        'email'    => 'member@example.com',
        'password_hash' => Yii::$app->security->generatePasswordHash('123456'),
        'auth_key' => Yii::$app->security->generateRandomString(),
        'status' => User::STATUS_ACTIVE,
    ]);

    $this->assertTrue($user->save(), 'User should be saved');

    // 🔥 VERY IMPORTANT
    Yii::$app->user->setIdentity($user);

    $team = new Team([
        'name' => 'Test Team',
    ]);

    $this->assertTrue($team->save(), 'Team should be saved');

    $model = new TeamMembers([
        'team_id' => $team->id,
        'user_id' => $user->id,
        'role'    => 'admin',
    ]);

    $this->assertTrue($model->save(), 'Team member should be saved');
}


    /**
     * Relation: User
     */
    public function testUserRelation()
    {
        $model = new TeamMembers();
        $this->assertInstanceOf(
            \yii\db\ActiveQuery::class,
            $model->getUser()
        );
    }

    /**
     * Relation: Team
     */
    public function testTeamRelation()
    {
        $model = new TeamMembers();
        $this->assertInstanceOf(
            \yii\db\ActiveQuery::class,
            $model->getTeam()
        );
    }
}
