<?php

/** @var \yii\web\View $this */
/** @var string $content */

$action = Yii::$app->controller->action->id;
?>
<div class="page_user">
    <div class="container">
        <div class="row">
            <div class="col-3">
                <div class="auth-nav__container">
                    <?= \yii\bootstrap4\Nav::widget([
                        'items' => [
                            [
                                'label' => 'Зарегистрироваться',
                                'url' => ['/auth/register'],
                            ],
                            [
                                'label' => 'Войти',
                                'url' => ['/auth/login'],
                            ],
                            [
                                'label' => 'Восстановить пароль',
                                'url' => ['/auth/reset-password'],
                                'active' => $action === 'reset-password' || $action === 'reset-password-confirm'
                            ],
                        ],
                    ]);?>
                </div>
            </div>
            <div class="col-9">
                <div class="auth-content">
                    <?= $content?>
                </div>
            </div>
        </div>
    </div>
</div>
