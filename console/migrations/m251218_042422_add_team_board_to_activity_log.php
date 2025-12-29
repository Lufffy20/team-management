<?php

use yii\db\Migration;

class m251218_042422_add_team_board_to_activity_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'activity_log',
            'team_id',
            $this->integer()->after('user_id')
        );

        $this->addColumn(
            'activity_log',
            'board_id',
            $this->integer()->after('team_id')
        );

        // Optional indexes (recommended)
        $this->createIndex(
            'idx-activity_log-team_id',
            'activity_log',
            'team_id'
        );

        $this->createIndex(
            'idx-activity_log-board_id',
            'activity_log',
            'board_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-activity_log-team_id', 'activity_log');
        $this->dropIndex('idx-activity_log-board_id', 'activity_log');

        $this->dropColumn('activity_log', 'team_id');
        $this->dropColumn('activity_log', 'board_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251218_042422_add_team_board_to_activity_log cannot be reverted.\n";

        return false;
    }
    */
}
