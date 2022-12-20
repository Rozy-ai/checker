<?php

use kartik\date\DatePicker;
use yii\helpers\StringHelper;
use yii\widgets\Pjax;
use yii\helpers\Html;
use yii\grid\GridView;
use frontend\models\Billing;

/** @var yii\web\View $this */
/** @var frontend\models\search\BillingSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Billing';
$this->params['breadcrumbs'][] = $this->title;

$dateFieldId = Html::getInputId($searchModel, 'date');
Pjax::begin();
?>
<div class="billing-index">
    <div class="d-flex justify-content-between">
        <h1><?= Html::encode($this->title) ?></h1>
        <span>Balance: <?= Yii::$app->getFormatter()->asCurrency(Yii::$app->getUser()->getBalance())?></span>
    </div>

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
            [
                'attribute' => 'sum',
                'format' => 'currency',
                'options' => [
                    'style' => 'width: 180px'
                ],
            ],
            //'source',
            [
                'attribute' => 'date',
                'format' =>  'dateTime',
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'date',
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy'
                    ]
                ])
            ],
            [
                'attribute' => 'description',
                'format' => 'raw',
                'value' => function(Billing $billing) {
                    if (strlen($billing->description) > 50) {
                        return Html::tag('abbr', StringHelper::truncate($billing->description, 50), [
                            'data-toggle' => 'tooltip',
                            'title' => Html::encode($billing->description)
                        ]);
                    }
                    return Html::encode($billing->description);
                }
            ],
        ],
    ]); ?>


</div>

<?php Pjax::end(); ?>