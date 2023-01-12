<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%password_resets}}`.
 */
class m221109_172548_create_external_users_password_resets_table extends Migration
{
    private const TABLE_NAME = '{{%external_user_password_resets}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(10)->unsigned()->notNull()->comment('Пользователь'),
            'token' => $this->char(32)->notNull()->comment('Токен'),
            'used' => $this->boolean()->notNull()->defaultValue(false)->comment('Использован'),
            'created_at' => $this->integer(10)->unsigned()->comment('Дата')
        ]);
        $this->addForeignKey(
            'user_password_resets_ibfk1',
            self::TABLE_NAME,
            'user_id',
            \common\models\ExternalUser::tableName(),
            'id',
            'CASCADE',
            'CASCADE',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
