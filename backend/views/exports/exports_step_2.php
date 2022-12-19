<?
/* @var $profiles_list */
use common\models\Source;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$model = new \yii\base\DynamicModel(['source_id','comparisons','test','use_previous_saved','profile','ignore_step_3']);
$model->addRule(['source_id'], 'integer');
$model->addRule(['comparisons'], 'required');
$model->addRule(['test'], 'trim');
$model->addRule(['use_previous_saved'], 'integer');
$model->addRule(['profile'], 'string');
$model->addRule(['ignore_step_3'], 'string');
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

<div>
  <h2 class="export_step_2_title" data-source_id="<?=Source::get_source($source_id)['source_id']?>">Выгрузка: Step 2 (<?=Source::get_source($source_id)['source_name']?>)</h2>
  <?php $form = ActiveForm::begin(['action' => '/exports/step_3',]); ?>

    <?= $form->field($model,'source_id')->hiddenInput(['value' => $source_id])->label(false); ?>

    <?= $form->field($model,'comparisons')->radioList(
      [
        'PRE_MATCH' => 'Prematch',
        'match' => 'Match',
        'mismatch' => 'Mismatch',
        'other' => 'Other',
        'YES_NO_OTHER' => 'Result (Match, Prematch, Other)',
        //'nocomare' => 'nocompare',
      ],
      [ // можно без этого массива
        'item' =>
          function($index, $label, $name, $checked, $value)
          {
            return Html::radio($name,$label === 'match',['label'=>$label,'value' => $value]).'<br/>';
          },
      ]

    )->label('<strong>Выберите сравнение:</strong>')?>

    <?= $form->field($model, 'profile')->dropDownList($profiles_list) ?>

<!-- $profile_list -->

    <?= $form->field($model, 'use_previous_saved')->checkbox(
      [
        'label' => 'использовать сохраненные ранее поля',
        'checked' => true
      ]
    )?>
  <?= $form->field($model, 'ignore_step_3')->checkbox(
    [
      'label' => 'пропустить шаг 3',
      'checked' => false
    ]
  )?>



  <div class="row">
    <div class="col">
      <span onclick="window.location = '/exports';" class="btn btn-secondary btn-block">Назад</span>
    </div>
    <div class="col">
      <button type="submit" class="btn btn-primary btn-block js-next">Далее</button>
      <a style="display: none" href="" class="btn btn-primary js-download">Скачать</a>
    </div>

  </div>

  <?php ActiveForm::end() ?>

</div>
<script src="/js/exports_step_2.js" defer></script>

