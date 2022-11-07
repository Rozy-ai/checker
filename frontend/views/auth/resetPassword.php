<?php

/* @var $this yii\web\View */
/* @var $form ActiveForm */
/* @var $model \frontend\models\forms\auth\ResetPassword */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->title = 'Сброс пароля';
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@app/views/auth/_base.php');
?>
<div class="site-reset-password">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Введите новый пароль:</p>

    <div class="row">
        <div class="col-lg-7">
            <?php $form = ActiveForm::begin([
                'id' => 'reset-password-form',
            ]); ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
                <?= $form->field($model, 'password_confirm')->passwordInput() ?>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" name="login-button">Сохранить</button>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$this->endContent();
