<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ProductSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'categories') ?>

    <?= $form->field($model, 'asin') ?>

    <?= $form->field($model, 'info') ?>

    <?php // echo $form->field($model, 'comparsion_info') ?>

    <?php // echo $form->field($model, 'results_all_all') ?>

    <?php // echo $form->field($model, 'results_1_1') ?>

    <?php // echo $form->field($model, 'images') ?>

    <?php // echo $form->field($model, 'images_url') ?>

    <?php // echo $form->field($model, 'item_url') ?>

    <?php // echo $form->field($model, 'date_add') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('site', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('site', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
