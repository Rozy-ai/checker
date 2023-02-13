<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%source}}`.
 */
class m230206_144750_add_country_column_to_source_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%source}}', 'country', $this->char(5));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%source}}', 'country');
    }
}
