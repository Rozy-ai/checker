<?php

use yii\db\Migration;

/**
 * Class m230313_091808_modify_settings__fields_extend_price_table
 */
class m230313_091808_modify_settings__fields_extend_price_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%settings__fields_extend_price}}',
            'section',
            "ENUM('price', 'bsr') NOT NULL DEFAULT 'price'", 
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%settings__fields_extend_price}}', 'section');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230313_091808_modify_settings__fields_extend_price_table cannot be reverted.\n";

        return false;
    }
    */
}
