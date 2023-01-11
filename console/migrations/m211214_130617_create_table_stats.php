<?php

use yii\db\Migration;

/**
 * Handles the creation for table `{{%stats}}`.
 */
class m211214_130617_create_table_stats extends Migration
{

    /** @var string  */
    protected $tableName = '{{%stats}}';

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
            'period' => $this->string(10)->notNull(),
            'type' => $this->string(1)->notNull(),
            'match_count' => $this->integer(11)->notNull()->defaultValue(0),
            'mismatch_count' => $this->integer(11)->notNull()->defaultValue(0),
            'other_count' => $this->integer(11)->notNull()->defaultValue(0),
        ], $collation);

        $this->addPrimaryKey('pk_stats', '{{%stats}}', ['user_id', 'period']);
        $this->createIndex('idx_stats_type', $this->tableName, 'type', true);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropPrimaryKey('idx_stats_type', $this->tableName);
        $this->dropIndex('idx_stats_type', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
