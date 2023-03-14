<?php

use yii\db\Migration;

/**
 * Class m230314_153642_add_colomn_cnt_record_stats__import_export
 */
class m230314_153642_add_colomn_cnt_products_stats__import_export extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{stats__import_export}}', 'cnt_records_left', $this->integer(11));
        $this->addColumn('{{stats__import_export}}', 'cnt_records_rigth', $this->integer(11));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{stats__import_export}}', 'cnt_records_left');
        $this->dropColumn('{{stats__import_export}}', 'cnt_records_rigth');
    }
}
