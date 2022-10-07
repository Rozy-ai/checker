<?php

use yii\db\Migration;

/**
 * Handles the creation for table `{{%user_message}}`.
 */
class m211210_135004_create_table_user_message extends Migration
{

    /** @var string  */
    protected $tableName = '{{%user_message}}';

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
            'user_id' => $this->integer(11)->notNull(),
            'message_id' => $this->integer(11)->notNull(),
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
