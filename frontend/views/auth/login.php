<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $model \frontend\models\forms\auth\Login */

use yii\bootstrap4\ActiveForm;

$this->title = 'Форма входа';
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@app/views/auth/_base.php');
?>
<div class="site-login">
    <h2 class="app-h2 mb-05-rem">Войти</h2>
    <div class="row">
        <div class="col-lg-7">
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
            ]); ?>
                <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
                <?= $form->field($model, 'rememberMe')->checkbox() ?>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="login-button">Войти</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$this->endContent();
