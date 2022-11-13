<?php

namespace common\models;

use yii\db\{ActiveQuery, ActiveRecord};

/**
 * This is the model class for table "{{%billing}}".
 *
 * @property int $id
 * @property int|null $user_id Пользователь
 * @property int $status Статус
 * @property float $sum Сумма
 * @property string $description Описание
 * @property int $source Платежная система
 * @property int $date Дата
 * @property int|null $admin_id Админ
 *
 * @property User $admin
 * @property ExternalUser $user
 */
class Billing extends ActiveRecord
{
    public const STATUS_NEW = 1;
    public const STATUS_PAID = 2;
    public const STATUS_CANCEL = 3;

    public const SOURCE_ADMIN = 1;

    public const STATUSES = [
        self::STATUS_NEW => 'Создан',
        self::STATUS_PAID => 'Оплачен',
        self::STATUS_CANCEL => 'Отменен',
    ];

    public const SOURCES = [
        self::SOURCE_ADMIN => 'Админ'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%billing}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'status' => 'Статус',
            'sum' => 'Сумма',
            'description' => 'Описание',
            'source' => 'Платежная система',
            'date' => 'Дата',
            'admin_id' => 'Админ',
        ];
    }

    public function getSourceText(): string
    {
        return self::SOURCES[(int)$this->source];
    }

    public function getStatusText(): string
    {
        return self::STATUSES[(int)$this->status];
    }

    /**
     * Gets query for [[Admin]].
     *
     * @return ActiveQuery
     */
    public function getAdmin(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'admin_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(ExternalUser::class, ['id' => 'user_id']);
    }
}
