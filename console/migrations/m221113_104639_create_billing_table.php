<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%balance}}`.
 */
class m221113_104639_create_billing_table extends Migration
{
    private const TABLE_NAME = '{{%billing}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(10)->unsigned()->notNull(),
            'user_id' => $this->integer(10)->unsigned()->null()->comment('Пользователь'),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->comment('Статус'),
            'sum' => $this->money()->notNull()->comment('Сумма'),
            'description' => $this->string()->notNull()->comment('Описание'),
            'source' => $this->tinyInteger(1)->notNull()->comment('Платежная система'),
            'date' => $this->integer(10)->unsigned()->notNull()->comment('Дата'),
            'admin_id' => $this->integer(11)->null()->comment('Админ'),
        ]);
        $this->createIndex('balance_ind1', self::TABLE_NAME, 'date');
        $this->addForeignKey(
            'balance_ibfk1',
            self::TABLE_NAME,
            'user_id',
            \common\models\ExternalUser::tableName(),
            'id',
            'SET NULL',
            'CASCADE',
        );
        $this->addForeignKey(
            'balance_ibfk2',
            self::TABLE_NAME,
            'admin_id',
            \common\models\User::tableName(),
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
