<?php

use backend\controllers\ImportController;
use common\models\Source;
use yii\db\Migration;

/**
 * Class m230219_113947_update_last_product_date
 */
class m230219_113947_update_last_product_date extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sources = Source::find()->all();
        foreach ($sources as $source) {
            $import_max_product_date = ImportController::get_max_product_date_in_parser($source);
            if ($import_max_product_date === false) $import_max_product_date = null;
            $this->update('source', ['import_local__max_product_date' => $import_max_product_date], ['id' => $source->id]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sources = Source::getAllWithIdKey();
        foreach ($sources as $source) {
            $this->update('source', ['import_local__max_product_date' => null]);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230219_113947_update_last_product_date cannot be reverted.\n";

        return false;
    }
    */
}
