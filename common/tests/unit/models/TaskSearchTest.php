<?php

namespace common\tests\unit\models;

use common\models\Task;
use common\models\TaskSearch;
use common\tests\UnitTester;

class TaskSearchTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    protected function _before()
    {
        // Create sample tasks
        Task::deleteAll();

        $task1 = new Task([
            'title' => 'First Task',
            'description' => 'Test description',
            'status' => 'open',
            'priority' => 'high',
            'team_id' => 1,
            'board_id' => 1,
        ]);
        $task1->save(false);

        $task2 = new Task([
            'title' => 'Second Task',
            'description' => 'Another description',
            'status' => 'completed',
            'priority' => 'low',
            'team_id' => 1,
            'board_id' => 1,
        ]);
        $task2->save(false);
    }

    /** ✅ Search without filters */
    public function testSearchWithoutFilters()
    {
        $searchModel = new TaskSearch();
        $dataProvider = $searchModel->search([]);

        $this->assertCount(2, $dataProvider->getModels());
    }

    /** ✅ Search by title */
    public function testSearchByTitle()
    {
        $searchModel = new TaskSearch();
        $dataProvider = $searchModel->search([
            'TaskSearch' => [
                'title' => 'First',
            ],
        ]);

        $models = $dataProvider->getModels();

        $this->assertCount(1, $models);
        $this->assertEquals('First Task', $models[0]->title);
    }

    /** ✅ Search by status (dropdown exact match) */
    public function testSearchByStatus()
    {
        $searchModel = new TaskSearch();
        $dataProvider = $searchModel->search([
            'TaskSearch' => [
                'status' => 'completed',
            ],
        ]);

        $models = $dataProvider->getModels();

        $this->assertCount(1, $models);
        $this->assertEquals('completed', $models[0]->status);
    }

    /** ✅ Search by priority */
    public function testSearchByPriority()
    {
        $searchModel = new TaskSearch();
        $dataProvider = $searchModel->search([
            'TaskSearch' => [
                'priority' => 'high',
            ],
        ]);

        $models = $dataProvider->getModels();

        $this->assertCount(1, $models);
        $this->assertEquals('high', $models[0]->priority);
    }

    /** ✅ Team & Board filter */
    public function testSearchByTeamAndBoard()
    {
        $searchModel = new TaskSearch();
        $searchModel->team_id = 1;
        $searchModel->board_id = 1;

        $dataProvider = $searchModel->search([]);

        $this->assertCount(2, $dataProvider->getModels());
    }

    /** ✅ Pagination test (per-page) */
    public function testPagination()
    {
        $searchModel = new TaskSearch();
        $dataProvider = $searchModel->search([
            'per-page' => 1,
        ]);

        $this->assertEquals(1, $dataProvider->pagination->pageSize);
        $this->assertCount(1, $dataProvider->getModels());
    }

    /** Invalid filter should not crash */
    public function testInvalidFilter()
    {
        $searchModel = new TaskSearch();
        $dataProvider = $searchModel->search([
            'TaskSearch' => [
                'id' => 'invalid',
            ],
        ]);

        // Validation fails but data provider still returns
        $this->assertNotNull($dataProvider);
    }
}
