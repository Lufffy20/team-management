<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%task}}`.
 */
class m251217_035100_add_image_column_to_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
     public function safeUp()
    {
        $this->addColumn('{{%task}}', 'image', $this->string(255)->null()->after('description'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%task}}', 'image');
    }
}
