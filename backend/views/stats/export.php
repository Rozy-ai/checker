<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StatsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $dataProvider_import yii\data\ActiveDataProvider */
/* @var $dataProvider_export yii\data\ActiveDataProvider */

$this->title = Yii::t('site', 'Stats');
if (\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) !== null):
    if (! $searchModel->total):
        $this->title .= ' (' . Yii::t('site', 'by users') . ')';
    else:
        $this->title .= ' (' . Yii::t('site', 'summary') . ')';
    endif;
endif;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stats-index">

  <h1>Статистика Export</h1>

  <?= GridView::widget([
    'dataProvider' => $dataProvider_export,
    //'showHeader' => false,
    //'filterModel' => $searchModel,
    'columns' => [
      'created',
      [
        'attribute' => 'file_name',
        'format' => 'raw',
        'value' => function ($itm) {
          return Html::a($itm->file_name, '/export/'.$itm->file_name , ['class' => '']);
        },
      ],


      [
        'attribute' => 'comparison',
        'format' => 'raw',
        'value' => function ($itm) {

          return \common\models\Comparison::get_status_by_code($itm['comparison'])['name'];
          //return Html::a($itm->title, '/'.$itm->route , ['class' => 'idName']);
        },
        'options' => [ 'class' => 'comparison']
      ],

      'cnt',
      [
        'attribute' => 'source_id',
        'format' => 'raw',
        'value' => function ($itm) {

          return \backend\models\Source::get_source($itm['source_id'])['source_name'];
        },
        'options' => [ 'class' => 'source_id']
      ],


      'profile',
    ],
    'options' => [ 'class' => 'export']
  ]); ?>
  <a class="btn btn-secondary" href="/stats">назад</a>


</div>

<style>
  .import table th:nth-child(1), .export table th:nth-child(1){
    width: 108px;
  }
  .import table .cnt, .export table .cnt{
    width: 60px;
  }
  .export table .comparison{
    width: 120px;
  }
  .import table .source_id, .export table .source_id{
    width: 100px;
  }
  .import table tbody td, .export table tbody td{
    word-wrap: break-word;
    word-break: break-word;
  }
</style>