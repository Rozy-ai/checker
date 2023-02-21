<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\UploadFields $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="upload-fields-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'price_field')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'product_field')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'row_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'position')->textInput() ?>

    <?= $form->field($model, 'default_visible')->textInput() ?>

    <?= $form->field($model, 'is_select_field')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
