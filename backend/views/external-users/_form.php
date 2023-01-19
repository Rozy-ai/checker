<?php

use common\models\ExternalUser;
use common\models\ExternalUserProfile;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\ExternalUser $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="external-user-form">
    <?php $form = ActiveForm::begin(['id' => 'external-user-form', 'enableAjaxValidation' => true]); ?>
    <?= $form->field($model, 'login')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email')->input('email', ['maxlength' => true]) ?>
    <?= $form->field($model, 'status')->dropDownList(ExternalUser::STATUSES, ['prompt' => '']) ?>
    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'ex_profile_id')->dropDownList(
            ArrayHelper::map(
                    array_merge([null=>'Не выбран'], ExternalUserProfile::find()->all()), 'id', 'name'
            )
    ) ?>
    <div class="form-group">
        <button type="submit" class="btn btn-success">Сохранить</button>
    </div>
    <?php ActiveForm::end(); ?>
</div>
