<?php

use yii\db\Migration;

class m251229_050416_add_access_token_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->addColumn('{{%user}}', 'access_token', $this->string(64)->null()->unique());
}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
{
    $this->dropColumn('{{%user}}', 'access_token');
}

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251229_050416_add_access_token_to_user cannot be reverted.\n";

        return false;
    }
    */
}
