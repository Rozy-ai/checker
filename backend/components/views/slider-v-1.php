<?php

/**
 * Отображение списка кратко
 * 
 * @var string $f_comparison_status
 * @var string $f_profile
 * @var bool   $f_hide_mode
 * @var bool   $is_admin
 * @var int    $number_page_current
 * @var $option_class_slider
 * @var $option_sales_key
 * @var $number_node Позиция активного товара начиная от 0
 * @var Product $product
 * @var $items
 */


use common\models\Comparison;
use yii\helpers\Html;
use common\models\User;
use yii\helpers\Url;

$source         = $product->source;
$comparisons    = $product->comparisons;

$canCompare = \Yii::$app->user->can('compare-products', ['product' => $product]);

$variables_left = $this->context->getVariablesLeft($product);
$source_id = $source->id;
$identity = \Yii::$app->user->identity;

// Переменная введена для производительности, чтобы в actionCompare() потом 
// не искать количество правых товаров и количество товаров имеющих сравнение
$is_last = ((count($items)-count($comparisons)) <= 1);
?>

<!-- Если администратор, то показываем в виде ссылки -->

<div id="id_td3_title" class="main-item-title">
    <?php if ($identity && method_exists($identity, 'is_detail_view_for_items') && ($identity->is_detail_view_for_items() || $is_admin)): ?><a target="_blank" href="<?=$product->baseInfo['URL: Amazon']?>"><?php endif; ?>
        <?= $variables_left['description_left'] ?>
    <?php if ($identity && method_exists($identity, 'is_detail_view_for_items') && ($identity->is_detail_view_for_items() || $is_admin)): ?></a><?php endif; ?>
</div>
 
<!-- VIEW 1 -->
<div class='slider__view-1 <?= $option_class_slider ?> [ SLIDER ] product-view__slider '>
    <?php 
        foreach ($items as $index => $item): 
    ?>

    <?php
        $comparison = $comparisons[$item->id];

        switch ($f_comparison_status){
            case 'NOCOMPARE':
                if ($comparison) {
                    continue 2;
                } break;
            case 'PRE_MATCH':
                if (!$comparison || $comparison->status != 'PRE_MATCH') {
                    continue 2;
                } break;
            case 'MATCH':
                if (!$comparison || $comparison->status != 'MATCH') {
                    continue 2;
                } break;
            case 'OTHER':
                if (!$comparison || $comparison->status != 'OTHER') {
                    continue 2;
                } break;                    
            case 'YES_NO_OTHER':
                if (!$comparison || !in_array($comparison->status,['PRE_MATCH', 'MATCH', 'OTHER'])) {
                    continue 2;
                } break;
            case 'MISMATCH':
                if (!$comparison || $comparison->status != 'MISMATCH') {
                    continue 2;
                } break;
        }
        
        //Иниацияализация переменных
        $variables_right = $this->context->getVariablesRight($source, $item, true);
        $current = ($number_page_current === $index) ? '&load_next=1' : '&load_next=0';
        
        $is_hide = $f_hide_mode && (
            $comparison->status === Comparison::STATUS_PRE_MATCH ||
            $comparison->status === Comparison::STATUS_MATCH ||
            $comparison->status === Comparison::STATUS_MISMATCH ||
            $comparison->status === Comparison::STATUS_OTHER
        );
    ?>

    <div
        class="[ SLIDER-ITEM ] slider__slider-item item"
        <?=$is_hide? "style=\"display: none\"":''?>
        data-id_source="<?=$source_id?>"
        data-id_product="<?=$product->id?>"
        data-id_item="<?=$item->id?>"
        data-status="<?=$comparison->status?>"
        data-node_id="<?= $index + 1 ?>" 
    >
        <!--slider_images несодержит стилей. Добавлен для отображения TopSlider-->
        <div
            class="slider-item__border slider_images"
  
            data-description_left   = "<?= htmlspecialchars($variables_left['description_left'])?>"
            data-description_right  = "<?= htmlspecialchars($variables_right['description_right'])?>"
            data-img_left           = "<?= htmlspecialchars($variables_left['img_left'])?>"
            data-img_right          = "<?= htmlspecialchars($variables_right['img_right'])?>"
            data-footer_left        = "<?= htmlspecialchars($variables_left ['footer_left'])?>"
            data-footer_right       = "<?= htmlspecialchars($variables_right['footer_right'])?>"
            data-count_images_right = "<?= htmlspecialchars($variables_right['count_images_right'])?>"
            data-comparison_status  =  <?=$comparison->status?>
        >
            <div class="[ color-marker ] horizontal <?= isset($comparison) ? ($comparison->status === 'MATCH' ? ' match' : ($comparison->status === 'MISMATCH' ? ' mismatch' : ($comparison->status === 'PRE_MATCH' ? ' pre_match' : ' other'))) : ' nocompare' ?>"></div>

            <?=
            Html::a(
                    "<div class=\"slider-item__img\" data-img='" . $variables_right['img_right'] . "' style=\"background-image: url('" . $variables_right['img_right'] . "')\"></div>",
                    ['view', 'id' => $product->id, 'number_node' => $index + 1, 'source_id' => $source_id, 'comparisons' => $f_comparison_status, 'filter-items__profile' => $f_profile],
                    ['class' => 'linkImg slider-item__link-img']
            )
            ?>


            <!-- FORK -->
            <?php if ($source->name === 'EBAY'): ?>
            <div class="slider-item__cnt-1">
                <span class="cnt-1__stock-title __blue-title">Stock:</span>
                <span class="grade cnt-1__stock-n"><?= $item->gradeKey; ?></span>
            </div>

            <?php if (!empty($option_sales_key)): ?>
            <span class="slider__sales sales">
                <span class="__blue-title">Sold:</span><?= preg_replace('|\D|', '', $item->salesKey) ?: 0 ?>
            </span>
            <?php endif; ?>

            <?php elseif ($source->name === 'CHINA'): ?>
            <div class="slider-item__cnt-1">
                <span class="cnt-1__stock-title __blue-title">ROI:</span>
                <span class="grade cnt-1__stock-n"><?= $item->ROI_Ali; ?></span>
            </div>
            <div class="slider__sales sales" style="margin: 0">
                <!--<span class="__blue-title">R:</span>--><?= $item->rating; ?>
            </div>
            <?php endif; ?>
            <span class="slider__sales sales">
                <span class="cnt-1__stock-title __blue-title">Price:</span><span class="grade cnt-1__stock-n"><?= $item->price ?></span>
            </span>
            <div class="slider__sales sales" style="margin: 0">
                <span class="__blue-title">R:</span><?= explode(' ', $item->rating)[0]; ?>
            </div>

            <!-- / FORK -->
            <div
                    class="slider__yellow_button _slider__green_button -v-2 -v-3 -min <?= $comparisons[$item->id]->status === 'PRE_MATCH' ? '-hover' : '' ?>"
                    data-url = "/product/compare"
                    data-id_source = "<?=$source_id?>"
                    data-id_product = "<?=$product->id?>"
                    data-id_item = "<?=$item->id?>"
                    data-status = "<?= Comparison::STATUS_PRE_MATCH ?>"
                    data-is_last = "<?=$is_last?>"
            ></div>

            <div
                    class="slider__red_button -min <?= $comparisons[$item->id]->status === 'MISMATCH' ? '-hover' : '' ?>"
                    data-url = "/product/compare"
                    data-id_source = "<?=$source_id?>"
                    data-id_product = "<?=$product->id?>"
                    data-id_item = "<?=$item->id?>"
                    data-status = "<?= Comparison::STATUS_MISMATCH ?>"
                    data-is_last = "<?=$is_last?>"
            ></div>

        </div>

        <?php if ((count($variables_right['images_right']) > 1)): ?>
            <div class="slider__right-item-other-markers">
                <?php foreach ($variables_right['images_right'] as $key=>$image_right): ?>
                    <div class="slider__right-item-other-marker">
                        <div class="slider__right-item-other-marker_image" style="background-image: url(<?=$image_right?>)"
                             data-description_left   = "<?= htmlspecialchars($variables_left['description_left'])?>"
                             data-description_right  = "<?= htmlspecialchars($variables_right['description_right'])?>"
                             data-img_left           = "<?= htmlspecialchars($variables_left['img_left'])?>"
                             data-img_right          = "<?= htmlspecialchars($image_right)?>"
                             data-footer_left        = "<?= htmlspecialchars($variables_left ['footer_left'])?>"
                             data-footer_right       = "<?= htmlspecialchars($variables_right['footer_right'])?>"
                             data-count_images_right = "<?= htmlspecialchars($variables_right['count_images_right'])?>"
                        ></div>
                    </div>
                <?php
                    if($key > 3) {
                        break;
                    }
                endforeach; ?>
            </div>
        <?php endif;?>
    </div>
    <?php $cnt++; endforeach; ?>
</div>
