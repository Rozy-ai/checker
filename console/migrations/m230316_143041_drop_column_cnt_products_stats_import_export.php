<?php

use yii\db\Migration;

/**
 * Class m230316_143041_drop_column_cnt_products_stats_import_export
 */
class m230316_143041_drop_column_cnt_products_stats_import_export extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{stats__import_export}}', 'cnt_products');
        $this->dropColumn('{{stats__import_export}}', 'cnt_products_right');
        $this->dropColumn('{{stats__import_export}}', 'stat');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{stats__import_export}}', 'cnt_products', $this->integer(11));
        $this->addColumn('{{stats__import_export}}', 'cnt_products_right', $this->integer(11));
        $this->addColumn('{{stats__import_export}}', 'stat', $this->text());
        
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230316_143041_drop_column_cnt_products_stats_import_export cannot be reverted.\n";

        return false;
    }
    */
}
