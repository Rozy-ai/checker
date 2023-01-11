<?php

use yii\db\Migration;

class m230111_160441_create_table_external_user_profile_source extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%external_user_profile_source}}',
            [
                'id' => $this->primaryKey(),
                'profile_id' => $this->integer()->notNull(),
                'source_id' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('profile_id', '{{%external_user_profile_source}}', ['profile_id']);
        $this->createIndex('source_id', '{{%external_user_profile_source}}', ['source_id']);

        $this->addForeignKey(
            'external_user_profile_source_ibfk_1',
            '{{%external_user_profile_source}}',
            ['profile_id'],
            '{{%external_user_profile}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%external_user_profile_source}}');
    }
}
