<?

/* @var $dataProvider */

use backend\models\Settings__common_fields;
use common\models\Source;
use yii\helpers\Html;

$this->title = Yii::t('site', 'Список общих полей');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>

  <div class="table-responsive">
    <?=
    \yii\grid\GridView::widget([
      'dataProvider' => $dataProvider,

      'columns' => [
        'id','name','description',
        [
          'attribute' => 'Edit',
          'format' => 'raw', // "raw", "text", "html", ['date', 'php:Y-m-d'])
          'value' => function ($itm) {
            return Html::a('Edit','/settings/common_field_edit?id='.$itm->id,['type' => "submit", 'class' => 'btn btn-primary']);
          }
        ],

      ],
    ])
    ?>


  </div>
  <?=Html::a('Добавить','/settings/common_field_edit',['type' => "submit", 'class' => 'btn btn-primary']);?>


</div>

