<?php

use yii\db\Migration;

class m251219_044922_add_access_token_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%user}}',
            'access_token',
            $this->string(255)->null()->after('auth_key')
        );
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
        echo "m251219_044922_add_access_token_to_user_table cannot be reverted.\n";

        return false;
    }
    */
}
