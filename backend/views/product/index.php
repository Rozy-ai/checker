<?php

use backend\components\TableView;
use backend\components\TopSlider;
use backend\controllers\StatsController;
use common\models\Comparison;
use common\models\HiddenItems;
use yii\helpers\Html;

/*
 * @var string $f_source
 * @var string $f_profile
 * @var string $f_count_products_on_page
 * $var int    $f_number_page_current
 * @var string $f_asin
 * @var string $f_title
 * @val string $f_status
 * @var string $f_username
 * @var string $f_comparison_status
 * @var string $f_sort
 * @var string $f_detail_view
 * @var string $f_categories_root
 * $var string $f_batch_mode
 * $var string $f_hide_mode
 * 
 * @var array  $list_source
 * @var array  $list_profiles
 * @var array  $list_categories_root
 * @var array  $list_username
 * @var array  $list_comparison_statuses
 * @var array  $list
 * 
 * @var int    $count_products_all
 * @var int    $count_products_right
 * @var bool   $is_admin
 * @var string $default_price_name
 * @var int    $count_pages
 * @var Source $source
 * @var array  $local_import_stat
 * @var        $last_update
 */

$this->title = $source->name . " | " . Yii::t('site', 'Products');
$this->params['breadcrumbs'][] = Yii::t('site', 'Products');
$this->params['breadcrumbs'][] = [
    'label' => $source->name . "&emsp;" . ($source->country ? Html::img('@web/img/flags-normal/'.$source->country.'.png', ['alt' => '', 'style'=>['height' => 'auto', 'width'=> '30px']]) : ''),
    'template' => '<li style="width: auto;">{link}</li>',
    'encode' => false
];
//if ($is_admin) {
    $this->params['breadcrumbs'][] = [
        'label' => Html::dropDownList('f_profile', $f_profile, $list_profiles, ['id' => 'id_f_profile', 'class' => 'form-control form-control-sm w-auto']),
        'template' => '<li>{link}</li>',
        'encode' => false
    ];
//}
$is_active_show_all = True;
if ($count_products_all > 200) {
    $is_active_show_all = False;
}
$is_active_show_all ? $list_count_products_on_page['ALL'] = 'ВСЕ' : '';

$this->params['breadtail'] = '<div class="d-inline-block cnt-items" id="id_block_count">Показано '
    . min($f_count_products_on_page, $count_products_all) . '(' . $count_products_right .') из ' . $count_products_all . ' </div> по: '
    . Html::dropDownList('f_count_products_on_page', $f_count_products_on_page, $list_count_products_on_page, ['id' => 'id_f_count_products_on_page', 'class' => 'form-control form-control-sm d-inline-block w-auto']);
$local_import_stat = null;

$last_local_import_txt = StatsController::getStatsLastLocalImportMessage();

\backend\assets\IconsAsset::register($this);
\backend\assets\ProductIndexAsset::register($this);
?>

<script>
    document.body.classList.add('loaded_hiding');
    window.onload = function() {
        document.getElementById("show_all").click();
        if ($('#id_f_comparison_status').val() === "MISMATCH" || $('#id_f_comparison_status').val() === "PRE_MATCH") {
            document.getElementById("show_all").click();
        }

        // window.setTimeout(function () {
            document.body.classList.add('loaded');
            document.body.classList.remove('loaded_hiding');
        // }, 500);
    };
</script>

<div class="[ PRODUCTS ]">
    <div class="position-1">
        <div class="[ FILTER-ITEMS ] products__filter-items mt-0">
            <!--<form method="get" action="change-filters" id="id_products__filter-form">-->
            <div class="form-row js-title-and-source_selector">
                <!--div class="form-group _col-sm-2" style="width: 128px">
                    <div class="titleName" style="margin-top: 5px;"><?= Html::encode(Yii::t('site', 'Products')) ?></div>
                </div-->

                <!--div class="form-group _col-sm-2" style="width: 128px">
                    <select name="f_source" id="id_f_source" class="form-control">
                        <?php
                        if ($list_source) {
                            foreach ($list_source as $item) {
                                $selected = ($item->id === $f_source) ? 'selected' : '';
                                echo "<option value=$item->id $selected>$item->name</option>";
                            }
                        }
                        ?>
                    </select>
                </div-->

                <?php if ($is_admin) : ?>
                    <!--div class="form-group _col-sm-2" >
                        <select name="f_profile" id="id_f_profile" class="form-control ">
                            <?php
                            if ($list_profiles) {
                                foreach ($list_profiles as $k_profile => $profile) {
                                    $selected = ($k_profile === $f_profile) ? 'selected' : '';
                                    echo "<option value=$k_profile $selected>$profile</option>";
                                }
                            }
                            ?>
                        </select>
                    </div-->
                <?php endif; ?>

                <div class="form-group _col-sm-2 filter-items__last-update" >
                    last <?php echo Html::a('update','/import/local_import?source_id='.$f_source, ['title' => $last_local_import_txt]) ?>:
                    <?= $last_update->created ?? 'Нет данных' ?>
                </div>

            </div>

            <div class="form-row form-inline" style="width: 100%;">
                <?php
                $profile_path = $f_profile ?? 'Все';
                if ($profile_path === '{{all}}')
                    $profile_path = 'Все';
                ?>
                <!--div class="cnt-items col-sm-6" id="id_block_count">Показаны записи <?= min($f_count_products_on_page, $count_products_all) ?> из <?= $count_products_all; ?> (<?= $count_products_right ?>) Источник <?= $source->name ?> / <?= $profile_path ?></div-->

                <!--div class="cnt-items col-sm-6" style="    text-align: right; padding-right: 0;">
                    <span>Показывать по:&nbsp;&nbsp;</span>
                    <select name="f_count_products_on_page" id="id_f_count_products_on_page" class="form-control ">
                        <?php foreach ($list_count_products_on_page as $pnl):?>
                        <option value="<?= $pnl ?>" <?= ((int) $f_count_products_on_page === $pnl) ? 'selected' : '' ?>><?= $pnl ?></option>
                        <?php endforeach;?>
                        <option value="ALL" <?= ($f_count_products_on_page === 'ALL') ? 'selected' : '' ?> >ВСЕ</option>
                    </select>
                </div-->
            </div>

            <div class="form-row">
                <div class="form-group _col-sm-2" style="width: 128px">
                    <input
                        value="<?= $f_asin ?>"
                        type="text" class="form-control"
                        placeholder="ASIN" 
                        id="id_f_asin"
                        name="f_asin"
                        >
                </div>


                <div class="form-group _col-sm-3" style="width: 200px">
                    <select name="f_categories_root" id="id_f_categories_root" class="form-control">
                        <option value="">Categories:Root</option>
                        <?php foreach ($list_categories_root as $where_3_item => $cnt):?>
                        <option
                            value="<?= $where_3_item ?>"
                            <?= ($f_categories_root == $where_3_item) ? 'selected' : '' ?>
                            ><?= $where_3_item ?> (<?= $cnt ?>)</option>
                        <?php endforeach;?>
                    </select>
                </div>

                <div class="form-group _col-sm-2">
                    <input
                        value="<?= $f_title ?>"
                        type="text" class="form-control" placeholder="Title"  id="id_f_title" name="f_title">
                </div>

                <div class="form-group _col-sm-2">
                    <select name="f_status" id="id_f_status" class="form-control">
                        <option value="">Status</option>        
                        <option 
                            value="<?= HiddenItems::STATUS_NOT_FOUND ?>"
                            <?= ($f_status == HiddenItems::STATUS_NOT_FOUND) ? 'selected' : ''; ?>
                            ><?= HiddenItems::getTitleStatuses(HiddenItems::STATUS_NOT_FOUND) ?>
                        </option>                                                   
                        <option 
                            value="<?= HiddenItems::STATUS_CHECK ?>"
                            <?= ($f_status == HiddenItems::STATUS_CHECK) ? 'selected' : ''; ?>
                            ><?= HiddenItems::getTitleStatuses(HiddenItems::STATUS_CHECK) ?>
                        </option>                           
                        <option 
                            value="<?= HiddenItems::STATUS_ACCEPT ?>"
                            <?= ($f_status == HiddenItems::STATUS_ACCEPT) ? 'selected' : ''; ?>
                            ><?= HiddenItems::getTitleStatuses(HiddenItems::STATUS_ACCEPT) ?>
                        </option>
                        <option 
                            value="<?= HiddenItems::STATUS_NO_ACCEPT ?>"
                            <?= ($f_status == HiddenItems::STATUS_NO_ACCEPT) ? 'selected' : ''; ?>
                            ><?= HiddenItems::getTitleStatuses(HiddenItems::STATUS_NO_ACCEPT) ?>
                        </option>
                    </select>
                </div>

                <?php if ($is_admin): ?>
                <div class="form-group _col-sm-3">
                    <select name="f_username" id="id_f_username" class="form-control">
                        <option value="">User</option>
                        <?php
                        foreach ($list_username as $key => $data) {
                            $name = $data['name'];
                            $count = $data['count'];
                            $is_active = ($key == $f_username) ? 'selected' : '';
                            $st = "<option value=$key $is_active>$name ($count)</option>";
                            echo $st;
                        }
                        ?>
                    </select>
                </div>
                <?php endif;?>


                <div class="form-group _col-sm-3">
                    <select name="f_comparison_status" id="id_f_comparison_status" class="form-control">
                        <?php
                        foreach ($list_comparison_statuses as $key => $data) {
                            $name = $data['name'];
                            $count = $data['count'];
                            $is_active = ($key == $f_comparison_status) ? 'selected' : '';
                            $st = "<option value=$key $is_active>$name ($count)</option>";
                            echo $st;
                        }
                        ?>
                        <option value="" <?=$f_comparison_status?'':'selected'?>>All</option>
                        <?php if (0):?>
                        <option value="YES_NO_OTHER" <?= ($f_comparison_status === 'YES_NO_OTHER') ? 'selected' : '' ?>>Result</option>
                        <?php foreach ($list_comparison_statuses as $k_6 => $where_6_item):?> 
                        <option value="<?= $k_6 ?>" <?= ($f_comparison_status === $k_6) ? 'selected' : '' ?>>
                            <?= ($k_6 === 'MISMATCH') ? 'Mismatch (No)' : '' ?>
                            <?= ($k_6 === 'PRE_MATCH') ? 'Pre_match (Yes?)' : '' ?>
                            <?= ($k_6 === 'MATCH') ? 'Match (Yes)' : '' ?>
                            <?= ($k_6 === 'OTHER') ? 'Other' : '' ?>
                            <?= ($k_6 === 'NOCOMPARE') ? 'Nocompare' : '' ?>
                            <?php if (0):?>
                            [<?= $k_6 ?>] (<?= $where_6_item ?>)
                            <?php endif;?>
                        </option>
                        <?php endforeach; ?>
                        <option value="ALL" <?= ($f_comparison_status === 'ALL') ? 'selected' : '' ?>>All</option>
                        <?php endif;?>

                    </select>
                </div>

                <div class="form-group _col-sm-3">
                    <select name="f_sort" id="id_f_sort" class="form-control">
                        <option value="">Сортировать по</option>
                        <option value="created_ASC" <?= ($f_sort === 'created_ASC') ? 'selected' : '' ?> >дате добавления ↑</option>
                        <option value="created_DESC" <?= ($f_sort === 'created_DESC') ? 'selected' : '' ?> >дате добавления ↓</option>
                        <option value="updated_ASC" <?= ($f_sort === 'updated_ASC') ? 'selected' : '' ?> >дате обновления ↑</option>
                        <option value="updated_DESC" <?= ($f_sort === 'updated_DESC') ? 'selected' : '' ?> >дате обновления ↓</option>
                    </select>
                </div>

                <?php if ($is_admin): ?>
                <div class="form-group _col-sm-3" >
                    <select name="f_detail_view" id="id_f_detail_view" class="form-control ">
                        <option value="0" <?= ($f_detail_view === '0')? 'selected':'' ?>>Кратко</option>
                        <option value="1" <?= ($f_detail_view === '1')? 'selected':'' ?>>Подробно</option>
                        <option value="2" <?= ($f_detail_view === '2')? 'selected':'' ?>>Кратко со списком</option>
                        <option value="3" <?= ($f_detail_view === '3')? 'selected':'' ?>>Подробно со списком</option> 
                    </select>
                </div>

                <?php endif; ?>

                <?php if (0): ?>
                <div class="custom-control custom-switch">
                    <input 
                        type="checkbox" 
                        class="custom-control-input" 
                        id="id_f_batch_mode" 
                        name="f_batch_mode"
                        <?= $f_batch_mode ? 'checked' : '' ?>
                    >
                    
                    <label 
                        class="custom-control-label"  
                        for="id_f_batch_mode"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Для сохранения значений статусов правых товаров необходимо сменить любой фильтр"
                    >Пакетный режим
                    </label>
                </div>
                <?php endif; ?>    
                <?php 
                if (0): 
                ?>
                <div class="custom-control custom-switch">
                    <input 
                        type="checkbox" 
                        class="custom-control-input" 
                        id="id_f_hide_mode" 
                        name="f_hide_mode"
                        <?= $f_hide_mode?'checked' : '' ?>
                    >

                    <label 
                        class="custom-control-label"  
                        for="id_f_hide_mode"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="При включении выбранные элементы скрываются визуально"
                    >Скрывать выбраные
                    </label>
                </div>                
                <?php endif; ?>
                <?php if (0): ?>
                <div class="custom-control custom-switch">
                    <div style="margin: 12px 10px 12px 0">
                        <input
                        <?= !empty($f_no_compare) ? 'checked' : '' ?>
                            name="f_no_compare" type="checkbox" class="custom-control-input" id="id_f_no_compare">
                        <label class="custom-control-label" for="f_no_compare" style="margin-left: 7px; position: relative;">
                            <span style="top: 5px; position: relative;">No compare</span>
                        </label>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-group _col-sm-3">
                    <a href="/product/" class="btn btn-secondary" id="id_button_reset_filters">Сбросить</a>
                </div>
            </div>
            <!--</form>-->
        </div>
    </div>

    <div class="table-responsive__" id = "id_table_container">
        <?=
        $this->render('index_table', [
            'list' => $list,
            'local_import_stat' => $local_import_stat,
            'is_admin' => $is_admin,
            'f_comparison_status' => $f_comparison_status,
            'f_profile' => $f_profile,
            'f_no_compare' => $f_no_compare,
            'f_detail_view' => $f_detail_view,
            'f_hide_mode' => $f_hide_mode,
            'source' => $source,
        ]);
        ?>
    </div><!-- table-responsive -->

    <div class="row">
        <div class="col">
            <?php
            echo '<div class="d-inline-block cnt-items" id="id_block_count">Показано '
                . min($f_count_products_on_page, $count_products_all) . '(' . $count_products_right .') из ' . $count_products_all . ' </div> по: '
                . Html::dropDownList('f_count_products_on_page', $f_count_products_on_page, $list_count_products_on_page, ['id' => 'id_f_count_products_on_page_footer', 'class' => 'form-control form-control-sm d-inline-block w-auto']);
            ?>


<!--            <div class="featured-items">Показаны записи --><?php //= min($f_count_products_on_page, $count_products_all) ?><!-- из --><?php //= $count_products_all ?><!--.</div>-->

            <?php
            $e_comparison = isset($f_comparison_status) && $f_comparison_status ? strtolower($f_comparison_status) : 'match';
            $e_profile = isset($f_profile) && $f_profile && $f_profile !== 'Все' ? $f_profile : '{{all}} ';
            ?>
            <a href="<?= '/exports/step_4?source_id=' . $source->id . '&comparisons=' . $e_comparison . '&profile=' . $e_profile ?>" class="product-list-item__export js-export-step-4" >
                экспортировать
            </a>
            <?php if ($is_admin): ?>
            <a
                href="/import/step_1?source_id=<?= $source->id ?>"
                target="_blank"
                data-source_id="<?= $source->id ?>"
                class="product-list-item__import-from-sql js-import-from-sql"
                >
                загрузить SQL
            </a>
            <?php endif; ?>


        </div>
        <div class="col" style="text-align: right;max-width: 200px;">
            <div class="product-list-item__del -del-all js-del-all-visible-items">удалить все</div>
            <div class="product-list-item__reset-compare -compare-all js-reset-compare-all-visible-items">отменить все</div>
            <button id="show_all" class="product-list-item__reset-compare -compare-all js-show_products_all">показать все</button>
        </div>
    </div>

    <?php if ($f_count_products_on_page !== 'ALL'):?>
    <div class="products__pager">
        <nav aria-label="Page navigation example ">
            <ul id="id_paginator" class="pagination justify-content-center">
                <?php
                echo $this->context->indexPresenter->getHTMLPaginator($f_number_page_current, $count_pages, 5, $is_active_show_all);
                ?>

            </ul>
        </nav>
    </div>
    <?php endif;?>

</div>

<br>
<br>
<br>
<br>
<br>
<?php if (0): ?>
<br>
<br>
<br>
<br>
<br>
<br>

<?=
TableView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'summaryOptions' => [
        'class' => 'allSingle',
        'tag' => 'p'
    ],
    'headerRowOptions' => [
        'class' => 'topTable',
    ],
    'filterRowOptions' => [
        'class' => 'topSearch',
    ],
    'rowOptions' => ['class' => 'tableProducts'],
    'layout' => "{summary}\n{items}\n{pager}",
    'columns' => [
        [
            'attribute' => 'id',
            'filter' => Html::activeInput('text', $searchModel, 'id', [
                'class' => 'searchName'
            ]),
            'format' => 'raw',
            'value' => function ($model) {
                return Html::tag("div", implode("", [
                    Html::tag('p', $model->id, ['class' => 'name']),
                    Html::tag('p', $model->baseInfo["Sales Rank: Current"], ['class' => 'text']),
                    Html::tag('p', $model->baseInfo["Sales Rank: Drops last 30 days"], ['class' => 'text']),
                    Html::tag('p', $model->baseInfo["Price Amazon"], ['class' => 'text']),
                        ]), ['class' => 'idName']);
            }
        ],
        [
            'attribute' => 'target_image',
            'format' => 'raw',
            'value' => function ($model) {
                $result = '';
                $canCompare = \Yii::$app->user->can('compare-products', ['product' => $model]);
                if (isset($model->baseInfo["Image"]) and $model->baseInfo["Image"]) {
                    $images = preg_split("/[; ]/", $model->baseInfo["Image"]);
                    $result = Html::a(
                                    Html::img($images[0], ['class' => 'targetImg']) . "<p class='text'>{$model->baseInfo['Title']}</p>",
                                    ['view', 'id' => $model->id], ['class' => 'btn-image']
                            ) . "<p class='view'>{$model->baseInfo['Categories: Root']}</p>"
                            . ($canCompare ? Html::a("", ['missall', 'id' => $model->id, 'return' => true], ['class' => 'btn del']) : "");
                }
                return Html::tag('div', $result, ['class' => 'targetImg']);
            }
        ],
        [
            'attribute' => 'comparing_images',
            'format' => 'raw',
            'value' => function ($model) {
                return Html::tag('div', TopSlider::widget([
                            'page' => 0, //$pages->page,
                            'product' => $model,
                            'options' => [
                                'class' => 'sliderTop sliderProducts',
                                'salesKey' => '',
                                'delBtn' => true,
                            ],
                            'source' => $source
                        ]), ['class' => 'comparingImg']);
            }
        ],
        [
            'attribute' => 'aggregated.users',
            'format' => 'raw',
            'value' => function ($model) {
                $value = Html::tag('p', empty($model->aggregated->users) ? "(не задано)" : $model->aggregated->users, ['class' => 'name']);
                return Html::tag('div', $value, ['class' => 'agg']);
            }
        ],
        [
            'attribute' => 'comparisons',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                $statuses = [
                    Comparison::STATUS_MATCH => 0,
                    Comparison::STATUS_MISMATCH => 0,
                    Comparison::STATUS_OTHER => 0,
                ];
                foreach ($model->comparisons as $comparison):
                    $statuses [$comparison->status]++;
                endforeach;
                $counted = $model->aggregated ? $model->aggregated->counted : 0;
                $ret = Html::a("{$counted}/" . count($model->getAddInfo()), ['product/result', 'id' => $model->id], ['class' => 'name']);
                $ret .= '<br/>';
                $ret .= Html::tag(
                                'p',
                                "{$statuses[Comparison::STATUS_MATCH]}/{$statuses[Comparison::STATUS_MISMATCH]}/{$statuses[Comparison::STATUS_OTHER]}",
                                ['class' => 'name']
                );
                return Html::tag('div', $ret, ['class' => 'comparisons']);
            },
            'filter' => Html::activeDropDownList($searchModel, 'status', Comparison::getStatuses(),
                    ['prompt' => '', 'class' => 'selectName'])
        ],
    ],
]);
?>

<?php endif;?>
