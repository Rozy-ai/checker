<?php

use yii\db\Migration;

class m230111_160439_create_table_external_user_profile extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%external_user_profile}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(1024)->notNull(),
                'comment' => $this->text()->notNull(),
                'description' => $this->text()->notNull(),
                'need_confirmation' => $this->boolean()->notNull()->defaultValue('0'),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%external_user_profile}}');
    }
}
