<?php
namespace frontend\models\forms\auth;

use common\enums\UserStatuses;
use common\models\ExternalUser;
use common\models\User;
use common\models\ExternalUserPasswordReset;
use frontend\models\Identity;
use Yii;
use yii\base\ErrorException;
use yii\base\Model;
use yii\db\ActiveQuery;

/**
 * Password reset request form
 */
class PasswordResetRequest extends Model
{
    public ?string $email = null;

    private ?Identity $_user = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['email', 'trim'],
            ['email', 'required', 'message' => 'Введите ваш логин или email'],
            ['email', 'validateEmail'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'email' => 'Email/Логин'
        ];
    }

    public function validateEmail()
    {
        $this->_user = Identity::find()
            ->where(
                '[[status]] = :status AND (LOWER([[login]]) = :username OR LOWER([[email]] = :username))', [
                    ':status' => ExternalUser::STATUS_ACTIVE,
                    ':username' => $this->email
                ]
            )
            ->one();
        if (!$this->_user) {
            $this->addError('email', 'Пользователь не найден');
        } else {
            $check = ExternalUserPasswordReset::find()
                ->where('[[user_id]] = :user AND [[used]] = :used AND DATE_ADD([[created_at]], INTERVAL :seconds SECOND) > NOW()', [
                    ':user' => $this->_user->id,
                    ':used' => false,
                    ':seconds' => ExternalUserPasswordReset::TOKEN_LIFETIME
                ])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();

            if ($check) {
                $message = Yii::t(
                    'app',
                    'Следующий токен можно запросить {nextRequestDuration}',
                    [
                        'nextRequestDuration' => Yii::$app
                            ->getFormatter()
                            ->asRelativeTime(
                                (new \DateTime($check->created_at))->modify('+ ' . ExternalUserPasswordReset::TOKEN_LIFETIME . 'seconds')
                            )
                    ]
                );
                $this->addError('email', $message);
            }
        }
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     * @throws ErrorException
     * @throws \yii\base\Exception
     */
    public function sendEmail(): bool
    {
        $user = $this->_user;

        if (!$user) {
            Yii::error('Отправка токена сброса пароля. Не найден пользователь');
            throw new ErrorException('Произошла ошибка');
        }

        $token = ExternalUserPasswordReset::createForUser($user);

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                [
                    'user' => $user,
                    'link' => Yii::$app->urlManager->createAbsoluteUrl([
                        'auth/reset-password-confirm',
                        'token' => $token
                    ])
                ]
            )
            ->setTo($user->email)
            ->setSubject('Сброс пароля на сайте ' . Yii::$app->name)
            ->send();
    }
}
