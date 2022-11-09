<?php

use backend\models\ExternalUser;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\jui\DatePicker;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var backend\models\search\ExternalUsersSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'External Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="external-user-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <a href="<?= Url::to(['create'])?>" class="btn btn-success">Create External User</a>
    </p>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'options' => [
                    'style' => 'width: 100px'
                ],
            ],
            'login',
            'email:email',
            [
                'class' => ActionColumn::class,
                'options' => [
                    'style' => 'width: 100px'
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
