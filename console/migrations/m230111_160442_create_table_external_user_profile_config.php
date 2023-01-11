<?php

use yii\db\Migration;

class m230111_160442_create_table_external_user_profile_config extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%external_user_profile_config}}',
            [
                'id' => $this->primaryKey(),
                'profile_id' => $this->integer(),
                'field_id' => $this->integer(),
            ],
            $tableOptions
        );

        $this->createIndex('field_id', '{{%external_user_profile_config}}', ['field_id']);
        $this->createIndex('profile_id', '{{%external_user_profile_config}}', ['profile_id']);

        $this->addForeignKey(
            'external_user_profile_config_ibfk_1',
            '{{%external_user_profile_config}}',
            ['field_id'],
            '{{%external_user_profile_field}}',
            ['id'],
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'external_user_profile_config_ibfk_2',
            '{{%external_user_profile_config}}',
            ['profile_id'],
            '{{%external_user_profile}}',
            ['id'],
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%external_user_profile_config}}');
    }
}
