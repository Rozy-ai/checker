<?php

use yii\db\Migration;

class m230111_160440_create_table_external_user_profile_field extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%external_user_profile_field}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(1024)->notNull(),
                'comment' => $this->text(),
                'type' => $this->string()->notNull(),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%external_user_profile_field}}');
    }
}
