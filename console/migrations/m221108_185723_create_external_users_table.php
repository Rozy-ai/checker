<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%external_users}}`.
 */
class m221108_185723_create_external_users_table extends Migration
{
    private const TABLE_NAME = '{{%external_users}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(10)->unsigned()->notNull(),
            'login' => $this->string(24)->notNull()->unique(),
            'email' => $this->string(64)->notNull()->unique(),
            'status' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(\common\models\ExternalUser::STATUS_NEW),
            'password_hash' => $this->char(60)->notNull(),
            'auth_key' => $this->char(32)->notNull(),
            'email_confirm_token' => $this->char(32)->null(),
            'created_at' => $this->integer(10)->unsigned()->notNull(),
            'updated_at' => $this->integer(10)->unsigned()->null(),
        ]);
        $this->createIndex('ext_usrs_ind1', self::TABLE_NAME, 'auth_key');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
