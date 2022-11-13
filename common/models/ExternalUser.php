<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%external_users}}".
 *
 * @property int $id
 * @property string $login
 * @property string $email
 * @property integer $status
 * @property string $password_hash
 * @property string $auth_key
 * @property string $email_confirm_token
 * @property int $created_at
 * @property int|null $updated_at
 * @property null|string $password
 * @property-read Billing[] $billings
 * @property-read string $name
 */
class ExternalUser extends \yii\db\ActiveRecord
{
    public const STATUS_NEW = 1;
    public const STATUS_ACTIVE = 2;
    public const STATUS_BLOCKED = 3;

    public const STATUSES = [
        self::STATUS_NEW => 'Новый',
        self::STATUS_ACTIVE => 'Активен',
        self::STATUS_BLOCKED => 'Заблокирован',
    ];

    /**
     * @var float
     */
    public float $balance = 0;


    public function behaviors(): array
    {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%external_users}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'login' => 'Логин',
            'email' => 'Email',
            'status' => 'Статус',
            'password' => 'Пароль',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлен',
        ];
    }

    public function getName(): string
    {
        return $this->login;
    }

    public function statusText(): string
    {
        return self::STATUSES[(int)$this->status];
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    public function setNew(): self
    {
        $this->status = self::STATUS_NEW;
        return $this;
    }

    public function setActive(): self
    {
        $this->status = self::STATUS_ACTIVE;
        return $this;
    }

    public function block(): self
    {
        $this->status = self::STATUS_BLOCKED;
        return $this;
    }

    public function getPassword()
    {
        return null;
    }

    public function setPassword(string $password): self
    {
        if ($password) {
            $this->password_hash = \Yii::$app->getSecurity()->generatePasswordHash($password);
        }
        return $this;
    }

    public function generateEmailConfirmToken(): self
    {
        $this->email_confirm_token = Yii::$app->getSecurity()->generateRandomString();
        return $this;
    }

    public function generateAuthKey(): self
    {
        $this->auth_key = Yii::$app->getSecurity()->generateRandomString();
        return $this;
    }

    /**
     * Gets query for [[Billings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBillings(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Billing::class, ['user_id' => 'id']);
    }

    public function getBalance(): float
    {
        return $this->getBillings()->where(['status' => Billing::STATUS_PAID])->sum('sum') ?: 0;
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->setNew();
            $this->generateAuthKey();
            $this->generateEmailConfirmToken();
        }
        return true;
    }
}
