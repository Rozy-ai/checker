<?php
namespace frontend\models\forms\auth;

use common\models\ProfileName;
use frontend\events\UserRegisteredEvent;
use frontend\models\Identity;
use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class Register extends Model
{
    public ?string $login = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?string $password_confirm = null;

    private const DENIED_LOGIN_STARTS = [
        'general', 'free'
    ];

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['login', 'email'], 'trim'],
            [['email', 'login'], 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 128],
            ['email', 'unique', 'targetClass' => Identity::class, 'message' => 'Пользователь с таким E-mail уже зарегистрирован'],
            ['login', 'match', 'pattern' => '/[a-z0-9_-]+/i', 'message' => 'Разрешены только символы латинского алфавита, цифры, тире и подчеркивание'],
            ['login', 'unique', 'targetClass' => Identity::class, 'message' => 'Данный логин занят'],
            [
                'login',
                'unique',
                'targetClass' => ProfileName::class,
                'targetAttribute' => 'name',
                'message' => 'Запрещенный логин'
            ],
            ['login', function() {
                foreach (self::DENIED_LOGIN_STARTS as $str) {
                    if (strpos(strtolower($this->login), $str) === 0) {
                        $this->addError('login', 'Запрещенное имя');
                        break;
                    }
                }
            }],
            ['password', 'required'],
            ['password', 'string', 'min' => 4],
            ['password_confirm', 'required'],
            ['password_confirm', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'login' => 'Логин',
            'email' => 'E-mail',
            'password' => 'Пароль',
            'password_confirm' => 'Подтверждение',
        ];
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $user = new Identity();
        $user->login = $this->login;
        $user->email = $this->email;
        $user->setPassword($this->password);
        if (!$user->save(false)) {
            return false;
        }
        $event = new UserRegisteredEvent();
        $event->setNewUser($user);
        $event->trigger(UserRegisteredEvent::class, UserRegisteredEvent::EVENT_NAME, $event);
        return true;
    }
}
