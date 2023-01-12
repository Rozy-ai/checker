<?php

use yii\db\Migration;

/**
 * Class m211210_135854_modify_primary_index_of_table_comparisons
 */
class m211210_135854_modify_primary_index_of_table_comparisons extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
            ALTER TABLE `u1257171_e2f7`.`comparisons` DROP PRIMARY KEY, ADD PRIMARY KEY (`product_id`, `node`) USING BTREE;
         */
        $this->dropPrimaryKey('pk_comparisons', '{{%comparisons}}');
        $this->addPrimaryKey('pk_comparisons', '{{%comparisons}}', ['product_id', 'node']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropPrimaryKey('pk_comparisons', '{{%comparisons}}');
        $this->addPrimaryKey('pk_comparisons', '{{%comparisons}}', ['product_id']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211210_135854_modify_primary_index_of_table_comparisons cannot be reverted.\n";

        return false;
    }
    */
}
