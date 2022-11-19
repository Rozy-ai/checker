<?php

/**
 * Отображение списка кратко
 * 
 * @var string $f_comparison_status
 * @var string $f_profile
 * @var bool   $is_admin
 * @var int    $number_page_current
 * @var $option_class_slider
 * @var $option_sales_key
 * @var $option_del_btn
 * @var $number_node Позиция активного товара начиная от 0
 * @var Product $product
 * @var $number_node
 * @var $items
 */


use common\models\Comparison;
use yii\helpers\Html;
use common\models\User;
use yii\helpers\Url;
        
$source         = $product->source;
$comparisons    = $product->comparisons;

$canCompare = \Yii::$app->user->can('compare-products', ['product' => $product]);
$cnt = 1;

$variables_left = $this->context->getVariablesLeft($product);
$source_id = $source->id;
$identity = \Yii::$app->user->identity;
?>

<!-- Если администратор, то показываем в виде ссылки -->

<? if ($identity && ($identity->is_detail_view_for_items() || $identity->isAdmin())): ?>
    <div class="main-item-title ">
        <? if ($is_admin): ?><a target="_blank" href="<?=$product->baseInfo['URL: Amazon']?>"><? endif; ?>
            <?= $variables_left['description_left'] ?>
        <? if ($is_admin): ?></a><? endif; ?>
    </div>
<? endif; ?>
 
<!-- VIEW 1 -->
<div class='slider__view-1 <?= $option_class_slider ?> [ SLIDER ] product-view__slider'>
    <?php 
        foreach ($items as $index => $item): 
    ?>

    <?php
        $comparison = $comparisons[$item->id];

        if (isset($comparison->status)){
            if ($f_comparison_status == 'NOCOMPARE'){
                if ($comparison->status != 'NOCOMPARE') continue;
            } elseif ($f_comparison_status == 'PRE_MATCH'){
                if ($comparison->status != 'PRE_MATCH') continue;
            } elseif ($f_comparison_status == 'MATCH') {
                if ($comparison->status != 'MATCH') continue;
            } elseif ($f_comparison_status == 'OTHER') {
                if ($comparison->status != 'OTHER') continue; 
            } elseif($f_comparison_status == 'YES_NO_OTHER') {
                if ($comparison->status != 'PRE_MATCH' &&
                    $comparison->status != 'MATCH' &&
                    $comparison->status != 'OTHER') return;
            } elseif ($f_comparison_status == 'MISSMATCH') {
                if ($f_comparison_status != 'MISSMATCH') return;
            }
            
            /*
           

        if (isset($comparison->status)){
            switch ($f_comparison_status){
                case 'NOCOMPARE':
                    if ($comparison->status != 'NOCOMPARE') {
                        continue 2;
                    }; break;
                case 'PRE_MATCH':
                    if ($comparison->status != 'PRE_MATCH') {
                        continue 2;
                    }; break;
                case 'MATCH':
                    if ($comparison->status != 'MATCH') {
                        continue 2;
                    }; break;
                case 'OTHER':
                    if ($comparison->status != 'OTHER') {
                        continue 2;
                    }; break;                    
                case 'YES_NO_OTHER':
                    if (!in_array($comparison->status,['PRE_MATCH', 'MATCH', 'OTHER'])) {
                        continue 2;
                    }; break;
                case 'MISSMATCH':
                    if ($comparison->status != 'MISSMATCH') {
                        continue 2;
                    }; break;  
            }
             * 
             */
        }               
        
        //Иниацияализация переменных
        $variables_right = $this->context->getVariablesRight($source, $item, true);
        $current = ($number_page_current === $index) ? '&load_next=1' : '&load_next=0'
    ?>

    <div
        data-node_id="<?= $index + 1 ?>"
        class="[ SLIDER-ITEM ] slider__slider-item <?= $number_page_current === $index ? '-current' : '' ?> item<?= (int) $number_node === $index ? " slick-current" : '' ?>"
    >
        <!--slider_images несодержит стилей. Добавлен для отображения TopSlider-->
        <div
            class="slider-item__border slider_images <?= $number_page_current === $index ? '-current' : '' ?>"
  
            data-description_left   = "<?= htmlspecialchars($variables_left['description_left'])?>"
            data-description_right  = "<?= htmlspecialchars($variables_right['description_right'])?>"
            data-img_left           = "<?= htmlspecialchars($variables_left['img_left'])?>"
            data-img_right          = "<?= htmlspecialchars($variables_right['img_right'])?>"
            data-footer_left        = "<?= htmlspecialchars($variables_left ['footer_left'])?>"
            data-footer_right       = "<?= htmlspecialchars($variables_right['footer_right'])?>"
            data-count_images_right = "<?= htmlspecialchars($variables_right['count_images_right'])?>"             
        >
            <div class="[ color-marker ] horizontal <?= isset($comparisons[$index]) ? ($comparisons[$index]->status === 'MATCH' ? ' match' : ($comparisons[$index]->status === 'MISMATCH' ? ' mismatch' : ($comparisons[$index]->status === 'PRE_MATCH' ? ' pre_match' : ' other'))) : ' nocompare' ?>"></div>

            <?=
            Html::a(
                    "<div class=\"slider-item__img\" data-img='" . $variables_right['img_right'] . "' style=\"background-image: url('" . $variables_right['img_right'] . "')\"></div>",
                    ['view', 'id' => $product->id, 'number_node' => $index + 1, 'source_id' => $source_id, 'comparisons' => $f_comparison_status, 'filter-items__profile' => $f_profile],
                    ['class' => 'linkImg slider-item__link-img']
            )
            ?>


            <!-- FORK -->
            <? if ($source->name === 'EBAY'): ?>
            <div class="slider-item__cnt-1">
                <span class="cnt-1__stock-title __blue-title">Stock:</span>
                <span class="grade cnt-1__stock-n"><?= $item->gradeKey; ?></span>
            </div>

            <? if (!empty($option_sales_key)): ?>
            <span class="slider__sales sales">
                <? if (\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) !== null): ?>
                    <span class="__blue-title">Sales:</span><?= preg_replace('|\D|', '', $item->salesKey) ?: 0 ?>
                <? else: ?>
                    <span class="__blue-title">Sales:</span><?= preg_replace('|\D|', '', $item->salesKey) ?: 0 ?>
                <? endif; ?>
            </span>
            <? endif; ?>
            <? elseif ($source->name === 'CHINA'): ?>
            <div class="slider-item__cnt-1">
                <span class="cnt-1__stock-title __blue-title">ROI:</span>
                <span class="grade cnt-1__stock-n"><?= $item->ROI_Ali; ?></span>
            </div>
            <div class="slider__sales sales" style="margin: 0">
                <!--<span class="__blue-title">R:</span>--><?= $item->rating; ?>
            </div>
            <? endif; ?>
            <span class="slider__sales sales">
                <span class="cnt-1__stock-title __blue-title">Price:</span><span class="grade cnt-1__stock-n"><?= $item->price ?></span>
            </span>

            <!-- / FORK -->
            
            <?php $link = Url::to(['product/compare',
                'id'=>$product->id,
                'source_id'=>$source_id,
                'number_node'=>($index+1),
                'status'=>Comparison::STATUS_PRE_MATCH],true).$current;
            ?>
            <div
                class="slider__yellow_button _slider__green_button -v-2 -v-3 -min <?= $comparisons[$index]->status === 'PRE_MATCH' ? '-hover' : '' ?>"
                data-link = "<?= Html::encode($link) ?>"
            >
            </div>

            <div
                class="slider__red_button -min <?= $comparisons[$index]->status === 'MISMATCH' ? '-hover' : '' ?>"
                data-url = "/product/compare"
                data-id_source = "<?=$source_id?>"
                data-id_product = "<?=$product->id?>"
                data-id_item = "<?=$item->id?>"
            >

            </div>

        </div>

        <a href="/product/view?id=<?= $product->id ?>&source_id=<?= $source_id ?>&node=<?= $index + 1 ?>&comparisons=<?= $f_comparison_status ?>&filter-items__profile=<?= $f_profile ?>"
           class="[ PAGE-N ]  slider__page-n <?= $number_page_current === $index ? '-current' : '' ?>"><?= $index + 1 ?><?//=$cnt?></a>

    </div>
    <?php $cnt++; endforeach; ?>
</div>