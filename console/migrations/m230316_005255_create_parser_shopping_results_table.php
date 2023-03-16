<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%parser_shopping_results}}`.
 */
class m230316_005255_create_parser_shopping_results_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE google.parser_shopping_results ADD COLUMN parse_at TIMESTAMP NULL");
        $this->execute("CREATE TABLE IF NOT EXISTS {{%parser_shopping_results}} SELECT * FROM google.parser_shopping_results LIMIT 0");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%parser_shopping_results}}');
    }
}
