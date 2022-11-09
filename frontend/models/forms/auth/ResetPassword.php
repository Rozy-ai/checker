<?php

namespace frontend\models\forms\auth;

use yii\base\InvalidArgumentException;
use yii\base\Model;
use Yii;
use common\models\ExternalUserPasswordReset;

/**
 * Password reset form
 */
class ResetPassword extends Model
{
    public ?string $password = null;
    public ?string $password_confirm = null;

    /**
     * @var null|ExternalUserPasswordReset
     */
    private ?ExternalUserPasswordReset $_token = null;


    /**
     * Creates a form model given a token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, array $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Не передан токен сброса пароля');
        }
        $this->_token = ExternalUserPasswordReset::findByToken($token);
        if (!$this->_token) {
            throw new InvalidArgumentException('Неверный токен сброса пароля');
        }
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['password', 'required'],
            ['password', 'string', 'min' => 4],
            ['password_confirm', 'required'],
            ['password_confirm', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'password' => 'Пароль',
            'password_confirm' => 'Подтверждение',
        ];
    }

    /**
     * Resets password.
     *
     * @return bool if password was reset.
     */
    public function resetPassword(): bool
    {
        $this->_token->confirm();
        $this->_token->user->setPassword($this->password)->generateAuthKey();
        return $this->_token->user->save(false);
    }
}
