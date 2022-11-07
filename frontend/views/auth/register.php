<?php

/* @var $this yii\web\View */
/* @var $form ActiveForm */
/* @var $model Register */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use frontend\models\forms\auth\Register;

$this->title = 'Регистрация';
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@app/views/auth/_base.php');
?>
<div class="site-login">
    <h2 class="app-h2 mb-05-rem"><?= Html::encode($this->title) ?></h2>
    <div class="row">
        <div class="col-lg-9">
            <?php $form = ActiveForm::begin([
                'id' => 'register-form',
                'enableAjaxValidation' => true,
            ]); ?>
            <?= $form->errorSummary($model)?>
                <?= $form->field($model, 'login')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'email')->input('email', ['maxlength' => true]) ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
                <?= $form->field($model, 'password_confirm')->passwordInput() ?>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="login-button">Зарегистрироваться</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$this->endContent();
