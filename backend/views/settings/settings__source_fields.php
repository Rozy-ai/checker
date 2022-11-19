<?

/* @var $dataProvider */

use backend\models\Settings__common_fields;
use common\models\Source;
use yii\helpers\Html;

$this->title = Yii::t('site', 'Сопоставление общих полей и полей из разных источников');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>

  <div class="table-responsive">
    <?=
    \yii\grid\GridView::widget([
      'dataProvider' => $dataProvider,

      'columns' => [
        'id',
        [
          'attribute' => 'settings__common_fields_id',

          'format' => 'text', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Settings__common_fields::findOne(['id' => $itm->settings__common_fields_id])->name;
          }

        ],
        [
          'attribute' => 'source_id',
          'format' => 'text', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Source::findOne(['id' => $itm->source_id])->name;
          }

        ],


        'name','type',
        [
          'attribute' => 'Edit',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Html::a('Edit','/settings/source_fields_edit?id='.$itm->id,['type' => "submit", 'class' => 'btn btn-primary']);
          }
        ],

        /*
        [
          'attribute' => 'title',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Html::input('text','title',$itm->title, ['class' => 'left']);
          }
        ],
        [
          'attribute' => 'item_1_key',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Html::input('text','item_1_key',$itm->item_1_key, ['class' => 'item_1_key']);
          }
        ],

        [
          'attribute' => 'item_2_key',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Html::input('text','item_2_key',$itm->item_2_key, ['class' => 'item_2_key']);
          }
        ],

        [
          'attribute' => 'Save',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Html::button('Save',['type' => "submit", 'class' => 'btn btn-primary']);
          }
        ],
        */

      ],
    ])
    ?>


  </div>
  <?=Html::a('Добавить','/settings/source_fields_edit',['type' => "submit", 'class' => 'btn btn-primary']);?>


</div>

