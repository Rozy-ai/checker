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
    if (!$searchModel->total):
        $this->title .= ' (' . Yii::t('site', 'by users') . ')';
    else:
        $this->title .= ' (' . Yii::t('site', 'summary') . ')';
    endif;
endif;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stats-index">

    <?php if (0): ?>
        <p style="margin: 10px 0">
            <?php if (\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) !== null): ?>
                <?php if (!$searchModel->total): ?>
                    <?= Html::a(\Yii::t('site', 'Summary'), ['index', 'mode' => 'total'], ['class' => 'btn btn-secondary']) ?>
                <?php else: ?>
                    <?= Html::a(\Yii::t('site', 'By users'), ['index', 'mode' => 'users'], ['class' => 'btn btn-secondary']) ?>
                <?php endif ?>
            <?php endif ?>
        </p>
    <?php endif; ?>

    <div style="margin: 25px 0 0 0"></div>
    <h4><?= Html::encode($this->title) ?></h4>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link <?= $searchModel->total ? 'active' : '' ?>" href="/stats/index?mode=total">Summary</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= !$searchModel->total ? 'active' : '' ?>" href="/stats/index?mode=users">By users</a>
        </li>


    </ul>
    <br>

<?php
    $month_list = array_combine(range(1, 12), range(1, 12));
    $year_list = array_combine(range(2021, strftime('%Y')), range(2021, strftime('%Y')));

    $columns = [
        ['class' => 'yii\grid\SerialColumn'],
    ];
    if (!$searchModel->total) {
        $columns [] = [
            'attribute' => 'username',
            'value' => 'user.username'
        ];
    }
    $columns [] = [
        'attribute' => 'date',
        'value' => function ($model) {
            $ret = '';
            $i = explode('-', $model->period);
            list($year, $month, $day) = array_pad($i, 3, 1);
            if ($model->type == 'T') {
                $ret = 'TOTAL';
            } elseif ($model->type == 'D') {
                $dt = new \DateTime();
                $dt->setDate(intval($year), $month, $day);

                $ret = $dt->format("j M Y");
            } elseif ($model->type == 'M') {
                $dt = new \DateTime();
                $dt->setDate($year, $month, 1);

                $ret = $dt->format("F Y");
            } elseif ($model->type == 'Y') {
                $dt = new \DateTime();
                $dt->setDate(intval($year), 1, 1);

                $ret = $dt->format("Y");
            }
            return $ret;
        },
        'filter' =>
        Html::beginTag('div', ['class' => 'form-inline']) .
        Html::activeDropDownList($searchModel, 'year', ['' => '—'] + $year_list, ['class' => 'form-control ml-2']) . ' ' .
        Html::activeDropDownList($searchModel, 'month', ['' => '—'] + $month_list, ['class' => 'form-control ml-2']) . ' ' .
        Html::activeDropDownList(
                $searchModel, 'type',
                ['' => '—', 'T' => 'Total', 'Y' => 'By year', 'M' => 'By month', 'D' => 'By day'],
                ['class' => 'form-control ml-2']
        ) .
        Html::endTag('div'),
        'format' => 'html',
    ];

    $columns [] = [
        'attribute' => 'pre_match',
        'value' => 'pre_match_count',
    ];

    $columns [] = [
        'attribute' => 'match',
        'value' => 'match_count',
    ];

    $columns [] = [
        'attribute' => 'mismatch',
        'value' => 'mismatch_count',
    ];
    $columns [] = [
        'attribute' => 'other',
        'value' => 'other_count',
    ];
?>
<?=GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $columns,
    ]);
?>

<!--<h1><?/*= Html::encode($this->title) */?></h1>-->

<?php // echo $this->render('_search', ['model' => $searchModel]);  ?>

    <br>

    <h4>Статистика Import</h4>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider_import,
        //'showHeader' => false,
        //'filterModel' => $searchModel,
        'columns' => [
            'created', 'file_name',
            [
                'attribute' => 'cnt_products_left',
                'value' => 'cnt_products_left',
                'options' => ['class' => 'cnt_products_left']
            ],
            [
                'attribute' => 'cnt_products_rigth',
                'value' => 'cnt_products_left',
                'options' => ['class' => 'cnt_products_right']
            ],
            [
                'attribute' => 'source_id',
                'format' => 'raw',
                'value' => function ($itm) {

                    return \common\models\Source::get_source($itm['source_id'])['source_name'];
                },
                'options' => ['class' => 'source_id']
            ],
        ],
        'options' => ['class' => 'import']
    ]);
    ?>
    <a class="btn btn-primary" href="/stats/import">показать все</a>


    <br>
    <h4>Статистика Export</h4>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider_export,
        //'showHeader' => false,
        //'filterModel' => $searchModel,
        'columns' => [
            'created',
            [
                'attribute' => 'file_name',
                'format' => 'raw',
                'value' => function ($itm) {
                    return Html::a($itm->file_name, '/export/' . $itm->file_name, ['class' => '']);
                },
            ],
            [
                'attribute' => 'comparison',
                'format' => 'raw',
                'value' => function ($itm) {

                    return \common\models\Comparison::get_status_by_code($itm['comparison'])['name'];
                    //return Html::a($itm->title, '/'.$itm->route , ['class' => 'idName']);
                },
                'options' => ['class' => 'comparison']
            ],
            'cnt',
            [
                'attribute' => 'source_id',
                'format' => 'raw',
                'value' => function ($itm) {

                    return \common\models\Source::get_source($itm['source_id'])['source_name'];
                },
                'options' => ['class' => 'source_id']
            ],
            'profile',
        ],
        'options' => ['class' => 'export']
    ]);
    ?>
    <a class="btn btn-primary" href="/stats/export">показать все</a>


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
