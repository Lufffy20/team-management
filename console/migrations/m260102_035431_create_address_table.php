<?php

use yii\db\Migration;

class m260102_035431_create_address_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%address}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'address' => $this->string()->notNull(),
            'city' => $this->string()->notNull(),
            'state' => $this->string()->null(),
            'pincode' => $this->string(10)->notNull(),
            'created_at' => $this->integer(),
        ]);

        
        $this->createIndex(
            'idx-address-user_id',
            '{{%address}}',
            'user_id'
        );

        
        $this->addForeignKey(
            'fk-address-user_id',
            '{{%address}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-address-user_id', '{{%address}}');
        $this->dropIndex('idx-address-user_id', '{{%address}}');
        $this->dropTable('{{%address}}');
    }
}
