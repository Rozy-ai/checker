<?php

use yii\helpers\Html;
use common\models\User;
use yii\widgets\ActiveForm;
use backend\models\UserForm;
/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $model_2 backend\models\User */
/* @var $form yii\widgets\ActiveForm */
/* @var $model_2_res  backend\models\User */
/* @var array $model_2_source_list_out  backend\models\User */
/* @var array $model_2_res_user_selected  backend\models\User */
?>

<div class="user-form">
  <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
    <div class="row">
        <div class="col-lg-12">

          <?php if ($model->scenario == UserForm::SCENARIO_CREATE): ?>
          <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
          <?php endif ?>
          <?= $form->field($model, 'email') ?>

          <?= $form->field($model, 'password')->passwordInput() ?>

          <?= $form->field($model, 'status')->dropDownList(User::getStatuses()); ?>

          <?= $form->field($model, 'detail_view_for_items')->checkbox([
            'checked' => $model->detail_view_for_items ? true : false,
            'label' => 'Показать в списке подробно'
          ]); ?>

          <div style="margin-bottom: 7px">
            <strong>Показывать только выбранные источники:</strong><br>
            !! Если не выбрано ничего, то доступ есть к всем!!
          </div>

          <div style="margin-bottom: 15px; border: 1px solid #ced4da; padding: 10px 15px 0 15px; border-radius: 4px;">

          <?=
              Html::activeCheckboxList(
                $model_2,
                'source_id',
                $model_2_source_list_out,
                [
                  'item' => function ($index, $label, $name, $checked, $value) use($model_2,$model_2_res_user_selected){
                    // $index = 0  $label = EBAY  $name = User__source_access[source_id][]  $checked =    $value=  1
                    $checked = $model_2_res_user_selected ? key_exists($value, $model_2_res_user_selected) : false;

                    return Html::checkbox(
                      $name,
                      $checked,
                      [
                        'label' => $label,
                        'value' => $value,
                      ]
                    );
                  },
                ]
              );
          ?>
          </div>



        </div>
    </div>



    <div class="row">
      <div class="col">
        <?= Html::submitButton(Yii::t('site', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'signup-button','style'=>'width:100%']) ?>
      </div>
      <div class="col">
        <?= Html::a('Отменить','/user/', ['class' => 'btn btn-secondary']) ?>
      </div>



    </div>


  <?php ActiveForm::end(); ?>
</div>

