<?php

namespace common\tests\unit\models;

use common\models\User;
use common\models\UserSearch;
use yii\data\ActiveDataProvider;

class UserSearchTest extends \Codeception\Test\Unit
{
    protected function _before()
    {
        // Create dummy users for search testing
        $user1 = new User([
            'username' => 'john_doe',
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 1,
            'status' => 10,
            'password_hash' => 'hash',
            'auth_key' => 'key1',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $user1->save(false);

        $user2 = new User([
            'username' => 'jane_smith',
            'email' => 'jane@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'role' => 2,
            'status' => 10,
            'password_hash' => 'hash',
            'auth_key' => 'key2',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $user2->save(false);
    }

    public function testSearchReturnsDataProvider()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search([]);

        $this->assertInstanceOf(ActiveDataProvider::class, $dataProvider);
    }

    public function testSearchByUsername()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search([
            'UserSearch' => [
                'username' => 'john',
            ]
        ]);

        $models = $dataProvider->getModels();

        $this->assertCount(1, $models);
        $this->assertEquals('john_doe', $models[0]->username);
    }

    public function testSearchByEmail()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search([
            'UserSearch' => [
                'email' => 'jane@example.com',
            ]
        ]);

        $models = $dataProvider->getModels();

        $this->assertCount(1, $models);
        $this->assertEquals('jane_smith', $models[0]->username);
    }

    public function testSearchByRole()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search([
            'UserSearch' => [
                'role' => 1,
            ]
        ]);

        $models = $dataProvider->getModels();

        $this->assertCount(1, $models);
        $this->assertEquals(1, $models[0]->role);
    }

    public function testInvalidSearchReturnsAllRecords()
    {
        $searchModel = new UserSearch();

        // invalid type for integer field
        $dataProvider = $searchModel->search([
            'UserSearch' => [
                'id' => 'invalid',
            ]
        ]);

        $models = $dataProvider->getModels();

        // validation fail → no filter applied
        $this->assertGreaterThanOrEqual(2, count($models));
    }

    protected function _after()
    {
        User::deleteAll();
    }
}
