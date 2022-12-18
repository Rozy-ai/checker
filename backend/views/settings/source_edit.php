<?

/* @var $dataProvider */
/* @var $item_res */
/* @var $common_fields */
/* @var $source_list */
/* @var $type_list */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('site', 'Источники');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>
  <? $form = ActiveForm::begin(['id' => 'source_edit']); ?>

  <div style="display: none">
  <?=$form->field($item, 'id')->input('text',['value' => $item_res->id])?>
  </div>

  <?=$form->field($item, 'name')->input('text',['value' => $item_res->name ]);?>

  <?=$form->field($item, 'table_1')->input('text',['value' => $item_res->table_1 ]);?>
  <?=$form->field($item, 'table_2')->input('text',['value' => $item_res->table_2 ]);?>

  <?=$form->field($item, 'import_local__db_import_name')->input('text',['value' => $item_res->import_local__db_import_name ])
    ->label('Имя базы данных в парсере');?>

    <?= $form->field($item, 'max_free_show_count')
        ->input('number', ['value' => $item_res->max_free_show_count])
        ->label('Ограничение кол-ва отображаемых товаров с открытыми данными для тарифа Free')
    ?>

  <?=$form->field($item, 'import__default_q_1')->dropDownList([
      'REPLACE' => 'заменить',
      'IGNORE' => 'игнорировать',
    ],[ 'prompt'=>'Select Category', 'options' => [ $item_res->import__default_q_1 => [ 'selected'=> true ]] ])
    ->label('Действие при импорте с одинаковым asin: по умолчанию');?>

  <?=$form->field($item, 'import_local__max_product_date')->input('text',['value' => $item_res->import_local__max_product_date ])
    ->label('Дата последнего импортированного товара');?>

  <?=$form->field($item, 'import__sql_file_path')->input('text',
    [
      'value' => $item_res->import__sql_file_path,
      'placeholder' => \Yii::$app->getBasePath()
    ]
  )
    ->label('Путь к sql фаилу для импорта из фаила (без загрузки)');?>


  <? if ($item_res->id): ?>
  <div class="form-row">

    <div class="col-sm-6 mb-3">
      <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block'])?>
    </div>

    <div class="col-sm-6 mb-3">
      <?= Html::a("Удалить", '/settings/source_delete?id='.$item_res->id, ['class' => 'btn btn-danger btn-block'])?>
    </div>

  </div>
  <? else: ?>
    <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block'])?>
  <? endif;?>

  <? ActiveForm::end(); ?>
</div>

