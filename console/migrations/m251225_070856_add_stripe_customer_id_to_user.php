<?php

use yii\db\Migration;

class m251225_070856_add_stripe_customer_id_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->addColumn('{{%user}}', 'stripe_customer_id', $this->string()->null());
}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
{
    $this->dropColumn('{{%user}}', 'stripe_customer_id');
}

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251225_070856_add_stripe_customer_id_to_user cannot be reverted.\n";

        return false;
    }
    */
}
