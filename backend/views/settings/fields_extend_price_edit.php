<?

/* @var $dataProvider */
/* @var $item_res */
/* @var $fields_in_source */
/* @var $source_list */
/* @var $type_list */
/* @var $return */
/* @var $source_id_for_input */


use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('site', 'Редактирование полей для price');
//$this->params['breadcrumbs'][] = $this->title;
// \backend\assets\SettingsAsset::register($this);
?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>
  <?php $form = ActiveForm::begin(['id' => 'settings__fields_extend_price']); ?>

  <?= $form->field($item, 'section')->hiddenInput(['value' => $section])->label(false) ?>
  <div style="display: none">
    <?= $form->field($item, 'id')->input('text', ['value' => $item_res->id]) ?>
    <input type="hidden" class="form-control" name="return" value="<?= $return ?>" />
  </div>

  <?= $form->field($item, 'source_id')->dropDownList(
    $source_list,
    ['prompt' => 'Source', 'options' => [$item_res->source_id => ['selected' => true]]]
  )->label(false); ?>

  <?= $form->field($item, 'name')->input('text', ['value' => $item_res->name, 'placeholder' => 'key'])->label(false); ?>

  <?= $form->field($item, 'title')->input('text', ['value' => $item_res->title, 'placeholder' => 'title'])->label(false); ?>

  <?= $form->field($item, 'default')->checkbox(
    [
      'checked' => $section === 'price' ? ($item_res->default ? true : false) : false,
      'class' => $section === 'price' ? '' : 'd-none',
      'label' => $section === 'price' ? 'price по умолчанию' : null,
    ]
  )->label(false); ?>


  <?php if ($item_res->id) : ?>
    <div class="form-row">

      <div class="col-sm-6 mb-3">
        <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block']) ?>
      </div>

      <div class="col-sm-6 mb-3">
        <?= Html::a("Удалить", '/settings/common_field_delete?id=' . $item_res->id, ['class' => 'btn btn-danger btn-block']) ?>
      </div>

    </div>
  <?php else : ?>
    <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block']) ?>
  <?php endif; ?>

  <?php ActiveForm::end(); ?>

  <div>
    <?php $keysFileName = $section . "_keys.txt"; ?>
    Перечень: (находится в корне под именем '<?= $keysFileName ?>')
    <pre>
<?php $price_keys_path =  __DIR__ . '/../../../' . $keysFileName; ?>
<?= (file_exists($price_keys_path)) ? file_get_contents($price_keys_path) : 'фаил не найден' ?>
</pre>
  </div>
</div>