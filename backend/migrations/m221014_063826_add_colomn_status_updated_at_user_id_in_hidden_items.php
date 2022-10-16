<?php

use yii\db\Migration;

/**
 * Class m221014_063826_add_colomn_status_updated_at_user_id_in_hidden_items
 */
class m221014_063826_add_colomn_status_updated_at_user_id_in_hidden_items extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('hidden_items', 'status', $this->integer(2));
        $this->addColumn('hidden_items', 'user_id', $this->integer(10));
        $this->addColumn('hidden_items', 'updated_at', $this->timestamp());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('hidden_items', 'status');
        $this->dropColumn('hidden_items', 'user_id');
        $this->dropColumn('hidden_items', 'updated_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221014_063826_add_colomn_status_updated_at_user_id_in_hidden_items cannot be reverted.\n";

        return false;
    }
    */
}
