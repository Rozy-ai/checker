<?php

use yii\db\Migration;

/**
 * Class m230315_153509_modify_stats__import_export
 */
class m230315_153509_modify_stats__import_export extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{stats__import_export}}', 'cnt_records_left', 'cnt_products');
        $this->renameColumn('{{stats__import_export}}', 'cnt_records_rigth', 'cnt_products_right');
        $this->addColumn('{{stats__import_export}}', 'cnt_records', $this->integer(11));
        $this->addColumn('{{stats__import_export}}', 'stat', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('{{stats__import_export}}', 'cnt_products', 'cnt_records_left');
        $this->renameColumn('{{stats__import_export}}', 'cnt_products_right', 'cnt_records_rigth');
        $this->dropColumn('{{stats__import_export}}', 'cnt_records');
        $this->dropColumn('{{stats__import_export}}', 'stat');
    }

}
