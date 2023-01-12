<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\UploadFieldsSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="upload-fields-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'comment') ?>

    <?= $form->field($model, 'price_field') ?>

    <?= $form->field($model, 'product_field') ?>

    <?php // echo $form->field($model, 'row_id') ?>

    <?php // echo $form->field($model, 'position') ?>

    <?php // echo $form->field($model, 'default_visible') ?>

    <?php // echo $form->field($model, 'is_select_field') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
