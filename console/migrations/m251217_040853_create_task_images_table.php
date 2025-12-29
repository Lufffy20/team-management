<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_images}}`.
 */
class m251217_040853_create_task_images_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->createTable('{{%task_images}}', [
        'id' => $this->primaryKey(),
        'task_id' => $this->integer()->notNull(),
        'image' => $this->string()->notNull(),
        'created_at' => $this->integer(),
    ]);

    $this->addForeignKey(
        'fk-task-images-task',
        '{{%task_images}}',
        'task_id',
        '{{%task}}',
        'id',
        'CASCADE'
    );
}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task_images}}');
    }
}
