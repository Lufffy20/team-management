<?php

use yii\db\Migration;

class m251216_061442_add_sort_order_to_task extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'task',
            'sort_order',
            $this->integer()->defaultValue(0)->after('status')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('task', 'sort_order');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251216_061442_add_sort_order_to_task cannot be reverted.\n";

        return false;
    }
    */
}
