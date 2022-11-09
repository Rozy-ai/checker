<?php

declare(strict_types=1);

namespace frontend\events;

use frontend\models\Identity;
use yii\base\Event;

class UserRegisteredEvent extends Event
{
    public const EVENT_NAME = 'user_registered';

    private ?Identity $newUser = null;

    /**
     * @param Identity $newUser
     */
    public function setNewUser(Identity $newUser): void
    {
        $this->newUser = $newUser;
    }

    /**
     * @return Identity
     */
    public function getNewUser(): Identity
    {
        return $this->newUser;
    }
}
