<?php

/* @var $this yii\web\View */
/* @var $form ActiveForm */
/* @var $model PasswordResetRequest */

use yii\bootstrap4\ActiveForm;
use frontend\models\forms\auth\PasswordResetRequest;

$this->title = 'Сбросить пароль';
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@app/views/auth/_base.php');
?>
<div class="site-request-password-reset">
    <h2 class="app-h2 mb-05-rem">Восстановить пароль</h2>
    <div class="row">
        <div class="col-lg-7">
            <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>

                <?= $form->field($model, 'email')->textInput() ?>

            <div class="row">
                <div class="offset-3">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" name="login-button">Восстановить пароль</button>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$this->endContent();
