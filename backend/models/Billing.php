<?php

namespace backend\models;

use yii\db\ActiveQuery;

/**
 * @inheritDoc
 */
class Billing extends \common\models\Billing
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'status', 'source', 'date', 'admin_id'], 'integer'],
            [['status', 'sum', 'description', 'source'], 'required'],
            ['date', 'default', 'value' => time()],
            [['sum'], 'number'],
            [['description'], 'string', 'max' => 255],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ExternalUser::class,
                'targetAttribute' => ['user_id' => 'id'],
                'message' => 'User does not exist'
            ],
            [['admin_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['admin_id' => 'id']],
        ];
    }

    public function setPaid(): Billing
    {
        $this->status = self::STATUS_PAID;
        return $this;
    }

    public function setSourceAdmin(): Billing
    {
        $this->source = self::SOURCE_ADMIN;
        return $this;
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
