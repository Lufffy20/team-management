<?php

use yii\db\Migration;

class m260102_102700_add_address_type_to_address_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%address}}',
            'address_type',
            $this->string(20)->notNull()->defaultValue('home')->after('user_id')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%address}}', 'address_type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260102_102700_add_address_type_to_address_table cannot be reverted.\n";

        return false;
    }
    */
}
