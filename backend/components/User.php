<?php

declare(strict_types=1);

namespace backend\components;

/**
 * @inheritDoc
 *
 * @property-read string $name
 * @property-read Identity $identity
 * @method \backend\models\User getIdentity($autoRenew = true)
 */
class User extends \yii\web\User
{
    public function getName(): ?string
    {
        if ($this->getIsGuest()) {
            return null;
        }
        return $this->getIdentity()->username;
    }
}
