<?php

use yii\db\Migration;

/**
 * Handles the creation for table `{{%comparisons}}`.
 */
class m211210_134845_create_table_comparisons extends Migration
{

    /** @var string  */
    protected $tableName = '{{%comparisons}}';

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
            'user_id' => $this->primaryKey()->notNull(),
            'product_id' => $this->integer(11)->notNull(),
            'node' => $this->integer(11)->notNull(),
            'status' => "enum('MISMATCH','MATCH','OTHER') NOT NULL" ,
            'message' => $this->text(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11)->notNull(),
            'url' => $this->text(),
        ], $collation);

        $this->createIndex('user_id', $this->tableName, 'user_id');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('user_id', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
