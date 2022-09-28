<?php

use backend\components\ProductWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Message */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="message-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'text')->textarea(['rows' => 3]) ?>
  <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

  <?
  $list = \backend\models\Settings__table_rows::find()->all();
  $out[-1] = 'Выбрать';
    foreach ($list as $l){
      $out[$l->id] = $l->title ;
      //$out[$l->id] = $l->title . ' ' .$l->item_1_key . ' ' . $l->item_2_key;
    }
  $options = ['control-label' => '111'];

  //$options = ['prompt'=>'Select'];
  if ($model->settings__table_rows_id > -1)
    $options['options'] = [$model->settings__table_rows_id => ['selected' => true]]
  ?>

  <div class="row [ settings ] message-form__settings js-settings-root">
    <div class="col">
      <?= $form->field($model, 'settings__table_rows_id')->dropDownList(
        $out,
        $options
      )->label(false); ?>
    </div>
    <div class="col">
      <?= $form->field($model, 'settings__compare_symbol')->dropDownList(
        ['-1' => 'всегда','==' => '==','!=' => '!=','<' => '<','>' => '>'],
        []
      )->label(false); ?>
    </div>
    <div class="col">
      <?= $form->field($model, 'settings__compare_field')->input('text')->label(false); ?>
    </div>
  </div>


  <div class="row settings__visible_all_row">
    <div class="col">
      <?= $form->field($model, 'settings__visible_all')->checkbox([
        'checked' => $model->settings__visible_all ? true : false,
        'label' => 'Видят все пользователи'
      ]); ?>
    </div>

    <div class="col">
      <?= $form->field($model, 'settings__show_additional_fields')->checkbox([
        'checked' => $model->settings__show_additional_fields ? true : false,
        'label' => 'Показать дополнительные поля'
      ]); ?>
    </div>
  </div>


  <div class="form-group">
    <?= Html::submitButton(Yii::t('site', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    <?= Html::a('Отменить','/message/', ['class' => 'btn btn-secondary']) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>
