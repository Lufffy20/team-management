<?php

use yii\db\Migration;

class m251204_110351_remove_user_id_from_board_table extends Migration
{
    /**
     * {@inheritdoc}
     */
   public function safeUp() {
    $this->dropColumn('board', 'user_id');
}

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
    $this->addColumn('board', 'user_id', $this->integer());
}

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251204_110351_remove_user_id_from_board_table cannot be reverted.\n";

        return false;
    }
    */
}
