<?

/* @var $dataProvider */
/* @var string $section */

use backend\models\Settings__fields_extend_price;
use common\models\Source;
use yii\helpers\Html;

//$this->title = Yii::t('site', 'Поля при наведении на Price');
$this->title = $settingsRow ? $settingsRow->title : Yii::t('site', 'Поля при наведении');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>

  <div class="table-responsive">
    <?=
    \yii\grid\GridView::widget([
      'dataProvider' => $dataProvider,

      'columns' => [
        'id', 'name',
        [
          'attribute' => 'source_id',
          'format' => 'text', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Source::findOne(['id' => $itm->source_id])->name;
          }

        ],
        'default', 'title',
        [
          'attribute' => 'Edit',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) use ($section) {
            return Html::a('Edit', '/settings/fields_extend_price_edit?id=' . $itm->id . '&section=' . $section, ['type' => "submit", 'class' => 'btn btn-primary']);
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
  <?= Html::a('Добавить', '/settings/fields_extend_price_edit?section=' . $section, ['type' => "submit", 'class' => 'btn btn-primary']); ?>
</div>