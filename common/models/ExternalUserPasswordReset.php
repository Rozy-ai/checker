<?php

declare(strict_types=1);

namespace common\models;

use Yii;
use yii\base\ErrorException;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_password_resets}}".
 *
 * @property int $id
 * @property int $user_id Пользователь
 * @property string $token Токен
 * @property int $used Использован
 * @property string|null $created_at Дата
 *
 * @property-read ExternalUser $user
 */
class ExternalUserPasswordReset extends \yii\db\ActiveRecord
{
    /**
     * @var int Время жизни токена в секундах (10 мин)
     */
    public const TOKEN_LIFETIME = 60 * 10;

    /**
    * {@inheritdoc}
    */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%external_user_password_resets}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'token'], 'required'],
            [['user_id'], 'integer'],
            ['used', 'boolean'],
            [['created_at'], 'safe'],
            [['token'], 'string', 'max' => 32],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExternalUser::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'token' => 'Токен',
            'used' => 'Использован',
            'created_at' => 'Дата',
        ];
    }

    public function confirm()
    {
        $this->used = true;
        $this->save();
    }

    /**
     * Gets query for [[ExternalUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ExternalUser::class, ['id' => 'user_id']);
    }

    /** Генерирует токен для пользователя, сохраняет в базу и возвращает его
     * @param ExternalUser $user
     * @return string
     * @throws ErrorException
     * @throws \yii\base\Exception
     */
    public static function createForUser(ExternalUser $user): string
    {

        $model = new static;
        $model->token = Yii::$app->getSecurity()->generateRandomString();
        $model->user_id = $user->id;
        if ($model->save()) {
            return $model->token;
        }
        throw new ErrorException('Произошла ошибка при генерации токена');
    }

    public static function findByToken(string $token): ?self
    {
        return static::find()
            ->alias('token')
            ->joinWith('user user')
            ->where('[[token]].[[token]] = :token AND [[token]].[[used]] = :used AND `token`.`created_at` > (UNIX_TIMESTAMP() - :seconds)', [
                ':token' => $token,
                ':used' => false,
                ':seconds' => ExternalUserPasswordReset::TOKEN_LIFETIME
            ])->one();
    }
}
