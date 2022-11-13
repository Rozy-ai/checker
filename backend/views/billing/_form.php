<?php

use backend\models\Billing;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Billing $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="billing-form">

    <?php $form = ActiveForm::begin(['id' => 'billing-form', 'enableAjaxValidation' => true]); ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?php if (!$model->getIsNewRecord()):?>
    <?= $form->field($model, 'status')->dropDownList(Billing::STATUSES, ['prompt' => ''])?>
    <?php endif;?>

    <?= $form->field($model, 'sum')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <button type="submit" class="btn btn-success">Save</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
