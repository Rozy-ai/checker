<?php

/**
 * @var string $list
 * @var string $local_import_stat
 * @var string $is_admin
 * @var string $f_comparison_status
 * @var string $f_profile
 * @var string $f_no_compare
 * @var string $f_detail_view
 * @var string $f_hide_mode
 * @var string $f_profile
 * @var array $profiles
 *
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\TopSlider;
use common\models\Comparison;
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script type="text/javascript" src="/js/product-index.js"></script>
<table class="table table-striped [ PRODUCT-LIST ] products__products-list products-list">
    <?php if ($local_import_stat) : ?>
        <tr>
            <td colspan="4">
                <div class="[ local-import-stat ] __local-import-stat">
                    <div class="local-import-stat__title">Только что импортировано:</div>
                    <div>Всего обработано: <strong><?= $local_import_stat['all'] ?></strong></div>
                    <div>Количество одинаковых asin: <strong><?= $local_import_stat['asin_duplicate'] ?></strong></div>
                    <div>Заменено: <strong><?= $local_import_stat['replaced'] ?></strong></div>
                    <div>Проигнорировано: <strong><?= $local_import_stat['ignored'] ?></strong></div>
                    <div>Добавлено новых: <strong><?= $local_import_stat['added'] ?></strong></div>
                    <?php if ($source_id > 1) : ?>
                        <div>C правыми товарами было: <strong><?= $local_import_stat['p_with_right_p'] ?></strong></div>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endif; ?>

    <?php foreach ($list as $k => $item) :   ?>
        <?php
        if (empty($item->addInfo)) {
            continue;
        }
        $images_left = preg_split("/[; ]/", ArrayHelper::getValue($item->baseInfo, "Image"));

        $source_id = $item->source->id;

        $list_comparison_statuses = Comparison::getListStatusForStatictic($item->id);

        $list_comparison_statuses[Comparison::STATUS_NOCOMPARE] = count($item->addInfo)
            - $list_comparison_statuses[Comparison::STATUS_PRE_MATCH]
            - $list_comparison_statuses[Comparison::STATUS_MATCH]
            - $list_comparison_statuses[Comparison::STATUS_MISMATCH]
            - $list_comparison_statuses[Comparison::STATUS_OTHER];

        $counted = $item->aggregated ? $item->aggregated->counted : 0;
        $processed = "{$counted}/" . count($item->getAddInfo());


        $bsr = number_format($item->baseInfo["Sales Rank: Current"], 0, '', ' ');
        $dropsValue = $item->baseInfo["Sales Rank: Drops last 30 days"];
        $dropsTitle = 'Drops(30)';
        if (isset($item->baseInfo["Sales Rank: Drops last 90 days"])) {
            $dropsValue .= '/' . $item->baseInfo["Sales Rank: Drops last 90 days"];
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
        <tr class="[ PRODUCT-LIST-ITEM ] product-list__product-list-item block_maximize <?= $is_minimize ? '-hidden' : '' ?>" data-pid="<?= $item->id ?>" data-source_id="<?= $source_id ?>">
            <?php
            ?>
            <td class="products-list__td1">
                <?php $bsrInfo = $item->addition_info_for_price('bsr'); ?>
                <div class="product-list-item__data js-addition-info-for-price" data-addition_info_for_price='<?= $bsrInfo; ?>'>
                    <span>BSR:</span>
                    <?php if (!empty($bsrInfo)) { ?><span class="bi bi-patch-question-fill"></span><?php } ?>
                    <br>
                    <span id="id_td1_bsr"><?= $bsr ?></span>
                </div>
                <div class="product-list-item__data"><span><?= $dropsTitle ?>:</span><br><span id="id_td1_sales30"><?= $dropsValue ?></span></div>
                <?php $priceInfo = $item->addition_info_for_price(); ?>
                <div class="product-list-item__data js-addition-info-for-price" data-addition_info_for_price='<?= $priceInfo; ?>'>
                    <span>Price:</span>
                    <?php if (!empty($priceInfo)) { ?><span class="bi bi-patch-question-fill"></span><?php } ?>
                    <br>
                    <span id="id_td1_price"><?= $price ?></span>
                </div>

                <div class="product-list-item__data"><span>Brand_R:</span><br><?= $item->baseInfo["Brand_R"] ?: "-"; ?></div>
                <div class="product-list-item__data"><span>FBA/FBM:</span><br><span id="id_td1_fba"><?= $fba ?></span></div>

            </td>
            <td class="products-list__td2" style="<?= (count($images_left) > 1) ? "padding-right: 53px;" : "" ?>">
                <div id='id_td2_asin' class="products-list__asin">
                    <div class="row">
                        <div class="col-3">
                            <?php if ($item->date_update === $last_update->created) { ?>
                                New
                            <?php } ?>
                        </div>
                        <div class="col-sm-6">
                            <span><?= $td2_asin ?></span>
                        </div>
                        <div class="col-sm-3">
                            <?= \supplyhog\ClipboardJs\ClipboardJsWidget::widget([
                                'text' => $td2_asin,
                                'label' => '<img src="' . Url::to('@web/img/copy.png') . '" data-src="' . Url::to('@web/img/copy.png') . '" alt="Copy " title="Copy " width="15" height="auto" class="lzy lazyload--done">',
                                'htmlOptions' => [
                                    'class' => 'js-tooltip js-copy',
                                    'data-toggle' => "tooltip",
                                    'data-placement' => "bottom",
                                    'title' => 'Copy to clipboard'
                                ],
                                'tag' => 'button',
                            ]) ?>
                        </div>
                    </div>


                </div>
                <div class="products-list__img-wrapper" data-id_source="<?= $source_id ?>" data-id_product="<?= $item->id ?>">
                    <span class="products-list__favor bi bi-star<?= isset($favorites[$item->id]) ? '-fill favored' : ''; ?>"></span>
                    <?php
                    $brand = $item->baseInfo['Brand'];
                    $manufacturer = $item->baseInfo['Manufacturer'];
                    $categoriesTree = $item->baseInfo["Categories: Tree"];
                    if (!empty($brand)) {
                        $title = 'Brand: ' . Html::encode($td2_brand);
                    } else {
                        $title = $manufacturer ? 'Manufacturer: ' . Html::encode($manufacturer) : '';
                    }
                    ?>
                    <div data-tree="<?= $categoriesTree ?>" data-title="<?= $title ?>" data-description="<?= Html::encode($item->baseInfo['Title']) ?>" class="products-list__img-wrapper-link" data-link="<?=
                                                                                                                                                                                                            Url::to([
                                                                                                                                                                                                                'product/view',
                                                                                                                                                                                                                'id' => $item->id,
                                                                                                                                                                                                                'source_id' => $source_id,
                                                                                                                                                                                                                'comparisons' => $f_comparison_status,
                                                                                                                                                                                                                'filter-items__profile' => $f_profile
                                                                                                                                                                                                            ])
                                                                                                                                                                                                            ?>">
                        <div class="products-list__img" style="background-image: url('<?= explode(';', $item->baseInfo['Image'])[0] ?>')">

                            <div class="slider__left-item__fade -top">
                                <?php if ($is_admin) : ?>
                                    <div id="id_td2_toptext" class="slider__left-item-img-top-text"><?= $td2_toptext ?></div>
                                <?php endif; ?>
                            </div>


                            <div class="slider__left-item__fade -bottom">
                                <div class="slider__left-item__text">
                                    <?= Html::encode($item->baseInfo['Brand']) ?>

                                    <div class="[ button-x-2 ] product-list__item-mismatch-all" data-url=<?= Url::to(['product/missall']) ?> data-id_product=<?= $item->id ?> data-id_source=<?= $source_id ?>></div>


                                </div>
                            </div>

                        </div>

                        <?php if (0) : ?>
                            <!-- message -->
                            <div class="products-list__img-message">
                                <div class="products-list__img-message-text">
                                    <strong><?= $item->baseInfo['Categories: Root'] ?></strong> <br> <br>
                                    <?= $item->baseInfo['Title'] ?>
                                </div>
                                <div class="products-list__img-message-arrow"></div>
                            </div>
                        <?php endif; ?>


                    </div>
                    <?php if ((count($images_left) > 1)) : ?>
                        <div class="slider__left-item-other-imgs">

                            <?php foreach ($images_left as $slider__left_img) : ?>
                                <div class="slider__left-item-other-img" style="background-image: url('<?= $slider__left_img ?>')"></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>

            </td>
            <td class="products-list__td3">
                <div class="products-list__slider-wrapper <?= ($f_detail_view == 1 || $f_detail_view == 3) ? '-v-2' : '' ?> js-slider-root">
                    <?php
                    echo TopSlider::widget([
                        'f_detail_view' => $f_detail_view,
                        'number_page_current' => $number_page_current ?? 1,
                        'product' => $item,
                        'f_comparison_status' => $f_comparison_status ?? false,
                        'f_profile' => $f_profile ?? false,
                        'f_no_compare' => $f_no_compare,
                        'f_hide_mode' => $f_hide_mode,
                        'source' => $source
                    ]);
                    ?>
                </div>

            </td>

            <td class="products-list__td4">

                <div class="slider_close -in-list">
                    <div class="-line -line-1"></div>
                    <div class="-line -line-2"></div>
                </div>

                <!--Дублирование значений сделано специально. data атрибуты используются для восстановления данных после визуальных манипуляций со значениями-->
                <div class="product-list-item__data -first-margin block_statistic" data-<?= Comparison::STATUS_PRE_MATCH ?>="<?= $list_comparison_statuses[Comparison::STATUS_PRE_MATCH] ?>" data-<?= Comparison::STATUS_MATCH ?>="<?= $list_comparison_statuses[Comparison::STATUS_MATCH] ?>" data-<?= Comparison::STATUS_MISMATCH ?>="<?= $list_comparison_statuses[Comparison::STATUS_MISMATCH] ?>" data-<?= Comparison::STATUS_OTHER ?>="<?= $list_comparison_statuses[Comparison::STATUS_OTHER] ?>" data-<?= Comparison::STATUS_NOCOMPARE ?>="<?= $list_comparison_statuses[Comparison::STATUS_NOCOMPARE] ?>" data-processed="<?= $processed ?>">
                    <?php
                    echo $ret = Html::tag(
                        'div',
                        "<span class='js-product-stat js-nocompare nocompare'>{$list_comparison_statuses[Comparison::STATUS_NOCOMPARE]}</span>" .
                            "<span class='js-product-stat js-pre_match pre_match'>{$list_comparison_statuses[Comparison::STATUS_PRE_MATCH]}</span>" .
                            "<span class='js-product-stat js-match match'>{$list_comparison_statuses[Comparison::STATUS_MATCH]}</span>" .
                            "<span class='js-product-stat js-other other'>{$list_comparison_statuses[Comparison::STATUS_OTHER]}</span>" .
                            "<span class='js-product-stat js-mismatch mismatch'>{$list_comparison_statuses[Comparison::STATUS_MISMATCH]}</span>",
                        ['class' => 'product-list-item__compare-statistics d-flex justify-content-between']
                    );
                    ?>

                    <span class="-title">Обработано:</span> <?= Html::tag('div', $processed, ['class' => 'name product-list-item__processed']); ?>
                </div>

                <?php if ($is_admin) : ?>
                    <div class="product-list-item__data"><span>Пользователь:</span><br>
                        <?= empty($item->aggregated->users) ? "не&nbsp;задано" : $item->aggregated->users; ?>
                    </div>
                    <div class="product-list-item__data">
                        <span>Profile:</span>
                        <div class="d-flex align-items-center">
                            <?php if ($is_admin) { ?>
                                <div class="product-list-item__profile pt-2 pb-2 pr-2" data-source-id="<?= $source_id ?>" data-pid="<?= $item->id ?>" data-value="<?= $item->profile ?>">
                                    <?= $item->profile ?>
                                </div>
                                <span class="btn bi bi-pencil product-list-item__edit-profile"></span>
                            <?php } ?>
                        </div>
                        <div class="mb-3">
                            <?= Html::dropDownList(
                                'product-profile',
                                array_search($item->profile, $profiles),
                                $profiles,
                                [
                                    'data-source-id' => $source_id,
                                    'data-pid' => $item->id,
                                    'class' => 'form-control product-list-item__profile-list',
                                ],
                            ) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="product-list-item__date " style="margin-bottom: 10px">
                    <div class="product-list-item__date-title">Добавлено:</div>
                    <div><?= date('d.m.Y H:i', strtotime($item->date_add)); ?></div>

                    <?php if ($item->date_update) : ?>
                        <div class="product-list-item__date-title" style="margin-top: 5px">Обновлено:</div>
                        <div><?= date('d.m.Y H:i', strtotime($item->date_update)); ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($is_admin) : ?>
                    <div class="tbl" style="width: 100%;">
                        <div class="td">

                            <button class="product-list-item__del js-del-item" data-url="/product/delete-product" data-id_product="<?= $item->id ?>" data-id_source="<?= $source_id ?>">
                                Удалить
                            </button>

                        </div>
                        <div class="td">
                            <button class="js-reset-compare product-list-item__reset-compare -margin" data-url="/product/reset-compare" data-id_product="<?= $item->id ?>" data-id_source="<?= $source_id ?>">
                                Отменить
                            </button>

                        </div>

                    </div>

                <?php endif; ?>


                <div class="actions_for_right-visible-items">
                    <?php if (($f_detail_view == 0 || $f_detail_view == 2) && $f_comparison_status === 'NOCOMPARE') : ?>
                        <div title="Только видимые" class="[ button-x-2 ] js-reset-compare-all-visible-items product-list-item__btn-red -change-2" data-url="/product/missall" data-id_product="<?= $item->id ?>" data-id_source="<?= $source_id ?>"></div>

                        <?php if (0) : ?>
                            <div class="slider__left-item__btn-yellow yellow_button_v1 -change-1" data-url="/product/missall" data-id_product="<?= $item->id ?>" data-id_source="<?= $source_id ?>"></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </td>
        </tr>
        <!-- / ITEM -->

        <!-- ITEM свернутое отображение-->

        <tr class="[ PRODUCT-LIST-ITEM ] product-list__product-list-item block_minimize <?= $is_minimize ? '' : '-hidden' ?>" data-pid="<?= $item->id ?>">
            <td colspan="2" class="products-list__td1_minimize text-nowrap">
                <div style="text-align: center">
                    <?php if ((count($images_left) > 0)) : ?>
                        <div class="block_minimize_data_img d-inline-block">
                            <div class="slider__left-item-other-img_minimize" style="background-image: url('<?= $images_left[0] ?>')"></div>
                        </div>
                    <?php endif; ?>
                    <div class="block_minimize_data d-inline-block"><span>BSR</span><br><?= $bsr ?></div>
                    <div class="block_minimize_data d-inline-block"><span><?= $dropsTitle ?></span><br><?= $dropsValue ?></div>
                    <div class="block_minimize_data d-inline-block"><span>Price</span><br><?= $price ?></div>
                    <div class="block_minimize_data d-inline-block"><span>FBA/FBM</span><br><?= $fba ?></div>

                </div>
            </td>
            <td class="products-list__td3_minimize">
                <div class="block_minimize_wrapper">
                    <div class="d-inline-flex" style="width: 100%;">
                        <div class="p-1" style="width: 15%;">
                            <div class="block_minimize_data d-inline-block" style="width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <span><?= $td2_asin ?></span>
                                <br>
                                <?= $td2_toptext ?>
                            </div>
                        </div>
                        <div class="p-1" style="width: 15%;">
                            <div class="block_minimize_data d-inline-block" style="width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <span>Brand</span>
                                <br>
                                <?= ($brand ? "$brand" : '') ?>
                            </div>
                        </div>
                        <div class="p-1" style="width: 70%;">
                            <div class="block_minimize_data d-inline-block" style="display: -webkit-box!important; width: 100%; font-weight: bold; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= $td3_title ?></div>
                        </div>
                    </div>

                    <!--                <p class="minimize_row"><span class=minimize_row_asin>--><?php //=$td2_asin
                                                                                                    ?><!--</span>  <span>--><?php //=$td2_toptext
                                                                                                                            ?><!--</span> <span>--><?php //=$brand?"/ $brand":($manufacturer?"/ $manufacturer":'') 
                                                                                                                                                    ?><!--</span></p>-->
                    <!--                <p class="minimize_wrapper_title">--><?php //=$td3_title
                                                                                ?><!--</p>-->
                </div>
            </td>
            <td class="products-list__td4 text-nowrap">
                <div class="product-list-item__data -first-margin block_statistic">
                    <div class="product-list-item__compare-statistics">
                        <span class="js-product-stat js-pre_match pre_match"><?= $list_comparison_statuses[Comparison::STATUS_PRE_MATCH] ?></span>
                        <span class="js-product-stat js-match match"><?= $list_comparison_statuses[Comparison::STATUS_MATCH] ?></span>
                        <span class="js-product-stat js-mismatch mismatch"><?= $list_comparison_statuses[Comparison::STATUS_MISMATCH] ?></span>
                        <span class="js-product-stat js-other other"><?= $list_comparison_statuses[Comparison::STATUS_OTHER] ?></span>
                        <span class="js-product-stat js-nocompare nocompare"><?= $list_comparison_statuses[Comparison::STATUS_NOCOMPARE] ?></span>
                    </div>
                </div>
            </td>
        </tr>
        <!-- /ITEM свернутое отображение-->

    <?php endforeach; ?>
</table>