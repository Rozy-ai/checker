<?php

use yii\db\Migration;

/**
 * Class m230324_151006_truncate_exports__saved_keys_table
 */
class m230324_151006_truncate_exports__saved_keys_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable('{{%exports__saved_keys}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230324_151006_truncate_exports__saved_keys_table cannot be reverted.\n";

        return false;
    }
    */
}
