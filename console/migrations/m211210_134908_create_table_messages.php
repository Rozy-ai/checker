<?php

use yii\db\Migration;

/**
 * Handles the creation for table `{{%messages}}`.
 */
class m211210_134908_create_table_messages extends Migration
{

    /** @var string  */
    protected $tableName = '{{%messages}}';

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
            'text' => $this->text()->notNull(),
        ], $collation);



    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
