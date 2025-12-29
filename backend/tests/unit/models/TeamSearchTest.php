<?php

namespace backend\tests\unit\models;

use backend\models\TeamSearch;
use common\models\Team;
use common\models\User;
use Codeception\Test\Unit;
use Yii;

class TeamSearchTest extends Unit
{
    protected function _before()
    {
        Yii::$app->db->createCommand()->delete('team')->execute();
        Yii::$app->db->createCommand()->delete('user')->execute();

        // 🔹 User 1
        $user1 = new User();
        $user1->username = 'creator_one';
        $user1->email = 'creator1@test.com';
        $user1->password_hash = Yii::$app->security->generatePasswordHash('password');
        $user1->auth_key = Yii::$app->security->generateRandomString();
        $user1->status = 10;
        $user1->role = 10;
        $user1->created_at = time();
        $user1->updated_at = time();
        $user1->save(false);

        // 🔹 User 2
        $user2 = new User();
        $user2->username = 'creator_two';
        $user2->email = 'creator2@test.com';
        $user2->password_hash = Yii::$app->security->generatePasswordHash('password');
        $user2->auth_key = Yii::$app->security->generateRandomString();
        $user2->status = 10;
        $user2->role = 10;
        $user2->created_at = time();
        $user2->updated_at = time();
        $user2->save(false);

        // 🔥 Team 1 → creator_one
        Yii::$app->user->setIdentity($user1);
        $team1 = new Team([
            'name' => 'Alpha Team',
            'description' => 'First team',
        ]);
        $team1->save(false);
        $team1->updateAttributes([
            'created_at' => strtotime('2024-01-01'),
        ]);

        // 🔥 Team 2 → creator_two
        Yii::$app->user->setIdentity($user2);
        $team2 = new Team([
            'name' => 'Beta Team',
            'description' => 'Second team',
        ]);
        $team2->save(false);
        $team2->updateAttributes([
            'created_at' => strtotime('2024-01-02'),
        ]);
    }

    public function testSearchReturnsAllRecords()
    {
        $search = new TeamSearch();
        $dataProvider = $search->search([]);

        $this->assertEquals(2, $dataProvider->getTotalCount());
    }

    public function testSearchByName()
    {
        $search = new TeamSearch();
        $dataProvider = $search->search([
            'TeamSearch' => ['name' => 'Alpha']
        ]);

        $models = $dataProvider->getModels();
        $this->assertCount(1, $models);
        $this->assertEquals('Alpha Team', $models[0]->name);
    }

    public function testSearchByDescription()
    {
        $search = new TeamSearch();
        $dataProvider = $search->search([
            'TeamSearch' => ['description' => 'Second']
        ]);

        $models = $dataProvider->getModels();
        $this->assertCount(1, $models);
        $this->assertEquals('Beta Team', $models[0]->name);
    }

    public function testSearchByCreatedByUsername()
    {
        $search = new TeamSearch();
        $dataProvider = $search->search([
            'TeamSearch' => ['created_by_username' => 'creator_one']
        ]);

        $models = $dataProvider->getModels();
        $this->assertCount(1, $models);
        $this->assertEquals('Alpha Team', $models[0]->name);
    }

    public function testSearchByCreatedAtDate()
    {
        $search = new TeamSearch();
        $dataProvider = $search->search([
            'TeamSearch' => [
                'created_at_date' => '2024-01-02'
            ]
        ]);

        $models = $dataProvider->getModels();
        $this->assertCount(1, $models);
        $this->assertEquals('Beta Team', $models[0]->name);
    }

    public function testInvalidDataReturnsAllRecords()
    {
        $search = new TeamSearch();
        $dataProvider = $search->search([
            'TeamSearch' => ['id' => 'invalid']
        ]);

        $this->assertEquals(2, $dataProvider->getTotalCount());
    }
}
