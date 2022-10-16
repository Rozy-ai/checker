<?php

/**
 * Отображение списка кратко
 * 
 * @var $product
 * @var $page
 * @var $comparisons
 * @var $product_id
 * @var $options
 * @var $hide_red
 * @var $no_compare
 * @var $compare_item
 * @var $right_item_show
 * @var $is_filter_items
 * @var $get_
 * @var $items
 */


use common\models\Comparison;
use yii\helpers\Html;
use backend\models\User;
use yii\helpers\Url;

$node = Yii::$app->request->get('node');
$canCompare = \Yii::$app->user->can('compare-products', ['product' => $product]);
$cnt = 1;

$variables_left = $this->context->getVariablesLeft($product);

$source_id = $product->source_id;
$source    = $product->source;
?>

<!-- Если администратор, то показываем в виде ссылки -->
<? if (User::is_detail_view_for_items() || $is_admin = User::isAdmin()):?>
    <div class="main-item-title ">
        <? if ($is_admin): ?><a target="_blank" href="<?=$product->baseInfo['URL: Amazon']?>"><? endif; ?>
            <?= $variables_left['description_left'] ?>
        <? if ($is_admin): ?></a><? endif; ?>
    </div>
<? endif; ?>
 
<!-- VIEW 1 -->
<div class='slider__view-1 <?= $options['class'] ?> [ SLIDER ] product-view__slider'>
    <? foreach ($items as $index => $item): ?>

    <?
        // Проверка фильтров
        if ($get_['filter-items__comparisons'] !== 'ALL' &&
            $get_['filter-items__comparisons'] !== 'ALL_WITH_NOT_FOUND' &&
            ($no_compare && isset($comparisons[$index])) &&
            ($comparisons[$index]->status === 'PRE_MATCH' || $comparisons[$index]->status === 'MATCH' || $comparisons[$index]->status === 'MISMATCH' || $comparisons[$index]->status === 'OTHER') )
            {
                continue;
            }

        if ($get_['filter-items__comparisons'] !== 'YES_NO_OTHER'){
        if ($get_['filter-items__comparisons'] !== 'ALL')
        if ($get_['filter-items__comparisons'] !== 'ALL_WITH_NOT_FOUND')
        if (!$no_compare && $is_filter_items && $get_['filter-items__comparisons'] )
            if ($comparisons[$index]->status !== $get_['filter-items__comparisons']) 
                continue;
            } else{
                if (!$no_compare && $is_filter_items && $get_['filter-items__comparisons'] )
                if (!in_array($comparisons[$index]->status,['PRE_MATCH','OTHER','MISMATCH','MATCH'])) 
                    continue;
            }

        //Иниацияализация переменных
        $variables_right = $this->context->getVariablesRight($source, $item, true);
        $current = ($page === $index) ? '&load_next=1' : '&load_next=0'
    ?>

    <div
        data-node_id="<?= $index + 1 ?>"
        class="[ SLIDER-ITEM ] slider__slider-item <?= $page === $index ? '-current' : '' ?> item<?= (int) $node === $index ? " slick-current" : '' ?>"
    >
        <!--slider_images несодержит стилей. Добавлен для отображения TopSlider-->
        <div
            class="slider-item__border slider_images <?= $page === $index ? '-current' : '' ?>"
  
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
                    ['view', 'id' => $product_id, 'node' => $index + 1, 'source_id' => $source_id, 'comparisons' => $get_['filter-items__comparisons'], 'filter-items__profile' => $get_['filter-items__profile']],
                    ['class' => 'linkImg slider-item__link-img']
            )
            ?>


            <!-- FORK -->
            <? if ($source->name === 'EBAY'): ?>
            <div class="slider-item__cnt-1">
                <span class="cnt-1__stock-title __blue-title">Stock:</span>
                <span class="grade cnt-1__stock-n"><?= $item->gradeKey; ?></span>
            </div>

            <? if (!empty($options['salesKey'])): ?>
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

            <? if (0 && !empty($options['delBtn']) && $options['delBtn'] && $canCompare): ?>
                <?=
                Html::a("", [
                    'compare',
                    'id' => $product_id,
                    'node' => $index + 1,
                    'status' => Comparison::STATUS_MISMATCH,
                    'return' => true,
                        ], ['class' => 'btn del']
                )
                ?>
            <? endif; ?>

            <?php $link = Url::to(['product/compare',
                'id'=>$product_id,
                'source_id'=>$source_id,
                'node'=>($index+1),
                'status'=>Comparison::STATUS_PRE_MATCH],true).$current;
            ?>
            <div
                class="slider__yellow_button _slider__green_button -v-2 -v-3 -min <?= $comparisons[$index]->status === 'PRE_MATCH' ? '-hover' : '' ?>"
                data-link = "<?= Html::encode($link) ?>"

                >
            </div>
            <?php $link = Url::to(['product/compare',
                'id'=>$product_id,
                'source_id'=>$source_id,
                'node'=>($index+1),
                'status'=>Comparison::STATUS_MISMATCH],true).$current;
            ?>
            <div
                class="slider__red_button -min <?= $comparisons[$index]->status === 'MISMATCH' ? '-hover' : '' ?>"
                data-link = "<?= Html::encode($link) ?>"            
                >

            </div>

            <? if (0): ?>
                <?=
                Html::a("", [
                    'compare',
                    'id' => $product_id,
                    'node' => $index + 1,
                    'status' => Comparison::STATUS_MISMATCH,
                        /* 'return' => true, */
                        ], ['class' => 'slider__red_button', 'target' => '_blank']
                )
                ?>
            <? endif; ?>

        </div>

        <a href="/product/view?id=<?= $product->id ?>&source_id=<?= $source_id ?>&node=<?= $index + 1 ?>&comparisons=<?= $get_['filter-items__comparisons'] ?>&filter-items__profile=<?= $get_['filter-items__profile'] ?>"
           class="[ PAGE-N ]  slider__page-n <?= $page === $index ? '-current' : '' ?>"><?= $index + 1 ?><?//=$cnt?></a>

    </div>
    <? $cnt++; endforeach; ?>
</div>