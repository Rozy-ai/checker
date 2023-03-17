<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
<style>
    .swiper-button-next:after, .swiper-button-prev:after {
        font-size: 22px;
    }

</style>
<?php

use backend\components\ProductWidget;
use common\models\Product;
use backend\components\TopSlider;
use yii\data\Pagination;
use yii\helpers\Html;
use backend\models\Settings__fields_extend_price;
use common\models\Comparison;

/* @var $model common\models\Product */
/* @var $compare_item */
/* @var $compare_items */
/* @var $source */

/* @var $active_comparison_status */
/* $var $list_comparison_statuses */

/* @var $f_profile */
/* @var $number_node */
/* @var $is_admin */

\backend\assets\ProductAsset::register($this);


$this->title = "#{$model->id}";
$base = $model->baseInfo;
$add_info = $model->addInfo;
$urls = array_keys($add_info);
$pages = new Pagination(['pageSize' => 1, 'totalCount' => count($add_info), 'pageParam' => 'node', 'pageSizeParam' => 'l']);
$node_idx = $pages->page + 1;
$item = count($urls) && isset($add_info[$urls[$pages->page]]) ? $add_info[$urls[$pages->page]] : [];

//$item = $compare_item;
// TODO!
//$item['eBay_stock'] = AddInfoHelper::valueList($model, $pages->page, 'eBay_stock');
//$item['E_Sales'] = AddInfoHelper::valueList($model, $pages->page, 'E_Sales');
?>
<div class="js__settings -hidden">
    <div
        class="js__product -start-settings"
        data-p_id="<?= $model->id ?>"
        data-source_id="<?= $source->id ?>"
        data-comparison="<?= $active_comparison_status ?>"
        data-profile="<?= $f_profile ?>"
        data-node="<?= $number_node ?? 0 ?>"
        >
    </div>
</div>

<div class="line_for_scroll_handler"></div>
<link href="/css/p-navigation.css" rel="stylesheet" />
<div class="[ wrapper_for_scroll_handler __ block ]">
    <div class="wrapper_for_scroll_handler __plus">

        <div class="product-page [ __p-navigation [ P-NAVIGATION ]">

            <div class="p-navigation __item [ __p-nav-left [ P-NAV-LEFT ]">

                <div class="p-nav-left __item [ __move-direction [ MOVE-DIRECTION ]">

                    <div class="move-direction __p-before js__prev">
                        <div class="move-direction __fade-wrapper js__main_prev_wrapper">
                            <!--<div class="[ p-min-view-item ] __ main_product prev "></div>-->
                        </div>
                    </div>

                    <div class="move-direction __identifier">
                        <select name="" class="move-direction __identifier-select js__move-direction-select form-control">
                            <option value="">...</option>
                        </select>
                    </div>

                    <div class="move-direction __p-after js__next">
                        <div class="move-direction __fade-wrapper js__main_next_wrapper">
                            <!--<div class="[ p-min-view-item ] __ main_product next "></div>-->
                        </div>
                    </div>

                </div>
                <div class="p-nav-left __item [ __filter [ FILTER ]">
                    <div class="filter __comparison">
                        <select name="f_comparison_status" id="id_f_comparison_status_view" class="filter __comparison-select form-control">
                            <option value="">All</option>
                            <?php
                                foreach ( $list_comparison_statuses as $key => $name){
                                    $is_active = ($key == $active_comparison_status)?'active':'';
                                    echo "<option value=$key $is_active>$name</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
            </div><!-- .__left-container -->



            <div class="p-navigation __item | __ [ p-nav-right ] __block">
                <div class="p-nav-right __right-products">
                    <div class="p-nav-right __swiper">
                        <div class="swiper-wrapper">
                            <!--                  <div class="swiper-slide [ p-min-view-item ] __ "></div>-->
                            <!--                  <div class="swiper-slide [ p-min-view-item ] __ "></div>-->
                        </div>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>

                <div class="p-nav-right __toggle-advanced-view-btn">

                    <button class="p-nav-right __tab">
                        <i class="bi bi-arrow-down"></i>
                    </button>

                </div>
            </div>
        </div>
        <div class="position-1 -hidden">
            <div class="slider__layout">

                <?php
                $images_left = preg_split("/[; ]/", $base["Image"]);

                $images_left[0];
                $images_left["Title"];
                ?>

                <div
                    data-pid="<?= $model->id ?>"
                    class="slider__layout-td slider__left-item"
                    style=""
                    >

                    <div class="slider__left-item_info">
                        <div class="slider__left-item__data"><span>id:</span><br><?= $model->id ?></div>
                        <div class="slider__left-item__data"><span>BSR:</span><br><?= number_format($base["Sales Rank: Current"], 0, '', ' '); ?></div>
                        <div class="slider__left-item__data"><span>Sales30:</span><br><?= $base["Sales Rank: Drops last 30 days"] ?></div>
                        <div
                            class="slider__left-item__data js-addition-info-for-price"

                            data-addition_info_for_price='<?php /*= $model->addition_info_for_price(); */?>'

                            ><span>Price:</span><br>

<?php $p_key = Settings__fields_extend_price::get_default_price($source->id)->name ?: 'Price Amazon'; ?>
<?= $base[$p_key] ?: '-' ?>
                        </div>
                        <div class="slider__left-item__data"><span>Status:</span><br><?= $base["Brand_R"] ?></div>
                        <?php if (0):?>
                        <div class="slider__left-item__data"><span>ASIN:</span><br><div class="slider__left-item__data-asin" ><?= $base["ASIN"] ?></div></div>
                        <?php endif; ?>
                        <?php if ($is_admin):?>
                        <div class="slider__left-item__data"><span>Profile:</span><br><?= $model->profile ?></div>
                        <?php endif;?>
                    </div>

                    <?php if ((count($images_left) > 1)):?>
                    <div class="slider__left-item-other-imgs">

                        <?php foreach ($images_left as $slider__left_img): ?>
                        <div class="slider__left-item-other-img" style="background-image: url('<?= $slider__left_img ?>')"></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif;?>


                    <div
                        data-title="<?= Html::encode($base['Brand']) ?>"
                        data-description="<?= Html::encode($base['Title']) ?>"
                        data-link="<?= $base['URL: Amazon'] ?>"
                        class="slider__left-item-img-wrapper-link"
                        >
                        <div class="slider__left-item-img" style="background-image: url('<?= $images_left[0] ?>')">

                            <div class="slider__left-item__fade -top">
                                <?php if (1 && $is_admin):?>
                                <div class="slider__left-item-img-top-text"><?= $base["Brand"] ?></div>
                                <?php endif;?>
                            </div>

                            <div class="slider__left-item__fade -bottom">
                                <div class="slider__left-item__text">
                                    <?//= $base['Sales Rank: Current'] .'/'. $base['Sales Rank: Drops last 30 days'] ?>

<?= Html::encode($base['Categories: Root']) ?>

                                    <?php if (0): ?>
                                    <div
                                        class="slider__left-item__btn-red [ button-x-2 ]"
                                        data-link="/product/missall?id=<?= $item->id ?>&source_id=<?= $source->id ?>&return=1"
                                        ></div>

                                    <div
                                        class="slider__left-item__btn-green green_button_v1 -change-1"
                                        href="/product/missall?id=<?= $item->id ?>&source_id=<?= $source->id ?>&return=1"
                                        ></div>
                                    <?php endif; ?>

                                </div>
                            </div>

                        </div>



                    </div>

                </div>

                <div class="slider__left-slider js-slider-root" style="<?= (count($images_left) > 1) ? 'padding-left: 335px;' : '' ?>">

<?=
TopSlider::widget([
    'product' => $model,
    'number_page_current' => $pages->page,
    'source' => $source
])
?>


                </div>
            </div>
        </div>
    </div>
</div>

<?php
$can = \Yii::$app->user->can('compare-products', ['product' => $model]);
if (!$can) {
    $can = Comparison::can_compare($model->id, $source->id);
}
?>

<?=
ProductWidget::widget([
    'left' => $base,
    'right' => count($add_info) > 0 ? $item : [],
    'right_info' => array_values($add_info),
    //'comparison' => isset($model->comparisons [$pages->page]) ? $model->comparisons [$pages->page] : null,
    'comparison' => isset($model->comparisons [$pages->page]) ? $model->comparisons [$pages->page] : $model->comparisons [$item_id],
    'canCompare' => $can,
    'product_id' => $product_id,
    'item_id' => $item_id,
    'node_idx' => $node_idx,
    'p_item' => $model,
    'arrows' => $arrows,
    'model' => $model,
    'compare_items' => $compare_items,
    'compare_item' => $compare_item,
    'source' => $source,
    'is_admin' => $is_admin
]);
?>
<?php //Pjax::end() ?>

<div class="row">
    <div class="col-6">
        Добавлено: <strong><?= date('d-m-Y H:i', strtotime($model->date_add)) ?></strong>
    </div>
    <div class="col-6">
        Обновлено: <strong><?= date('d-m-Y H:i', strtotime($model->updated->date)) ?></strong>
    </div>
</div>


