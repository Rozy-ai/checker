<?

/* @var $dataProvider */

use backend\models\Settings__common_fields;
use backend\models\Source;
use yii\helpers\Html;

$this->title = Yii::t('site', 'Список источников');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>

  <div class="table-responsive">
    <?=
    \yii\grid\GridView::widget([
      'dataProvider' => $dataProvider,

      'columns' => [
        'id','name', 'table_1','table_2',
        [
          'attribute' => 'Дата последнего товара',
          'value' => 'import_local__max_product_date'
        ],
        [
          'attribute' => 'Edit',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Html::a('Edit','/settings/source_edit?id='.$itm->id,['type' => "submit", 'class' => 'btn btn-primary']);
          }
        ],

        [
          'attribute' => 'Import',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Html::a('Import','/import/step_1?source_id='.$itm->id,['type' => "submit", 'class' => 'btn btn-primary']);
          }
        ],

        [
          'attribute' => 'local import',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Html::a('Start','/import/local_import?source_id='.$itm->id,['type' => "submit", 'class' => 'btn btn-primary']);
          }
        ],

      ],
    ])
    ?>


  </div>
  <?=Html::a('Добавить','/settings/source_edit',['type' => "submit", 'class' => 'btn btn-primary']);?>


</div>

