<?php
/**
 * @var string $list
 * @var string $local_import_stat
 * @var string $is_admin
 * @var string $f_comparison_status
 * @var string $f_profile
 * @var string $f_no_compare
 * @var atring $f_detail_view
 * @var atring $f_profile
 * 
 */

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\TopSlider;
use common\models\Comparison;
?>

<table class="table table-striped [ PRODUCT-LIST ] products__products-list">
    <? if ($local_import_stat): ?>
    <tr>
        <td colspan="4">
            <div class="[ local-import-stat ] __local-import-stat">
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

    /*$images_left[0];
    $images_left["Title"];*/
    $source_id = $item->source->id;
    
    $list_comparison_statuses = Comparison::getListStatusForStatictic($item->id);
    
    $list_comparison_statuses[Comparison::STATUS_NOCOMPARE] = count($item->addInfo)
        -$list_comparison_statuses[Comparison::STATUS_PRE_MATCH]
        -$list_comparison_statuses[Comparison::STATUS_MATCH]
        -$list_comparison_statuses[Comparison::STATUS_MISMATCH]
        -$list_comparison_statuses[Comparison::STATUS_OTHER];
    
    $counted = $item->aggregated ? $item->aggregated->counted : 0;
    $processed = "{$counted}/" . count($item->getAddInfo());
    
    
    $bsr = number_format($item->baseInfo["Sales Rank: Current"], 0, '', ' ');
    $dropsValue = $item->baseInfo["Sales Rank: Drops last 30 days"];
    $dropsTitle = 'Drops(30)';
    if(isset($item->baseInfo["Sales Rank: Drops last 90 days"])) {
        $dropsValue .= '/'. $item->baseInfo["Sales Rank: Drops last 90 days"];
        $dropsTitle = 'Drops(30/90)';
    }
    $price = ($item->baseInfo[$default_price_name]) ?: '-';
    $fba = $item->baseInfo["Count of retrieved live offers: New, FBA"] . ' / ' . $item->baseInfo["Count of retrieved live offers: New, FBM"];
    
    $td2_asin = (!$is_admin && strlen($item->asin) > 6) ? substr($item->asin, 0, 6) . '..' : $item->asin;
    $td2_toptext = Html::encode($item->baseInfo['Categories: Root']);
    $td2_brand = $item->baseInfo['Brand'] ?: $item->baseInfo['Manufacturer'];
            
    $td3_title = $item->baseInfo['Title'];
    
    
    $is_minimize = ($f_detail_view == 2 || $f_detail_view == 3);
    ?>
    

    <!-- ITEM полное отображение-->
    <tr
        class="[ PRODUCT-LIST-ITEM ] product-list__product-list-item block_maximize <?=$is_minimize?'-hidden':''?>"
        data-pid="<?= $item->id ?>"
        data-source_id="<?= $source_id ?>"
    >
        <?php
        ?>
        <td class="products-list__td1">
            <div class="product-list-item__data"><span>BSR:</span><br><span id="id_td1_bsr"><?=$bsr?></span></div>
            <div class="product-list-item__data"><span><?= $dropsTitle ?>:</span><br><span id="id_td1_sales30"><?= $dropsValue ?></span></div>
            <div
                class="product-list-item__data js-addition-info-for-price"
                data-addition_info_for_price='<?= $item->addition_info_for_price(); ?>'
                >
                <span>Price:</span><br>
                <span id="id_td1_price"><?=$price?></span>
            </div>

            <div class="product-list-item__data"><span>Brand_R:</span><br><?= $item->baseInfo["Brand_R"]?:"-"; ?></div>
            <div class="product-list-item__data"><span>FBA/FBM:</span><br><span id="id_td1_fba"><?=$fba?></span></div>
            <? if ($is_admin):?> 
            <div class="product-list-item__data"><span>Profile:</span><br><?= $item->profile ?></div>
            <? endif;?>

        </td>
        <td class="products-list__td2" style="<?= (count($images_left) > 1) ? "padding-right: 53px;" : "" ?>">
            <div id='id_td2_asin'><?= $td2_asin ?></div>
            <div class="products-list__img-wrapper"
                data-id_source="<?=$source_id?>"
                data-id_product="<?=$item->id?>"
            >
                <?php
                $brand = $item->baseInfo['Brand'];
                $manufacturer = $item->baseInfo['Manufacturer'];
                $categoriesTree = $item->baseInfo["Categories: Tree"];
                if (!empty($brand)) {
                    $title = 'Brand: ' . Html::encode($td2_brand);
                } else {
                    $title = $manufacturer?'Manufacturer: ' . Html::encode($manufacturer):'';
                }
                ?>
                <div
                    data-tree="<?= $categoriesTree ?>"
                    data-title="<?= $title ?>"
                    data-description="<?= Html::encode($item->baseInfo['Title']) ?>"
                    class="products-list__img-wrapper-link"
                    data-link = "<?=
                    Url::to([
                        'product/view',
                        'id' => $item->id,
                        'source_id' => $source_id,
                        'comparisons' => $f_comparison_status,
                        'filter-items__profile' => $f_profile
                    ])
                    ?>"
                    >
                    <div class="products-list__img" style="background-image: url('<?= explode(';', $item->baseInfo['Image'])[0] ?>')">

                        <div class="slider__left-item__fade -top">
                            <? if ($is_admin):?>
                            <div id="id_td2_toptext" class="slider__left-item-img-top-text"><?=$td2_toptext?></div>
                            <? endif;?>
                        </div>


                        <div class="slider__left-item__fade -bottom">
                            <div class="slider__left-item__text">
                                <?= Html::encode($item->baseInfo['Brand']) ?>

                                <div
                                    class="[ button-x-2 ] product-list__item-mismatch-all"
                                    data-url= <?= Url::to(['product/missall']) ?>
                                    data-id_product=<?= $item->id ?>
                                    data-id_source=<?= $source_id ?>
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
            <div class="products-list__slider-wrapper <?=($f_detail_view == 1 || $f_detail_view == 3)?'-v-2':''?> js-slider-root"
                <?php
                echo TopSlider::widget([
                    'detail_view' => $f_detail_view,
                    'number_page_current' => $number_page_current ?? 1,
                    'product' => $item,
                    'f_comparison_status' => $f_comparison_status,
                    'f_profile' => $f_profile,
                    'f_no_compare' => false,
                    'source' => $source
                ])
                ?>
            </div>

        </td>

        <td class="products-list__td4">

            <div class="slider_close -in-list"><div class="-line -line-1"></div><div class="-line -line-2"></div></div>

            <!--Дублирование значений сделано специально. data атрибуты используются для восстановления данных после визуальных манипуляций со значениями-->
            <div 
                class="product-list-item__data -first-margin block_statistic"
                data-<?=Comparison::STATUS_PRE_MATCH?>="<?=$list_comparison_statuses[Comparison::STATUS_PRE_MATCH]?>"
                data-<?=Comparison::STATUS_MATCH?>="<?=$list_comparison_statuses[Comparison::STATUS_MATCH]?>"
                data-<?=Comparison::STATUS_MISMATCH?>="<?=$list_comparison_statuses[Comparison::STATUS_MISMATCH]?>"
                data-<?=Comparison::STATUS_OTHER?>="<?=$list_comparison_statuses[Comparison::STATUS_OTHER]?>"
                data-<?=Comparison::STATUS_NOCOMPARE?>="<?=$list_comparison_statuses[Comparison::STATUS_NOCOMPARE]?>"
                data-processed="<?=$processed?>"
            >
                <?php
                echo $ret = Html::tag('div',
                        "<span class='js-pre_match pre_match'>{$list_comparison_statuses[Comparison::STATUS_PRE_MATCH]}</span>
                        <span class='js-match match'>{$list_comparison_statuses[Comparison::STATUS_MATCH]}</span>
                        <span class='js-mismatch mismatch'>{$list_comparison_statuses[Comparison::STATUS_MISMATCH]}</span>
                        <span class='js-other other'>{$list_comparison_statuses[Comparison::STATUS_OTHER]}</span>
                        <span class='js-nocompare nocompare'>" . $list_comparison_statuses[Comparison::STATUS_NOCOMPARE] . "</span>",
                        ['class' => 'product-list-item__compare-statistics']
                );
                ?>

                <span class="-title">Обработано:</span> <?=Html::tag('div', $processed, ['class' => 'name product-list-item__processed']);?>
            </div>

            <? if ($is_admin):?>
            <div class="product-list-item__data"><span>Пользователь:</span><br>
                <?=empty($item->aggregated->users) ? "не&nbsp;задано" : $item->aggregated->users;?>
            </div>
            <div class="product-list-item__data"><span>Profile:</span><br>
                <?= $item->profile ?>
            </div>  
            <? endif;?>

            <div class="product-list-item__date " style="margin-bottom: 10px">
                <div class="product-list-item__date-title">Добавлено:</div>
                <div><?= date('d.m.Y H:i', strtotime($item->date_add)); ?></div>

                <? if ($item->updated): ?>
                <div class="product-list-item__date-title" style="margin-top: 5px">Обновлено:</div>
                <div><?= date('d.m.Y H:i', strtotime($item->updated->date)); ?></div>
                <? endif;?>
            </div>

            <? if ($is_admin):?>
            <div class="tbl" style="width: 100%;">
                <div class="td">

                    <div class="product-list-item__del js-del-item"  
                        data-url = "/product/delete-product"
                        data-id_product="<?= $item->id ?>"
                        data-id_source="<?= $source_id ?>"
                    >
                        Удалить
                    </div>

                </div>
                <div class="td">
                    <div
                        class="js-reset-compare product-list-item__reset-compare -margin"
                        data-url = "/product/reset-compare"
                        data-id_product="<?= $item->id ?>"
                        data-id_source="<?= $source_id ?>"
                    >
                        Отменить
                    </div>

                </div>

            </div>

            <? endif; ?>


            <div class="actions_for_right-visible-items">
                <?php if (($f_detail_view == 0 || $f_detail_view == 2) && $f_comparison_status === 'NOCOMPARE'):?>
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
                <?php endif;?>
            </div>

        </td>
    </tr>
    <!-- / ITEM -->
    
    <!-- ITEM свернутое отображение-->
    
    <tr
        class="[ PRODUCT-LIST-ITEM ] product-list__product-list-item block_minimize <?=$is_minimize?'':'-hidden'?>"
        data-pid="<?= $item->id ?>"
    >
        <td colspan="2" class="products-list__td1_minimize text-nowrap">
            <div style="text-align: center">
                <? if ((count($images_left) > 0)):?>
                <div class="block_minimize_data_img d-inline-block">
                    <div class="slider__left-item-other-img_minimize" style="background-image: url('<?= $images_left[0] ?>')"></div>
                </div>
                <? endif;?>
                <div class="block_minimize_data d-inline-block"><span>BSR</span><br><?=$bsr?></div>
                <div class="block_minimize_data d-inline-block"><span><?= $dropsTitle ?></span><br><?= $dropsValue ?></div>
                <div class="block_minimize_data d-inline-block"><span>Price</span><br><?=$price?></div>
                <div class="block_minimize_data d-inline-block"><span>FBA/FBM</span><br><?=$fba?></div>

            </div>
        </td>
        <td class="products-list__td3_minimize">
            <div class="block_minimize_wrapper">
                <p class="minimize_row"><span class=minimize_row_asin><?=$td2_asin?></span>  <span><?=$td2_toptext?></span> <span><?=$brand?"/ $brand":($manufacturer?"/ $manufacturer":'') ?></span></p>
                <p class="minimize_wrapper_title"><?=$td3_title?></p>               
            </div>
        </td>
        <td class="products-list__td4 text-nowrap">
            <div class="product-list-item__data -first-margin block_statistic">
                <div class="product-list-item__compare-statistics">
                    <span class="js-pre_match pre_match"><?=$list_comparison_statuses[Comparison::STATUS_PRE_MATCH]?></span>
                    <span class="js-match match"><?=$list_comparison_statuses[Comparison::STATUS_MATCH]?></span>
                    <span class="js-mismatch mismatch"><?=$list_comparison_statuses[Comparison::STATUS_MISMATCH]?></span>
                    <span class="js-other other"><?=$list_comparison_statuses[Comparison::STATUS_OTHER]?></span>
                    <span class="js-nocompare nocompare"><?=$list_comparison_statuses[Comparison::STATUS_NOCOMPARE]?></span>
                </div>
            </div>
        </td>        
    </tr>
    <!-- /ITEM свернутое отображение-->
    
    <? endforeach;?>
</table>
