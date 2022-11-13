<?php

declare(strict_types=1);

namespace frontend\components;

use frontend\models\Identity;

/**
 * @inheritDoc
 *
 * @property-read string $name
 * @property-read Identity $identity
 * @method Identity getIdentity($autoRenew = true)
 */
class User extends \yii\web\User
{
    public function getName(): string
    {
        if ($this->getIsGuest()) {
            return 'Guest';
        }
        return $this->getIdentity()->login;
    }

    public function getBalance(): ?float
    {
        if ($this->getIsGuest()) {
            return null;
        }
        return $this->getIdentity()->getBalance();
    }
}
