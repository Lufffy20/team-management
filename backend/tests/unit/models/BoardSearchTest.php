<?php

namespace backend\tests\unit\models;

use backend\models\BoardSearch;
use common\models\Board;
use common\models\Team;
use common\models\User;
use Yii;

class BoardSearchTest extends \Codeception\Test\Unit
{
    protected function _before()
{
    // USER
    $user = new User([
        'username' => 'admin_user',
        'email' => 'admin@test.com',
        'password_hash' => Yii::$app->security->generatePasswordHash('123456'),
        'auth_key' => Yii::$app->security->generateRandomString(),
        'status' => 10,
        'created_at' => time(),
        'updated_at' => time(),
    ]);
    $user->save(false);

    Yii::$app->user->setIdentity($user);

    // TEAM
    $team = new Team([
        'name' => 'Dev Team',
        'created_at' => time(),
    ]);
    $team->save(false);

    // BOARD  ✅ created_by FORCE SET
    $board = new Board([
        'title' => 'Sprint Board',
        'description' => 'Testing board search',
        'team_id' => $team->id,
        'created_by' => $user->id,
        'created_at' => time(),
    ]);
    $board->save(false);
}


    public function testSearchReturnsAllRecordsWithoutFilters()
    {
        $searchModel = new BoardSearch();
        $dataProvider = $searchModel->search([]);

        $this->assertGreaterThan(0, $dataProvider->getTotalCount());
    }

    public function testSearchById()
    {
        $board = Board::find()->one();

        $searchModel = new BoardSearch();
        $dataProvider = $searchModel->search([
            'BoardSearch' => ['id' => $board->id]
        ]);

        $this->assertCount(1, $dataProvider->getModels());
    }

    public function testSearchByTeamName()
    {
        $searchModel = new BoardSearch();
        $dataProvider = $searchModel->search([
            'BoardSearch' => ['team_name' => 'Dev']
        ]);

        $this->assertGreaterThan(0, $dataProvider->getTotalCount());
    }

    public function testSearchByCreatedByUsername()
{
    $searchModel = new BoardSearch();
    $dataProvider = $searchModel->search([
        'BoardSearch' => [
            'created_by_username' => 'admin_user'
        ]
    ]);

    $this->assertGreaterThan(0, $dataProvider->getTotalCount());
}


    public function testSearchByTitle()
    {
        $searchModel = new BoardSearch();
        $dataProvider = $searchModel->search([
            'BoardSearch' => ['title' => 'Sprint']
        ]);

        $this->assertGreaterThan(0, $dataProvider->getTotalCount());
    }

    public function testSearchByCreatedAtDate()
    {
        $board = Board::find()->one();

        $searchModel = new BoardSearch();
        $dataProvider = $searchModel->search([
            'BoardSearch' => [
                'created_at' => date('Y-m-d', $board->created_at)
            ]
        ]);

        $this->assertGreaterThan(0, $dataProvider->getTotalCount());
    }

    public function testInvalidDataReturnsUnfilteredResults()
    {
        $searchModel = new BoardSearch();
        $dataProvider = $searchModel->search([
            'BoardSearch' => ['id' => 'invalid']
        ]);

        $this->assertGreaterThan(0, $dataProvider->getTotalCount());
    }


    
}
