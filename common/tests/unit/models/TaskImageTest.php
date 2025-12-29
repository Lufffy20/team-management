<?php

namespace common\tests\unit\models;

use common\models\TaskImage;
use common\models\Task;
use Codeception\Test\Unit;

class TaskImageTest extends Unit
{
    protected function _before()
    {
        // optional setup
    }

    protected function _after()
    {
        // optional cleanup
    }

    /** ❌ Validation should fail without required fields */
    public function testValidationFailsWithoutRequiredFields()
    {
        $model = new TaskImage();

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('task_id', $model->errors);
        $this->assertArrayHasKey('image', $model->errors);
    }

    /** ❌ task_id must be integer */
    public function testValidationFailsWithNonIntegerTaskId()
    {
        $model = new TaskImage([
            'task_id' => 'abc',
            'image'   => 'image.png',
        ]);

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('task_id', $model->errors);
    }

    /** ❌ image max length validation */
    public function testImageMaxLength()
    {
        $model = new TaskImage([
            'task_id' => 1,
            'image'   => str_repeat('a', 300),
        ]);

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('image', $model->errors);
    }

    /** ✅ Validation passes with correct data */
    public function testValidationPassesWithValidData()
    {
        $model = new TaskImage([
            'task_id' => 1,
            'image'   => 'task_image.png',
        ]);

        $this->assertTrue($model->validate());
    }

    /** 🔗 Task relation test */
    public function testTaskRelation()
    {
        $task = new Task([
            'title'       => 'Test Task',
            'description' => 'Task for image test',
            'status'      => 'todo',
            'created_at'  => time(),
        ]);
        $this->assertTrue($task->save(false));

        $image = new TaskImage([
            'task_id' => $task->id,
            'image'   => 'test.png',
        ]);
        $this->assertTrue($image->save(false));

        $this->assertInstanceOf(Task::class, $image->task);
        $this->assertEquals($task->id, $image->task->id);
    }
}
