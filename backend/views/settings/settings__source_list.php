<?php

/* @var $dataProvider */

use backend\models\Settings__common_fields;
use common\models\Source;
use common\models\Stats_import_export;
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
          'value' => function ($itm,$stats) {
            $html = '<div>';
            $html.=  '<div>'.Html::a('Import','/import/step_1?source_id='.$itm->id,['type' => "submit", 'class' => 'btn btn-primary']).'</div>';
            $html.=  '<div>'.Html::tag('span',Stats_import_export::getLastOtherImport($itm->id)->created ).'</div>';
            $html.= '</div>';
            return $html;
          }
        ],

        [
          'attribute' => 'local import',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            $html = '<div>';
            $html.=  '<div>'.Html::a('Start','/import/local_import?source_id='.$itm->id,['type' => "submit", 'class' => 'btn btn-primary js-preload']).'</div>';
            $html.=  '<div>'.Html::tag('span',Stats_import_export::getLastLocalImport($itm->id)->created ).'</div>';
            $html.= '</div>';
            return $html;
          }
        ],

      ],
    ])
    ?>


  </div>
  <?=Html::a('Добавить','/settings/source_edit',['type' => "submit", 'class' => 'btn btn-primary']);?>


</div>

