<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\ProductSearch;

/* @var $this yii\web\View */
/* @var $model backend\models\User */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => Yii::t('site', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$productSearch = new ProductSearch(); //Порнография

?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('site', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php if (! \Yii::$app->authManager->getAssignment('admin', $model->id)): ?>
        <?= Html::a(Yii::t('site', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('site', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
        <?php endif ?>
        <?= Html::a(Yii::t('site', 'Comparisons'), ['product/index', $productSearch->formName() . '[user]' => $model->username], ['class' => 'btn btn-info']) ?>
        <?= Html::a(Yii::t('site', 'Messages'), ['message/user', 'user' => $model->id], ['class' => 'btn btn-info']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'email:email',
            [
                'attribute' => 'status',
                'format' => 'status'
            ],
            [
                'attribute' => 'created_at',
                'format' =>  ['date', 'HH:mm, dd.MM.YYYY'],
            ],
            [
                'attribute' => 'updated_at',
                'format' =>  ['date', 'HH:mm, dd.MM.YYYY'],
            ],
        ],
    ]) ?>

</div>
