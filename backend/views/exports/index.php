<?
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$model = new \yii\base\DynamicModel(['source_id','comparisons','test']);
$model->addRule(['source_id'], 'integer');
$model->addRule(['comparisons'], 'required');
$model->addRule(['test'], 'trim');
?>

<div>
  <h2>Выгрузка: Step 1</h2>

  <? $form = ActiveForm::begin(['action' => '/exports/step_2',]); ?>
    <?= $form->field($model, 'source_id')->dropDownList(
      \backend\models\Source::get_sources_for_form()
    )->label('Выберите источник:'); ?>


  <div class="row">
    <div class="col">
      <span onclick="history.back();" class="btn btn-secondary btn-block">Назад</span>
    </div>
    <div class="col">
      <button type="submit" class="btn btn-primary btn-block">Далее</button>
    </div>

  </div>
  <? ActiveForm::end() ?>

</div>

