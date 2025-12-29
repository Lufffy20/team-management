<?php

namespace common\tests\unit;

use frontend\models\TaskSearchFrontend;
use common\models\Task;
use Codeception\Test\Unit;

class TaskSearchFrontendTest extends Unit
{
    /**
     * Test: empty search returns ActiveDataProvider
     */
    public function testSearchReturnsDataProvider()
    {
        $searchModel = new TaskSearchFrontend();

        $dataProvider = $searchModel->search([]);

        $this->assertInstanceOf(
            \yii\data\ActiveDataProvider::class,
            $dataProvider
        );
    }

    /**
     * Test: search by title filter
     */
    public function testSearchByTitle()
    {
        // Create dummy task
        $task = new Task([
            'title' => 'Frontend Test Task',
            'description' => 'Testing frontend search',
            'status' => 'open',
            'priority' => 'high',
        ]);
        $task->save(false);

        $searchModel = new TaskSearchFrontend();

        $dataProvider = $searchModel->search([
            'TaskSearchFrontend' => [
                'title' => 'Frontend Test Task',
            ],
        ]);

        $models = $dataProvider->getModels();

        $this->assertNotEmpty($models);
        $this->assertEquals('Frontend Test Task', $models[0]->title);
    }

    /**
     * Test: search by team_id filter
     */
    public function testSearchByTeamId()
    {
        $task = new Task([
            'title' => 'Team Task',
            'team_id' => 5,
            'status' => 'open',
        ]);
        $task->save(false);

        $searchModel = new TaskSearchFrontend();

        $dataProvider = $searchModel->search([
            'TaskSearchFrontend' => [
                'team_id' => 5,
            ],
        ]);

        $models = $dataProvider->getModels();

        $this->assertNotEmpty($models);
        $this->assertEquals(5, $models[0]->team_id);
    }

    /**
     * Test: invalid data does not break search
     */
    public function testSearchWithInvalidData()
    {
        $searchModel = new TaskSearchFrontend();

        $dataProvider = $searchModel->search([
            'TaskSearchFrontend' => [
                'id' => 'invalid-id',
            ],
        ]);

        $this->assertInstanceOf(
            \yii\data\ActiveDataProvider::class,
            $dataProvider
        );
    }
}
