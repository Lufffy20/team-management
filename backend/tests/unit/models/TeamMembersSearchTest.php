<?php

namespace backend\tests\unit\models;

use backend\models\TeamMembersSearch;
use common\models\TeamMembers;
use common\models\Team;
use common\models\User;
use Codeception\Test\Unit;
use Yii;

class TeamMembersSearchTest extends Unit
{
    protected function _before()
{
    // USER
    $user = new \common\models\User([
        'username' => 'john_doe',
        'email' => 'john@example.com',
        'password_hash' => Yii::$app->security->generatePasswordHash('password'),
        'auth_key' => 'testkey',
        'role' => 1,
        'status' => 10,
        'created_at' => time(),
        'updated_at' => time(),
    ]);
    $this->assertTrue($user->save(false));

    // 🔥 MOCK LOGGED-IN USER
    Yii::$app->set('user', [
        'class' => \yii\web\User::class,
        'identityClass' => \common\models\User::class,
        'enableSession' => false,
    ]);
    Yii::$app->user->setIdentity($user);

    // TEAM (created_by auto-filled correctly)
    $team = new \common\models\Team([
        'name' => 'Alpha Team',
        'created_at' => time(),
    ]);
    $this->assertTrue($team->save(false));

    // TEAM MEMBER
    $member = new \common\models\TeamMembers([
        'team_id' => $team->id,
        'user_id' => $user->id,
        'role' => 'admin',
    ]);
    $this->assertTrue($member->save(false));
}

    /** ✅ Search without filters */
    public function testSearchReturnsAll()
    {
        $search = new TeamMembersSearch();
        $dataProvider = $search->search([]);

        $this->assertGreaterThan(0, $dataProvider->getTotalCount());
    }

    /** ✅ Search by team name */
    public function testSearchByTeamName()
    {
        $search = new TeamMembersSearch();

        $dataProvider = $search->search([
            'TeamMembersSearch' => [
                'team_name' => 'Alpha',
            ],
        ]);

        $this->assertEquals(1, $dataProvider->getTotalCount());
    }

    /** ✅ Search by username */
    public function testSearchByUsername()
    {
        $search = new TeamMembersSearch();

        $dataProvider = $search->search([
            'TeamMembersSearch' => [
                'username' => 'john',
            ],
        ]);

        $this->assertEquals(1, $dataProvider->getTotalCount());
    }

    /** ✅ Search by role */
    public function testSearchByRole()
    {
        $search = new TeamMembersSearch();

        $dataProvider = $search->search([
            'TeamMembersSearch' => [
                'role' => 'admin',
            ],
        ]);

        $this->assertEquals(1, $dataProvider->getTotalCount());
    }

    /** ❌ Invalid data should return unfiltered results */
    public function testInvalidDataReturnsAll()
    {
        $search = new TeamMembersSearch();

        $dataProvider = $search->search([
            'TeamMembersSearch' => [
                'id' => 'invalid',
            ],
        ]);

        $this->assertGreaterThan(0, $dataProvider->getTotalCount());
    }
}
