<?php

namespace frontend\models\forms\Auth;

use frontend\models\Identity;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class VerifyEmail extends Model
{
    /**
     * @var string| null
     */
    public ?string $token = null;

    /**
     * @var Identity|null
     */
    private ?Identity $_user = null;

    /**
     * Creates a form model with given token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, array $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Не передан токен.');
        }
        $this->_user = Identity::findByVerificationToken($token);
        if (!$this->_user) {
            throw new InvalidArgumentException('Неверный токен.');
        }
        if ($this->_user->isActive()) {
            throw new InvalidArgumentException('Ваш Email уже подтвержден ранее');
        }
        parent::__construct($config);
    }

    /**
     * Verify email
     *
     * @return Identity|null the saved model or null if saving fails
     */
    public function verifyEmail(): ?Identity
    {
        $user = $this->_user;
        $user->setActive();
        return $user->save(false) ? $user : null;
    }
}
