<?php
/**
 * @var string $list
 * @var string $local_import_stat
 * @var string $f_comparison_status
 * @var string $f_profile
 * @var string $f_no_compare
 * @var string $f_detail_view
 * @var string $f_profile
 */

use yii\helpers\Html;
use yii\helpers\Url;
use common\models\Comparison;
?>

<table class="table table-striped [ PRODUCT-LIST ] products__products-list">
    <?php if ($local_import_stat): ?>
        <tr>
            <td colspan="4">
                <div class="[ local-import-stat ] __local-import-stat">
                    <div class="local-import-stat__title">Только что импортировано:</div>
                    <div>Всего обработано: <strong><?= $local_import_stat['all'] ?></strong></div>
                    <div>Количество одинаковых asin: <strong><?= $local_import_stat['asin_duplicate'] ?></strong></div>
                    <div>Заменено: <strong><?= $local_import_stat['replaced'] ?></strong></div>
                    <div>Проигнорировано: <strong><?= $local_import_stat['ignored'] ?></strong></div>
                    <div>Добавлено новых: <strong><?= $local_import_stat['added'] ?></strong></div>
                    <?php if ($source_id > 1): ?>
                        <div>C правыми товарами было: <strong><?= $local_import_stat['p_with_right_p'] ?></strong></div>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endif; ?>

    <?php foreach ($list as $k => $item): ?>

        <?php
        $images_left = preg_split("/[; ]/", $item->baseInfo["Image"]);

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
        $sales30 = $item->baseInfo["Sales Rank: Drops last 30 days"];
        $price = ($item->baseInfo[$default_price_name]) ?: '-';
        $fba = $item->baseInfo["Count of retrieved live offers: New, FBA"] . ' / ' . $item->baseInfo["Count of retrieved live offers: New, FBM"];

        $td2_asin = (strlen($item->asin) > 6) ? substr($item->asin, 0, 6) . '..' : $item->asin;
        $td2_toptext = Html::encode($item->baseInfo['Categories: Root']);
        $td3_title = $item->baseInfo['Title'];

        $is_minimize = ($f_detail_view == 2 || $f_detail_view == 3);
        ?>

        <!-- ITEM полное отображение-->
        <tr class="[ PRODUCT-LIST-ITEM ] product-list__product-list-item block_maximize <?= $is_minimize ? 'd-none' : '' ?>"
            data-pid="<?= $item->id ?>"
            data-source_id="<?= $source_id ?>"
        >
            <td class="products-list__td1">
                <div class="product-list-item__data"><span>BSR:</span><br><span id="id_td1_bsr"><?= $bsr ?></span></div>
                <div class="product-list-item__data"><span>Sales30:</span><br><span
                            id="id_td1_sales30"><?= $sales30 ?></span></div>
                <div class="product-list-item__data js-addition-info-for-price"
                     data-addition_info_for_price='<?= $item->addition_info_for_price(); ?>'
                >
                    <span>Price:</span><br>
                    <span id="id_td1_price"><?= $price ?></span>
                </div>

                <div class="product-list-item__data">
                    <span>Brand:</span><br><?= $item->baseInfo["Brand_R"] ?: $item->baseInfo['Manufacturer']; ?></div>
                <div class="product-list-item__data"><span>FBA/FBM:</span><br><span id="id_td1_fba"><?= $fba ?></span>
                </div>
                <div class="product-list-item__data"><span>Profile:</span><br><?= $item->profile ?></div>


            </td>
            <td class="products-list__td2" style="<?= (count($images_left) > 1) ? "padding-right: 53px;" : "" ?>">
                <div id='id_td2_asin'><?= $td2_asin ?></div>
                <div class="products-list__img-wrapper"
                     data-id_source="<?= $source_id ?>"
                     data-id_product="<?= $item->id ?>"
                >
                    <?php
                    $title = '';
                    $brand = $item->baseInfo['Brand'];
                    $manufacturer = $item->baseInfo['Manufacturer'];
                    $categoriesTree = $item->baseInfo["Categories: Tree"];

                    if (empty($manufacturer)) {
                        $title .= 'Brand: ' . Html::encode($brand);
                    } else {
                        $title .= 'Manufacturer: ' . Html::encode($manufacturer);
                    }
                    ?>
                    <div data-tree="<?= $categoriesTree ?>"
                         data-title="<?= $title ?>"
                         data-description="<?= Html::encode($item->baseInfo['Title']) ?>"
                         class="products-list__img-wrapper-link"
                         data-link="<?=
                         Url::to([
                             'product/view',
                             'id' => $item->id,
                             'source_id' => $source_id,
                             'comparisons' => $f_comparison_status,
                             'filter-items__profile' => $f_profile
                         ])
                         ?>"
                    >
                        <div class="products-list__img"
                             style="background-image: url('<?= explode(';', $item->baseInfo['Image'])[0] ?>')">

                            <div class="slider__left-item__fade -top">
                                <div id="id_td2_toptext"
                                     class="slider__left-item-img-top-text"><?= $td2_toptext ?></div>
                            </div>

                            <div class="slider__left-item__fade -bottom">
                                <div class="slider__left-item__text">
                                    <?php //= $base['Sales Rank: Current'] .'/'. $base['Sales Rank: Drops last 30 days'] ?>
                                    <?= Html::encode($item->baseInfo['Brand']) ?>
                                    <div class="[ button-x-2 ] product-list__item-mismatch-all"
                                         data-url= <?= Url::to(['product/missall']) ?>
                                         data-id_product=<?= $item->id ?>
                                         data-id_source=<?= $source_id ?>
                                    ></div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <?php if ((count($images_left) > 1)): ?>
                        <div class="slider__left-item-other-imgs">
                            <?php foreach ($images_left as $slider__left_img): ?>
                                <div class="slider__left-item-other-img"
                                     style="background-image: url('<?= $slider__left_img ?>')"></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </td>

            <td class="products-list__td4">
                <div class="product-list-item__date " style="margin-bottom: 10px">
                    <div class="product-list-item__date-title">Добавлено:</div>
                    <div><?= date('d.m.Y H:i', strtotime($item->date_add)); ?></div>

                    <?php if ($item->updated): ?>
                        <div class="product-list-item__date-title" style="margin-top: 5px">Обновлено:</div>
                        <div><?= date('d.m.Y H:i', strtotime($item->updated->date)); ?></div>
                    <?php endif; ?>
                </div>

                <div class="actions_for_right-visible-items">
                    <?php if (($f_detail_view == 0 || $f_detail_view == 2) && $f_comparison_status === 'NOCOMPARE'): ?>
                        <div
                                title="Только видимые"
                                class="[ button-x-2 ] product-list-item__btn-red -change-2"
                                href="/product/missall?id=<?= $item->id ?>&source_id=<?= $source_id ?>&return=1"
                        ></div>
                    <?php endif; ?>
                </div>

            </td>
        </tr>
        <!-- / ITEM -->

    <?php endforeach; ?>
</table>
