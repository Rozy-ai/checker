<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('site', "Result");
$this->params['breadcrumbs'][] = ['label' => Yii::t('site', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => "#{$model->id}", 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;

\backend\assets\IconsAsset::register($this);

$columns = [
                'user.username',
                'status',
                'message',
    ];

if (\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) !== null):
    $columns [] = 'url';
endif;

$columns = array_merge (
        $columns,
        [
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    return strftime('%d.%m.%Y (%H:%M)', $model->created_at);
                }
            ],
            [
                'attribute' => Yii::t('site', 'View'),
                'format' =>'raw',
                'value' => function ($model) {
                    return Html::a('<i class="bi bi-eye-fill icon-custom"></i>', ['view', 'id' => $model->product_id, 'node' => $model->node + 1]);
                }
            ]
        ]);
?>
<div class="product-result">

    <h1><?= Html::encode($this->title) ?></h1>

    <p></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $model,
        'columns' => $columns
        ]) ?>
</div>