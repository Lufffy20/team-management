<?php

use yii\db\Migration;

class m251204_105111_add_user_id_to_kanban_columns_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add user_id in existing table
        $this->addColumn('kanban_columns', 'user_id', $this->integer()->notNull()->after('board_id'));

        // Update existing rows → default set current board owner = 1 (CHANGE LATER IF NEEDED)
        $this->update('kanban_columns', ['user_id' => 1]);

        // If you want index for faster queries
        $this->createIndex('idx-kanban_columns-user_id', 'kanban_columns', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('kanban_columns', 'user_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251204_105111_add_user_id_to_kanban_columns_table cannot be reverted.\n";

        return false;
    }
    */
}
