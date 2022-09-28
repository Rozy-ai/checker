<?

/* @var $dataProvider */
use yii\helpers\Html;

$this->title = Yii::t('site', 'Поля таблицы товара');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>

  <div class="table-responsive">
    <?=
    \yii\grid\GridView::widget([
      'dataProvider' => $dataProvider,
      'columns' => [
        'title', 'item_1_key','item_2_key',
        [
          'attribute' => 'Видимость для пользователя',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            if ((int)$itm->visible_for_user === 0) return 'только админ';
            else return 'все пользователи';
          }
        ],
        [
          'attribute' => 'Общая видимость',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            if ((int)$itm->visible === 0) return 'скрыто';
            else return 'отображается';
          }
        ],
        [
          'attribute' => 'Edit',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Html::a('Edit','/settings/table_fields_edit?id='.$itm->id,['type' => "submit", 'class' => 'btn btn-primary']);
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
  <?=Html::a('Добавить','/settings/table_fields_edit',['type' => "submit", 'class' => 'btn btn-primary']);?>


</div>

