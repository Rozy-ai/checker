<?php

use yii\db\Migration;

class m230112_135552_create_table_upload_fields extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%upload_fields}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(512)->notNull(),
                'comment' => $this->string(1024),
                'price_field' => $this->string(2048),
                'product_field' => $this->string(256),
                'row_id' => $this->string()->notNull(),
                'position' => $this->integer()->notNull(),
                'default_visible' => $this->boolean()->notNull(),
                'is_select_field' => $this->boolean()->notNull()->defaultValue('0'),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%upload_fields}}');
    }
}
