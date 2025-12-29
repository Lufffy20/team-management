<?php

use yii\db\Migration;

class m251204_101828_add_user_id_to_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->addColumn('task', 'user_id', $this->integer()->notNull()->after('id'));
}

    /**
     * {@inheritdoc}
     */
   public function safeDown()
{
    $this->dropColumn('task', 'user_id');
}

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251204_101828_add_user_id_to_task_table cannot be reverted.\n";

        return false;
    }
    */
}
