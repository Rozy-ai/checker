<?php

declare(strict_types=1);

namespace frontend\controllers;

use Yii;
use yii\widgets\ActiveForm;
use frontend\components\User;
use yii\filters\{AccessControl, VerbFilter};
use yii\base\{ErrorException, InvalidArgumentException};
use yii\web\{BadRequestHttpException, Controller, Request, Response, Session};
use frontend\models\forms\auth\{Register, VerifyEmail, Login, PasswordResetRequest, ResetPassword};

class AuthController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['login', 'register', 'reset-password', 'reset-password-confirm', 'verify-email'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Signs user up.
     */
    public function actionRegister(Request $request, Session $session)
    {
        $model = new Register();
        if ($model->load($request->post())) {
            if ($request->post('ajax') === 'register-form') {
                return $this->asJson(ActiveForm::validate($model));
            }
            if ($model->signup()) {
                $session->setFlash(
                    'success',
                    "Регистрация завершена\nНа вашу почту отправлено письмо с ссылкой для подтверждения регистрации"
                );
                return $this->goHome();
            }
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    /**
     * Logs in a user
     * @return Response|string
     */
    public function actionLogin(User $user, Request $request)
    {
        if (!$user->isGuest) {
            return $this->goHome();
        }

        $model = new Login();
        if ($model->load($request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logs out the current user.
     * @param User $user
     * @return Response
     */
    public function actionLogout(User $user): Response
    {
        $user->logout();

        return $this->goHome();
    }

    /**
     * Requests password reset.
     * @return Response|string
     * @noinspection DuplicatedCode
     */
    public function actionResetPassword(Request $request, Session $session)
    {
        $model = new PasswordResetRequest();
        if ($model->load($request->post()) && $model->validate()) {
            try {
                if ($model->sendEmail()) {
                    $session->setFlash('success', 'Мы отправили на ваш почтовый ящик инструкцию по сбросу пароля');
                    return $this->goHome();
                }
            } catch (ErrorException $e) {
                $session->setFlash('error', $e->getMessage());
                return $this->refresh();
            } catch (\Exception $e) {
                $session->setFlash('error', 'Произошла ошибка..');
                Yii::error($e);
                return $this->refresh();
            }
            $session->setFlash('error', 'Произошла ошибка.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @param Request $request
     * @param Session $session
     * @return Response|string
     * @throws BadRequestHttpException
     * @noinspection DuplicatedCode
     */
    public function actionResetPasswordConfirm(string $token, Request $request, Session $session)
    {
        try {
            $model = new ResetPassword($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load($request->post()) && $model->validate() && $model->resetPassword()) {
            $session->setFlash('success', 'Новый пароль сохранен.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @param User $user
     * @param Session $session
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionVerifyEmail(string $token, User $user, Session $session): Response
    {
        try {
            $model = new VerifyEmail($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($identity = $model->verifyEmail()) {
            if ($user->login($identity)) {
                $session->setFlash('success', 'Ваш Email подтвержден!');
                return $this->goHome();
            }
        }

        $session->setFlash('error', 'Мы не смогли проверить ваш аккаунт с предоставленным вами токеном.');
        return $this->goHome();
    }
}
