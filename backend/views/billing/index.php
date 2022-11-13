<?php

use yii\jui\AutoComplete;
use yii\web\JsExpression;
use yii\widgets\Pjax;
use backend\models\Billing;
use yii\helpers\{Html, Url};
use yii\grid\{ActionColumn, GridView};
use common\models\Billing as BillingAlias;

/** @var yii\web\View $this */
/** @var backend\models\search\BillingSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Billing';
$this->params['breadcrumbs'][] = $this->title;

$dateFieldId = Html::getInputId($searchModel, 'date');
Pjax::begin();
?>
<div class="billing-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Billing', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

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
                'attribute' => 'status',
                'value' => 'statusText',
                'filter' => BillingAlias::STATUSES
            ],
            [
                'attribute' => 'user_id',
                'value' => 'user.login',
                'filter' => AutoComplete::widget([
                    'model' => $searchModel,
                    'attribute' => 'user_id',
                    'clientOptions' => [
                        'source' => new JsExpression("function(request, response) {
                            $.getJSON('" . Url::to(['external-users/ajax']) . "', {
                                search: request.term
                            }, response);
                        }"),
                    ],
                    'options' => [
                        'class' => 'form-control',
                        'placeholder' => Yii::t('site', 'Username')
                    ]
                ])
            ],
            'sum:currency',
            //'source',
            [
                'attribute' => 'date',
                'format' => 'datetime',
                'filter' => \kartik\date\DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'date',
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy'
                    ]
                ])
            ],
            [
                'class' => ActionColumn::className(),
            ],
        ],
    ]); ?>


</div>

<?php Pjax::end(); ?>