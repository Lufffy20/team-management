<?php

use yii\db\Migration;

class m251218_112944_add_last_reminder_at_to_task extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->addColumn('{{%task}}', 'last_reminder_at', $this->integer()->null());
}

    /**
     * {@inheritdoc}
     */
    
public function safeDown()
{
    $this->dropColumn('{{%task}}', 'last_reminder_at');
}
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251218_112944_add_last_reminder_at_to_task cannot be reverted.\n";

        return false;
    }
    */
}
