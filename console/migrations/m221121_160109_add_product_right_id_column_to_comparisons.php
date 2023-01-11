<?php

use yii\db\Migration;

/**
 * Class m221121_160109_add_id_item_column
 */
class m221121_160109_add_product_right_id_column_to_comparisons extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable('comparisons');
        $this->addColumn('comparisons', 'product_right_id', $this->integer()->notNull()->after('product_id'));
        $this->dropPrimaryKey('pk_comparisons', '{{%comparisons}}');
        $this->addPrimaryKey('pk_comparisons', '{{%comparisons}}', ['product_id', 'source_id', 'product_right_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('comparisons', 'product_right_id');
        $this->dropPrimaryKey('pk_comparisons', '{{%comparisons}}');
        $this->addPrimaryKey('pk_comparisons', '{{%comparisons}}', ['product_id', 'source_id', 'node']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221121_160109_add_id_item_column cannot be reverted.\n";

        return false;
    }
    */
}
