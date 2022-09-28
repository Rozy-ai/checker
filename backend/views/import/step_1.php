<?
/* @var $profiles_list */
use backend\models\Source;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$model = new \yii\base\DynamicModel(['source_id','q_1','load_file']);
$model->addRule(['source_id'], 'integer');
$model->addRule(['q_1'], 'required');
$model->addRule(['load_file'], 'file',['skipOnEmpty' => false, 'extensions' => 'sql', 'maxFiles' => 4]);
// , ]
//$model->addRule(['load_file'], 'trim');
//$model->addRule(['use_previous_saved'], 'integer');
//$model->addRule(['profile'], 'string');
//$model->addRule(['ignore_step_3'], 'string');
?>
<style>
  #dynamicmodel-comparisons{
    border: 1px solid #b4b4b4;
    border-radius: 6px;
    padding: 9px 6px 0 11px;
  }
  .field-dynamicmodel-use_previous_saved label{
    display: block;
  }
</style>

<div style="margin-top: 70px">
  <h2>Import: Step 1 (<?=Source::get_source($source_id)['source_name']?>)</h2>

  <? $form = ActiveForm::begin(['action' => '/import/step_2',]); ?>

  <?= $form->field($model,'source_id')->hiddenInput(['value' => $source_id])->label(false); ?>

  <?= $form->field($model,'load_file')->fileInput(
    [
      'accept' => '.sql'
    ]
  )->label(false); ?>

  <?= $form->field($model,'q_1')->radioList(
    [
      'replace' => 'заменить',
      'ignore' => 'игнорировать',
    ],
    [ // можно без этого массива
      'item' =>
        function($index, $label, $name, $checked, $value)
        {

          return Html::radio($name,$value === 'ignore',['label'=>$label,'value' => $value]).'<br/>';
        },
    ]

  )->label('<strong>Что делать с одинаковыми товарами?</strong>')?>

  <div class="row">
    <div class="col">
      <span onclick="window.location = '/settings/source_list';" class="btn btn-secondary btn-block">Назад</span>
    </div>
    <div class="col">
      <button type="submit" class="btn btn-primary btn-block js-next">Далее</button>
    </div>

  </div>

  <? ActiveForm::end() ?>

</div>
