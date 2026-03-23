<?php

use yii\db\Migration;

class m251208_044408_add_team_id_to_board_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('board','team_id',$this->integer()->notNull());

        $this->addForeignKey(
            'fk_board_team',
            'board',
            'team_id',
            'team',
            'id',
            'CASCADE'
        );
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_board_team','board');
        $this->dropColumn('board','team_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251208_044408_add_team_id_to_board_table cannot be reverted.\n";

        return false;
    }
    */
}
