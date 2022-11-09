<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Форма восстановления E-mail';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-resend-verification-email">
    <h2 class="app-h2 mb-05-rem"><?= Html::encode($this->title) ?></h2>

    <p class="auth-form-subtitle">Please fill out your email. A verification email will be sent there.</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'resend-verification-email-form']); ?>

            <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="login-button">Отправить</button>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
