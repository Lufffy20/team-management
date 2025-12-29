<?php

use yii\db\Migration;

class m251204_101643_add_user_id_to_board_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->addColumn('board', 'user_id', $this->integer()->notNull()->after('id'));

    // 🟢 Optional: assign old boards to admin(1) or current user
    $this->update('board', ['user_id' => 1]);
}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
{
    $this->dropColumn('board', 'user_id');
}

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251204_101643_add_user_id_to_board_table cannot be reverted.\n";

        return false;
    }
    */
}
