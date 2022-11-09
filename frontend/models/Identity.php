<?php

namespace frontend\models;

use common\models\ExternalUser;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

/**
 * @inheritDoc
 *
 * @property-read string $authKey
 */
class Identity extends ExternalUser implements IdentityInterface
{
    /**
     * Получить пользователя по логину (для формы авторизации)
     * @param string $login
     * @return Identity|null
     */
    public static function findByLogin(string $login): ?Identity
    {
        return self::find()
            ->where([
                'or',
                ['login' => $login],
                ['email' => $login]
            ])
            ->one();
    }

    /**
     * Поиск пользователя для активации email
     * @param string $token
     * @return IdentityInterface|null
     */
    public static function findByVerificationToken(string $token): ?IdentityInterface
    {
        return self::find()->where(['email_confirm_token' => $token, 'status' => self::STATUS_NEW])->one();
    }

    /**
     * @inheritDoc
     */
    public static function findIdentity($id)
    {
        return self::find()->where(['id' => $id, 'status' => self::STATUS_ACTIVE])->one();
    }

    /**
     * @inheritDoc
     */
    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * @inheritDoc
     */
    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    /**
     * @inheritDoc
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }
}
