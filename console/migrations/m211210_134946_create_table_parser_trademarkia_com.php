<?php

use yii\db\Migration;

/**
 * Handles the creation for table `{{%parser_trademarkia_com}}`.
 */
class m211210_134946_create_table_parser_trademarkia_com extends Migration
{

    /** @var string  */
    protected $tableName = '{{%parser_trademarkia_com}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $collation = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $collation = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->notNull(),
            'title' => $this->string(255),
            'categories' => $this->text(),
            'asin' => $this->string(15)->notNull(),
            'info' => $this->text(),
            'comparsion_info' => $this->text(),
            'results_all_all' => $this->text(),
            'results_1_1' => $this->text(),
            'images' => $this->text(),
            'images_url' => $this->text(),
            'item_url' => $this->string(500),
            'date_add' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $collation);


        $this->createIndex('title', $this->tableName, 'title');
        $this->createIndex('asin', $this->tableName, 'asin');
        $this->createIndex('item_url', $this->tableName, 'item_url');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('title', $this->tableName);
        $this->dropIndex('asin', $this->tableName);
        $this->dropIndex('item_url', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
