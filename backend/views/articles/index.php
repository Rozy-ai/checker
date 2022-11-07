<?
/* @var $dataProvider */

use yii\helpers\Html;
$this->title = Yii::t('site', 'Статьи на главной');
?>

<div class="table-responsive">
  <h1><?= Html::encode($this->title) ?></h1>

  <?= \yii\grid\GridView::widget([
  'dataProvider' => $dataProvider,
  'showHeader' => false,
  'columns' => [
    [
      'attribute' => 'title',
      'format' => 'raw',
      'value' => function ($itm) {
        return Html::a($itm->title, '/articles/edit?id='.$itm->id );
      }
    ],


  ],
]);
?>
</div>

<a href="/articles/edit?id=add" class="btn btn-primary btn-block">Добавить</a>

