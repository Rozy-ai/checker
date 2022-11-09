<?php

use backend\components\TableView;
use backend\components\TopSlider;

use common\models\Comparison;
use yii\helpers\Html;
use yii\helpers\Url;

/*
 * @var string $filter_items_comparisons
 * @var string $filter_items_profile
 * @var string $filter_no_compare
 * @var bool   $filter_is_detail_view
 * 
 * @var object $last_update
 * 
 * @var array  $list_source
 * @var int    $active_id_source
 * 
 * @var array  $list_profiles
 * @var string $active_profiles
 * 
 * @var array  $list_comparison_statuses
 * @var array  $active_comparison_status
 * 
 * @var array  $list
 * @var int    $count_products_all
 * @var int    $count_products_on_page
 * @var int    $count_products_right
 * 
 * @var bool   $is_admin

 * @var string $default_price_name
 * @var string $f_items__show_n_on_page       Сколько продуктов показывать на странице
 * @var int    $number_page_current
 */

$this->title = Yii::t('site', 'Products');
$this->params['breadcrumbs'][] = $this->title;

\backend\assets\IconsAsset::register($this);
\backend\assets\ProductIndexAsset::register($this);
?>

<div class="[ PRODUCTS ]">
    <div class="position-1">
        <div class="[ FILTER-ITEMS ] products__filter-items">
            <form action="" class="products__filter-form" >
                <div class="form-row js-title-and-source_selector">
                    <div class="form-group _col-sm-2" style="width: 128px">
                        <div class="titleName" style="margin-top: 5px;"><?= Html::encode($this->title) ?></div>
                    </div>

                    <div class="form-group _col-sm-2" style="width: 128px">
                        <select name="filter-items__source" id="filter-items__source" class="form-control">
                            <?php
                                if ($list_source){
                                    foreach ($list_source as $source){
                                        $selected = ($source->id===$active_id_source)?'selected':'';
                                        echo "<option value=$source->id $selected>$source->name</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>

                    <?php if ($is_admin) :?>
                        <div class="form-group _col-sm-2" >
                            <select name="filter-items__profile" id="filter-items__profile" class="form-control ">
                                <?php
                                    if ($list_profiles){
                                        foreach ($list_profiles as $k_profile => $profile){
                                            $selected = ($k_profile===$active_profile)?'selected':'';
                                            echo "<option value=$k_profile $selected>$profile</option>";
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-group _col-sm-2 filter-items__last-update" >
                        last update:<br>
                        <?= $last_update->created; ?>
                    </div>

                </div>

                <div class="form-row form-inline" style="width: 100%;">
                    <?
                        $profile_path = $get_['filter-items__profile'] ?? 'Все';
                        if ($profile_path === '{{all}}') $profile_path = 'Все';
                    ?>
                    <div class="cnt-items col-sm-6">Показаны записи <?= $count_products_on_page ?> из <?= $count_products_all; ?> (<?= $count_products_right ?>) Источник <?= $selected_source_name ?> / <?= $profile_path ?></div>

                    <? $pages_n_list = [10,20,50,100,200]?>
                    <div class="cnt-items col-sm-6" style="    text-align: right; padding-right: 0;">
                        <span>Показывать по:&nbsp;&nbsp;</span>
                        <select name="filter-items__show_n_on_page" id="filter-items__show_n_on_page" class="form-control ">
                            <? foreach ($pages_n_list as $pnl):?>
                            <option value="<?= $pnl ?>" <?= ((int) $items__show_n_on_page === $pnl) ? 'selected' : '' ?>><?= $pnl ?></option>
                            <? endforeach;?>
                            <option value="ALL" <?= ($items__show_n_on_page === 'ALL') ? 'selected' : '' ?> >ВСЕ</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group _col-sm-2" style="width: 128px">
                        <input
                            value="<?= $get_['filter-items__id'] ?>"
                            type="text" class="form-control" placeholder="id" id="filter-items__id" name="filter-items__id">
                    </div>


                    <div class="form-group _col-sm-3" style="width: 200px">
                        <select name="filter-items__target-image" id="filter-items__target-image" class="form-control">
                            <option value="">Categories:Root</option>
                            <? foreach ($where_3_list as $where_3_item => $cnt):?>
                            <option
                                value="<?= $where_3_item ?>"
                                <?= ($get_['filter-items__target-image'] == $where_3_item) ? 'selected' : '' ?>
                                ><?= $where_3_item ?> (<?= $cnt ?>)</option>
                            <? endforeach;?>
                        </select>
                    </div>

                    <div class="form-group _col-sm-2">
                        <input
                            value="<?= $get_['filter-items__comparing-images'] ?>"
                            type="text" class="form-control" placeholder="Title"  id="filter-items__comparing-images" name="filter-items__comparing-images">
                    </div>

                    <div class="form-group _col-sm-2">
                        <select name="filter-items__product_status" id="filter-items__product_status" class="form-control">
                            <option>Status</option>
                            <option value="<?= \common\models\HiddenItems::STATUS_NO_CHECK ?>">
                                <?= \common\models\HiddenItems::getTitleStatuses(\common\models\HiddenItems::STATUS_NO_CHECK) ?>
                            </option>
                            <option value="<?= \common\models\HiddenItems::STATUS_NOT_FOUND ?>">
                                <?= \common\models\HiddenItems::getTitleStatuses(\common\models\HiddenItems::STATUS_NOT_FOUND) ?>
                            </option>
                            <option value="<?= \common\models\HiddenItems::STATUS_CHECK ?>">
                                <?= \common\models\HiddenItems::getTitleStatuses(\common\models\HiddenItems::STATUS_CHECK) ?>
                            </option>
                            <option value="<?= \common\models\HiddenItems::STATUS_ACCEPT ?>">
                                <?= \common\models\HiddenItems::getTitleStatuses(\common\models\HiddenItems::STATUS_ACCEPT) ?>
                            </option><option value="<?= \common\models\HiddenItems::STATUS_NO_ACCEPT ?>">
                                <?= \common\models\HiddenItems::getTitleStatuses(\common\models\HiddenItems::STATUS_NO_ACCEPT) ?>
                            </option>
                        </select>
                    </div>

                    <? if ($is_admin): ?>
                    <div class="form-group _col-sm-3">
                        <select name="filter-items__user" id="filter-items__user" class="form-control">
                            <option value="">User</option>
                            <? foreach ($where_4_list as $where_4_item):?>
                            <option
                                value="<?= $where_4_item['username'] ?>"
                                <?= ($get_['filter-items__user'] == $where_4_item['username']) ? 'selected' : '' ?>
                                ><?= $where_4_item['username'] ?> (<?= $where_4_item['cnt'] ?>)</option>
                            <? endforeach;?>
                        </select>
                    </div>
                    <? endif;?>


                    <div class="form-group _col-sm-3">
                        <select name="filter-items__comparisons" id="filter-items__comparisons" class="form-control">
                            <!--<option value="">Result</option>-->
                            <?// array_pop($list_statuses) ?>
                            
                            <?php 
                                foreach ( $list_comparison_statuses as $key => $data){
                                    $is_active = ($key == $active_comparison_status)?'active':'';
                                    $name = $data['name'];
                                    $count = $data['count'];
                                    echo "<option value=$key $is_active>$name ($count)</option>";
                                }
                            ?>

                            <? if (0):?>
                            <option value="YES_NO_OTHER" <?= ($get_['filter-items__comparisons'] === 'YES_NO_OTHER') ? 'selected' : '' ?>>Result</option>
                            <? foreach ($where_6_list as $k_6 => $where_6_item):?>
                            <option value="<?= $k_6 ?>" <?= ($get_['filter-items__comparisons'] === $k_6) ? 'selected' : '' ?>>
                                <?= ($k_6 === 'MISMATCH') ? 'Mismatch (No)' : '' ?>
                                <?= ($k_6 === 'PRE_MATCH') ? 'Pre_match (Yes?)' : '' ?>
                                <?= ($k_6 === 'MATCH') ? 'Match (Yes)' : '' ?>
                                <?= ($k_6 === 'OTHER') ? 'Other' : '' ?>
                                <?= ($k_6 === 'NOCOMPARE') ? 'Nocompare' : '' ?>
                                <? if (0):?>
                                [<?= $k_6 ?>] (<?= $where_6_item ?>)
                                <? endif;?>
                            </option>
                            <? endforeach; ?>
                            <option value="ALL" <?= ($get_['filter-items__comparisons'] === 'ALL') ? 'selected' : '' ?>>All</option>
                            <? endif;?>

                        </select>
                    </div>

                    <div class="form-group _col-sm-3">
                        <select name="filter-items__sort" id="filter-items__sort" class="form-control">
                            <option value="">Сортировать по</option>
                            <option value="created_ASC" <?= ($sort === 'created_ASC') ? 'selected' : '' ?> >дате добавления ↓</option>
                            <option value="created_DESC" <?= ($sort === 'created_DESC') ? 'selected' : '' ?> >дате добавления ↑</option>
                            <option value="updated_ASC" <?= ($sort === 'updated_ASC') ? 'selected' : '' ?> >дате обновления ↓</option>
                            <option value="updated_DESC" <?= ($sort === 'updated_DESC') ? 'selected' : '' ?> >дате обновления ↑</option>
                        </select>
                    </div>

                    <? if ($is_detail_view_for_items || $is_admin): ?>
                    <div class="form-group _col-sm-3" >
                        <select name="filter-items__right-item-show" id="filter-items__right-item-show" class="form-control ">
                            <option value="0" <?= ($right_item_show) ?: 'selected' ?>>Кратко</option>
                            <option value="1" <?= ($right_item_show) ? 'selected' : '' ?>>Подробно</option>
                        </select>
                    </div>

                    <? endif; ?>

                    <? if (0): ?>
                    <div class="custom-control custom-switch">

                        <div style="margin: 12px 10px 12px 0">
                            <input
                            <?= ($get_['filter-items__no-compare'] === 'on') ? 'checked' : '' ?>
                                name="filter-items__no-compare" type="checkbox" class="custom-control-input" id="filter-items__no-compare">
                            <label class="custom-control-label" for="filter-items__no-compare" style="margin-left: 7px; position: relative;">
                                <span style="top: 5px; position: relative;">No compare</span>
                            </label>
                        </div>
                    </div>
                    <? endif; ?>

                    <div class="form-group _col-sm-3" style="opacity: 0;width: 1px;padding: 0;margin: 0;">
                        <button style="width: 1px; padding: 0;" type="submit" class="btn btn-primary products__filter-submit">Фильтровать</button>
                    </div>

                    <div class="form-group _col-sm-3">
                        <a href="/product/" class="btn btn-secondary">Сбросить</a>
                    </div>
                </div>
            </form>


        </div>

    </div>

    <div class="table-responsive__">



        <table class="table table-striped [ PRODUCT-LIST ] products__products-list">

            <? if ($this->params['local_import_stat']): ?>
            <tr>
                <td colspan="4">
                    <div class="[ local-import-stat ] __local-import-stat">
                        <? $local_import_stat = $this->params['local_import_stat']; ?>
                        <div class="local-import-stat__title">Только что импортировано:</div>
                        <div>Всего обработано: <strong><?= $local_import_stat['all'] ?></strong></div>
                        <div>Количество одинаковых asin: <strong><?= $local_import_stat['asin_duplicate'] ?></strong></div>
                        <div>Заменено: <strong><?= $local_import_stat['replaced'] ?></strong></div>
                        <div>Проигнорировано: <strong><?= $local_import_stat['ignored'] ?></strong></div>
                        <div>Добавлено новых: <strong><?= $local_import_stat['added'] ?></strong></div>
                        <? if ($source_id > 1):?>
                        <div>C правыми товарами было: <strong><?= $local_import_stat['p_with_right_p'] ?></strong></div>
                        <? endif; ?>
                    </div>
                </td>
            </tr>
            <? endif;?>

            <? foreach ($list as $k => $item):   ?>

            <?php
                $images_left = preg_split("/[; ]/", $item->baseInfo["Image"]);

                $images_left[0];
                $images_left["Title"];
                $source_id = $item->source->id;
            ?>
            <!-- ITEM -->
            <tr
                class="[ PRODUCT-LIST-ITEM ] product-list__product-list-item"
                data-pid="<?= $item->id ?>"
                data-source_id="<?= $source_id ?>"

                >
                <td class="products-list__td1">
                    <div class="product-list-item__data" style="white-space: nowrap"><span>Asin:</span><br><?= (strlen($item->asin) > 6) ? substr($item->asin, 0, 6) . '..' : $item->asin ?></div>
                    <div class="product-list-item__data"><span>BSR:</span><br><?= number_format($item->baseInfo["Sales Rank: Current"], 0, '', ' '); ?></div>
                    <div class="product-list-item__data"><span>Sales30:</span><br><?= $item->baseInfo["Sales Rank: Drops last 30 days"] ?></div>
                    <div
                        class="product-list-item__data js-addition-info-for-price"
                        data-addition_info_for_price='<?= $item->addition_info_for_price(); ?>'
                        >
                        <span>Price:</span><br>
                        <?= ($item->baseInfo[$default_price_name]) ?: '-' ?>
                    </div>
                    <div class="product-list-item__data"><span>Status:</span><br><?= $item->baseInfo["Brand_R"] ?></div>
                    <? if (0):?>
                    <div class="product-list-item__data"><span>ASIN:</span><br><div class="product-list-item__data-asin" ><?= $item->baseInfo["ASIN"] ?></div></div>
                    <? endif; ?>

                    <? if ($is_admin):?>
                    <div class="product-list-item__data"><span>Profile:</span><br><?= $item->profile ?></div>
                    <? endif;?>

                </td>
                <td class="products-list__td2" style="<?= (count($images_left) > 1) ? "padding-right: 53px;" : "" ?>">

                    <div class="products-list__img-wrapper">

                        <div
                            data-title="<?= Html::encode($item->baseInfo['Brand']) ?>"
                            data-description="<?= Html::encode($item->baseInfo['Title']) ?>"
                            class="products-list__img-wrapper-link"
                            data-link = "<?=
                            Url::to(['product/view',
                                'id' => $item->id,
                                'source_id' => $source_id,
                                'comparisons' => $get_['filter-items__comparisons'],
                                'filter-items__profile' => $get_['filter-items__profile']])
                            ?>"
                            >
                            <div class="products-list__img" style="background-image: url('<?= explode(';', $item->baseInfo['Image'])[0] ?>')">

                                <div class="slider__left-item__fade -top">
                                    <? if ($is_admin):?>
                                    <div class="slider__left-item-img-top-text"><?= $item->baseInfo["Brand"] ?></div>
                                    <? endif;?>
                                </div>


                                <div class="slider__left-item__fade -bottom">
                                    <div class="slider__left-item__text">
                                        <?//= $base['Sales Rank: Current'] .'/'. $base['Sales Rank: Drops last 30 days'] ?>

<?= Html::encode($item->baseInfo['Categories: Root']) ?>


                                        <div
                                            class="[ button-x-2 ] product-list__item-mismatch-all"
                                            data-url= <?= Url::to(['product/missall', 'id' => $item->id, 'source_id' => $source_id]) ?>
                                            ></div>


                                    </div>
                                </div>

                            </div>

                            <? if (0):?>
                            <!-- message -->
                            <div class="products-list__img-message">
                                <div class="products-list__img-message-text">
                                    <strong><?= $item->baseInfo['Categories: Root'] ?></strong> <br> <br>
<?= $item->baseInfo['Title'] ?>
                                </div>
                                <div class="products-list__img-message-arrow"></div>
                            </div>
                            <? endif;?>


                        </div>
                        <? if ((count($images_left) > 1)):?>
                        <div class="slider__left-item-other-imgs">

                            <? foreach ($images_left as $slider__left_img): ?>
                            <div class="slider__left-item-other-img" style="background-image: url('<?= $slider__left_img ?>')"></div>
                            <? endforeach; ?>
                        </div>
                        <? endif;?>

                    </div>


                </td>
                <td class="products-list__td3">

                    <div class="products-list__slider-wrapper <?= (int) $right_item_show === 1 ? '-v-2' : '' ?> js-slider-root">
                        <?php
                        echo TopSlider::widget([
                            'is_detail_view' => $filter_is_detail_view,
                            'number_page_current' => $number_page_current,
                            'product' => $item,
                            'filter_items_comparisons' => $filter_items_comparisons,
                            'filter_items_profile' => $filter_items_profile,
                            'filter_no_compare' => $filter_no_compare,
                        ])
                        ?>
                    </div>

                </td>

                <td class="products-list__td4">

                    <div class="slider_close -in-list"><div class="-line -line-1"></div><div class="-line -line-2"></div></div>

                    <?php                        
                        $statuses = [
                            Comparison::STATUS_PRE_MATCH => 0,
                            Comparison::STATUS_MATCH => 0,
                            Comparison::STATUS_MISMATCH => 0,
                            Comparison::STATUS_OTHER => 0,
                        ];


                        foreach ($item->comparisons as $comparison){
                            $statuses [$comparison->status]++;
                        }
                    ?>


                    <div class="product-list-item__data -first-margin">
                        <?php
                        echo $ret = Html::tag('div',
                           "<span class='js-pre_match pre_match'>{$statuses[Comparison::STATUS_PRE_MATCH]}</span>
                            <span class='js-match match'>{$statuses[Comparison::STATUS_MATCH]}</span>
                            <span class='js-mismatch mismatch'>{$statuses[Comparison::STATUS_MISMATCH]}</span>
                            <span class='js-other other'>{$statuses[Comparison::STATUS_OTHER]}</span>
                            <span class='js-nocompare nocompare'>".count(Comparison::get_no_compare_ids_for_item($item))."</span>",
                            ['class' => 'product-list-item__compare-statistics']
                        );
                        ?>

                        <span class="-title">Обработано:</span>
                        <?
                        $counted = $item->aggregated ? $item->aggregated->counted : 0;
                        $ret = Html::tag('div',"{$counted}/" . count($item->getAddInfo()),
                        ['class' => 'name product-list-item__processed']);
                        //$ret .= '<br/>';
                        echo $ret;
                        ?>
                    </div>



                    <? if ($is_admin):?>
                    <div class="product-list-item__data"><span>Пользователь:</span><br>
<?= empty($item->aggregated->users) ? "не&nbsp;задано" : $item->aggregated->users; ?>
                    </div>
                    <? endif;?>

                    <div class="product-list-item__date " style="margin-bottom: 10px">
                        <div class="product-list-item__date-title">Добавлено:</div>
                        <div><?= date('d.m.Y', strtotime($item->date_add)); ?></div>

                        <? if ($item->updated): ?>
                        <div class="product-list-item__date-title" style="margin-top: 5px">Обновлено:</div>
                        <div><?= date('d.m.Y H:i', strtotime($item->updated->date)); ?></div>
                        <? endif;?>
                    </div>

                    <? if ($is_admin):?>
                    <div class="tbl" style="width: 100%;">
                        <div class="td">

                            <div class="product-list-item__del js-del-item" data-p_id="<?= $item->id ?>" data-source_id="<?= $source_id ?>">
                                Удалить
                            </div>

                        </div>
                        <div class="td">

                            <div
                                class="js-reset-compare product-list-item__reset-compare -margin"
                                data-p_id="<?= $item->id ?>"
                                data-source_id="<?= $source_id ?>"
                                >
                                Отменить
                            </div>

                        </div>

                    </div>

                    <? endif; ?>


                    <div class="actions_for_right-visible-items">
                        <? if (!(int)$right_item_show && $get_['filter-items__comparisons'] === 'NOCOMPARE'):?>


                        <div
                            title="Только видимые"
                            class="[ button-x-2 ] product-list-item__btn-red -change-2"
                            href="/product/missall?id=<?= $item->id ?>&source_id=<?= $source_id ?>&return=1"
                            ></div>

                        <? if (0): ?>
                        <div
                            class="slider__left-item__btn-yellow yellow_button_v1 -change-1"
                            href="/product/missall?id=<?= $item->id ?>&source_id=<?= $source_id ?>&return=1"
                            ></div>
                        <? endif; ?>
                        <? endif;?>
                    </div>

                </td>
            </tr>
            <!-- / ITEM -->
            <? endforeach;?>


        </table>

    </div><!-- table-responsive -->

    <div class="row">
        <div class="col">
            <div class="featured-items">Показаны записи <?= $on_page ?> из <?= $cnt_all; ?>.</div>

            <?
            //  filter-items__source=1
            //  filter-items__profile=Prepod
            //  filter-items__show_n_on_page=10
            //  filter-items__id=
            //  filter-items__target-image=
            //  filter-items__comparing-images=
            //  filter-items__user=
            //  filter-items__comparisons=
            //  filter-items__sort=
            //  filter-items__right-item-show=0
            //  page=1

            $e_comparison = isset($get_['filter-items__comparisons']) && $get_['filter-items__comparisons'] ?  strtolower($get_['filter-items__comparisons']) : 'match';
            $e_profile = isset($get_['filter-items__profile']) && $get_['filter-items__profile'] && $get_['filter-items__profile'] !== 'Все' ? $get_['filter-items__profile'] : '{{all}} ';
            ?>
            <a href="<?= '/exports/step_4?source_id=' . $source_id . '&comparisons=' . $e_comparison . '&profile=' . $e_profile ?>" class="product-list-item__export js-export-step-4" >
                экспортировать
            </a>
            <? if ($is_admin): ?>
            <a
                href="/import/step_1?source_id=<?= $source_id ?>"
                target="_blank"
                data-source_id="<?= $source_id ?>"
                class="product-list-item__import-from-sql js-import-from-sql"
                >
                загрузить SQL
            </a>
            <? endif; ?>


        </div>
        <div class="col" style="text-align: right;max-width: 200px;">
            <div class="product-list-item__del -del-all js-del-all-visible-items">удалить все</div>
            <div class="product-list-item__reset-compare -compare-all js-reset-compare-all-visible-items">отменить все</div>

        </div>
    </div>

    <? if ($get_['filter-items__show_n_on_page'] !== 'ALL'):?>
    <div class="products__pager">

        <nav aria-label="Page navigation example ">
            <ul class="pagination justify-content-center pagination-striped ">


                <? for ($i = $pager['from']; $i <= $pager['to']; $i++):?>
                <? $url_construct['page'] = $i; ?>
                <li class="page-item <?= (int) $i === (int) $page_n ? 'active' : '' ?>"><a class="page-link" href="<?= Url::toRoute($url_construct) ?>"><?= $i ?></a></li>
                <? endfor; ?>

                <?
                $url_construct['filter-items__show_n_on_page'] = 'ALL';
                $url_construct['page'] = 1;
                ?>
                <li class="page-item "><a class="page-link" href="<?= Url::toRoute($url_construct) ?>">Показать все</a></li>

            </ul>
        </nav>
    </div>
    <? endif;?>

</div>

<br>
<br>
<br>
<br>
<br>
<? if (0): ?>
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
                            ]
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

<? endif;?>