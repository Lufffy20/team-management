<?php

use yii\db\Migration;

class m251202_105606_add_position_to_task extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->addColumn('{{%task}}', 'position', $this->integer()->defaultValue(0));
}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
{
    $this->dropColumn('{{%task}}', 'position');
}

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251202_105606_add_position_to_task cannot be reverted.\n";

        return false;
    }
    */
}
