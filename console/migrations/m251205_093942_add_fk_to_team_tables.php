<?php

use yii\db\Migration;

class m251205_093942_add_fk_to_team_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->addForeignKey('fk_team_created_by','team','created_by','user','id');
    $this->addForeignKey('fk_team_member_team','team_members','team_id','team','id');
    $this->addForeignKey('fk_team_member_user','team_members','user_id','user','id');
}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
{
    $this->dropForeignKey('fk_team_created_by','team');
    $this->dropForeignKey('fk_team_member_team','team_members');
    $this->dropForeignKey('fk_team_member_user','team_members');
}

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251205_093942_add_fk_to_team_tables cannot be reverted.\n";

        return false;
    }
    */
}
