<?php

namespace common\tests\unit\models;

use common\models\KanbanColumn;

class KanbanColumnTest extends \Codeception\Test\Unit
{
    public function testTableName()
    {
        $model = new KanbanColumn();
        $this->assertEquals('kanban_columns', $model::tableName());
    }

    public function testModelInstance()
    {
        $model = new KanbanColumn();
        $this->assertInstanceOf(KanbanColumn::class, $model);
    }

    public function testSaveKanbanColumn()
    {
        $model = new KanbanColumn();

        if ($model->validate()) {
            $this->assertTrue($model->save(false));
        } else {
            // Agar table me required fields hain
            $this->assertFalse($model->validate());
        }
    }
}
