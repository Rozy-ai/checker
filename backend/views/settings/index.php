<?

/* @var $dataProvider */
use yii\helpers\Html;

$this->title = Yii::t('site', 'Настройки');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>

  <div class="table-responsive">
    <?=
    \yii\grid\GridView::widget([
      'dataProvider' => $dataProvider,
      'showHeader' => false,
      'columns' => [
        /*'id',*/
        [
          'attribute' => 'title',
          'format' => 'raw',
          'value' => function ($itm) {
            return Html::a($itm->title, '/'.$itm->route , ['class' => 'idName']);
          }
        ],

      ],
    ])
    ?>
  </div>


</div>

