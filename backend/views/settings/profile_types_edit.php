<?php

use common\models\ProfileTypeSetting;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var ProfileTypeSetting $item */

$this->title = Yii::t(
    'site',
    'Редактирование тарифа ' . $item->source->name . ' - ' . $item->profileTypeLabel
);
?>
<div class="[ SETTINGS-MAIN ] settings-main">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php $form = ActiveForm::begin(['id' => 'settings__profile_type']); ?>

    <div style="display: none">
        <?= $form->field($item, 'source_id')->hiddenInput() ?>
        <?= $form->field($item, 'profile_type')->hiddenInput() ?>
    </div>

    <?= $form->field($item, 'price1')->input('float', ['value' => $item->price1, 'placeholder' => 'key']) ?>
    <?= $form->field($item, 'price2')->input('float', ['value' => $item->price2, 'placeholder' => 'key']) ?>
    <?= $form->field($item, 'max_views_count')->input('number', ['value' => $item->max_views_count, 'placeholder' => 'key']) ?>
    <?= $form->field($item, 'cancel_show_count')->input('number', ['value' => $item->cancel_show_count, 'placeholder' => 'key']) ?>
    <?= $form->field($item, 'inner_page')->checkbox(['checked' => (bool)$item->inner_page, 'placeholder' => 'key']) ?>

    <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block']) ?>
    <?php ActiveForm::end(); ?>

    <br>

    <a class="link" href="profile_types">Назад к списку</a>
</div>
