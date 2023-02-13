<?

/* @var $dataProvider */
/* @var $item_res */
/* @var $common_fields */
/* @var $source_list */
/* @var $type_list */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;


//$Country_codes = ['ru' => 'Россия', 'uk' => "Англия", 'us' => "США", 'ua' => "Украина", 'tr' => "Турция", 'ch' => "Китай", 'fr' => "Франция", 'vt' => "Вьетнам", 'it' => "Италия", 'pl' => "Польша"];
$Country_codes = [
    'us' => html_entity_decode('🇺🇸&emsp;United States'),
    'ca' => html_entity_decode('🇨🇦&emsp;Canada'),
    'mx' => html_entity_decode('🇲🇽&emsp;Mexico'),
    'br' => html_entity_decode('🇧🇷&emsp;Brazil'),
    'uk' => html_entity_decode('🏴󠁧󠁢󠁥󠁮󠁧󠁿󠁧󠁢󠁥󠁮󠁧󠁿&emsp;England'),
    'de' => html_entity_decode('🇩🇪&emsp;Germany'),
    'fr' => html_entity_decode('🇫🇷&emsp;France'),
    'it' => html_entity_decode('🇮🇹&emsp;Italy'),
    'es' => html_entity_decode('🇪🇸&emsp;Spain'),
    'jp' => html_entity_decode('🇯🇵&emsp;Japan'),
    'in' => html_entity_decode('🇮🇳&emsp;India'),
    'ru' => html_entity_decode('🇷🇺&emsp;Russia')
];




$this->title = Yii::t('site', 'Источники');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>
  <?php $form = ActiveForm::begin(['id' => 'source_edit']); ?>

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
    <?=$form->field($item, 'country')->dropDownList(
        $Country_codes
        ,[ 'prompt'=>'Выберите страну', 'options' => [ $item_res->country => [ 'selected'=> true ], 'multiple'=>'multiple'] ])
        ->label('привязка к стране');?>


  <?php if ($item_res->id): ?>
  <div class="form-row">

    <div class="col-sm-6 mb-3">
      <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block'])?>
    </div>

    <div class="col-sm-6 mb-3">
      <?= Html::a("Удалить", '/settings/source_delete?id='.$item_res->id, ['class' => 'btn btn-danger btn-block'])?>
    </div>

  </div>
  <?php else: ?>
    <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block'])?>
  <?php endif;?>

  <?php ActiveForm::end(); ?>
</div>

