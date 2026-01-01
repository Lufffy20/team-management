<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%payments}}`.
 */
class m260101_085931_create_payments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%payments}}', [
            'id' => $this->primaryKey(),

            'user_id' => $this->integer()->notNull(),

            'stripe_session_id' => $this->string(255)->notNull(),
            'stripe_payment_intent' => $this->string(255)->null(),

            'amount' => $this->integer()->notNull(), // cents
            'currency' => $this->string(10)->notNull(),

            'status' => $this->string(50)->notNull(),

            'created_at' => $this->dateTime()->notNull(),
        ]);

        //  Indexes
        $this->createIndex(
            'idx-payments-user_id',
            '{{%payments}}',
            'user_id'
        );

        $this->createIndex(
            'idx-payments-session_id',
            '{{%payments}}',
            'stripe_session_id',
            true // unique
        );

        //  Foreign key
        $this->addForeignKey(
            'fk-payments-user_id',
            '{{%payments}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-payments-user_id', '{{%payments}}');
        $this->dropTable('{{%payments}}');
    }
}
