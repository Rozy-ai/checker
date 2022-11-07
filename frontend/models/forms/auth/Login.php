<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace frontend\models\forms\auth;

use Yii;
use yii\base\Model;
use frontend\models\Identity;

/**
 * @property-read Identity|null $user
 */
class Login extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => 'E-mail/Логин',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     */
    public function validatePassword(string $attribute): void
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверные логин или пароль.');
            }
            if ($user) {
                if ($user->isBlocked()) {
                    $this->addError($attribute, 'Ваш аккаунт заблокирован.');
                }
                if ($user->isNew()) {
                    $this->addError($attribute, 'Вы не подтвердили свой email.');
                }
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login(): bool
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }

        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return Identity|null
     */
    protected function getUser(): ?Identity
    {
        if ($this->_user === null) {
            $this->_user = Identity::findByLogin($this->username);
        }

        return $this->_user;
    }
}
