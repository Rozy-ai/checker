<?php

declare(strict_types=1);

namespace frontend\events\dispatchers;

use frontend\events\UserRegisteredEvent;
use yii\base\BootstrapInterface;
use yii\base\Event;

class UserRegisteredDispatcher implements BootstrapInterface
{
    /**
     * @inheritDoc
     */
    public function bootstrap($app)
    {
        Event::on(UserRegisteredEvent::class, UserRegisteredEvent::EVENT_NAME, [$this, 'sendEmail']);
    }

    public function sendEmail(UserRegisteredEvent $event)
    {
        try {
            \Yii::$app
                ->getMailer()
                ->compose(
                    ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                    [
                        'user' => $event->getNewUser(),
                        'verifyLink' => \Yii::$app->getUrlManager()->createAbsoluteUrl([
                            '/auth/verify-email',
                            'token' => $event->getNewUser()->email_confirm_token])
                    ]
                )
                ->setTo($event->getNewUser()->email)
                ->setSubject('Регистрация аккаунта на сайте ' . \Yii::$app->name)
                ->send();
        } catch( \Exception $e) {}
    }
}
