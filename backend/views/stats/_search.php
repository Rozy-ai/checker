<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\StatsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="stats-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'user_id') ?>

    <?= $form->field($model, 'period') ?>

    <?= $form->field($model, 'match_count') ?>

    <?= $form->field($model, 'mismatch_count') ?>

    <?= $form->field($model, 'other_count') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('site', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('site', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
