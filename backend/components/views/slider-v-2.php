<?php

/**
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
 * @var $source
 * @var $items
 */


use common\models\Comparison;
use yii\helpers\Html;
use backend\models\User;

$node = Yii::$app->request->get('node');
$canCompare = \Yii::$app->user->can('compare-products', ['product' => $product]);
$cnt = 1;

$variables_left = $this->context->getVariablesLeft($product);
?>

<? if (User::is_detail_view_for_items() || $is_admin = User::isAdmin()):?>
    <div class="main-item-title ">
        <? if ($is_admin): ?><a target="_blank" href="<?=$product->baseInfo['URL: Amazon']?>"><? endif; ?>
            <?= $variables_left['description_left'] ?>
        <? if ($is_admin): ?></a><? endif; ?>
    </div>
<? endif; ?>
        
<!-- VIEW 2 -->
<div class='slider__view-2 [ SLIDER ] product-view__slider'  >
    <? foreach ($items as $index => $item): ?>
        <?php
            if ($get_['filter-items__comparisons'] !== 'ALL'){
                if ( ($no_compare && isset($comparisons[$index])) ){
                    if ($comparisons[$index]->status === 'PRE_MATCH' || $comparisons[$index]->status === 'MATCH' || $comparisons[$index]->status === 'MISMATCH' || $comparisons[$index]->status === 'OTHER'){
                        continue;
                    }
                }
            }
            
            if ($get_['filter-items__comparisons'] !== 'YES_NO_OTHER'){
                if ($get_['filter-items__comparisons'] !== 'ALL'){
                    if (!$no_compare && $is_filter_items && $get_['filter-items__comparisons'] ){
                        if ($comparisons[$index]->status !== $get_['filter-items__comparisons']) {
                            continue;
                        }
                    }
                }
            } else {
                if (!$no_compare && $is_filter_items && $get_['filter-items__comparisons'] ){
                    if (!in_array($comparisons[$index]->status,['PRE_MATCH','OTHER','MISMATCH','MATCH'])){ 
                        continue;                       
                    }
                }
            }

            // Инициализаця переменных:
            $variables_right = $this->context->getVariablesRight($source, $item);

            $urlKey_right = $item->urlKey;
            $node_id = $index + 1;
            $current = ($page === $index) ? '&load_next=1' : '&load_next=0';
        ?>

        <div
            data-node_id="<?= $node_id ?>"
            class="tbl [ SLIDER-ITEM ] slider__slider-item -v-2 <?= $page === $index ? '-current' : '' ?> item<?= (int)$node === $index ? " slick-current" : '' ?>"
        >
            <div class="tr slider-item__border <?= $page === $index ? '-current' : '' ?> -v-2">
                <div class="[ color-marker ] vertical <?= isset($comparisons[$index]) ? ($comparisons[$index]->status === 'MATCH' ? ' match' : ($comparisons[$index]->status === 'MISMATCH' ? ' mismatch' : ($comparisons[$index]->status === 'PRE_MATCH' ? ' pre_match' : ' other'))) : ' nocompare' ?>">
                </div>

                <div class="td -img"
                    data-description_left   = "<?= htmlspecialchars($variables_left['description_left'])?>"
                    data-description_right  = "<?= htmlspecialchars($variables_right['description_right'])?>"
                    data-img_left           = "<?= htmlspecialchars($variables_left['img_left'])?>"
                    data-img_right          = "<?= htmlspecialchars($variables_right['img_right'])?>"
                    data-footer_left        = "<?= htmlspecialchars($variables_left ['footer_left'])?>"
                    data-footer_right       = "<?= htmlspecialchars($variables_right['footer_right'])?>"
                    data-count_images_right = "<?= htmlspecialchars($variables_right['count_images_right'])?>"                          
                >
                    <?=
                    Html::a(
                            "<div class=\"slider-item__img\" data-img='" . $variables_right['img_right'] . "' style=\"background-image: url('" . $variables_right['img_right'] . "')\"></div>",
                            ['view', 'id' => $product_id,
                                'node' => $node_id,
                                'source_id' => $source['source_id'],
                                'comparisons' => $get_['filter-items__comparisons'],
                                'filter-items__profile' => $get_['filter-items__profile']
                            ],
                            ['class' => 'linkImg slider-item__link-img -v-2']
                    )
                    ?>

                </div>

                <div class="td td-4">
                    <table class="table_reset layout__item_detailed_view">
                        <tr>
                            <td>
                                <div class="-title"><a class="a-black" target="_blank" href="<?= $urlKey_right ?>"><?= $variables_right['description_right'] ?></a></div>

                                <!-- FORK ( Содержимое слайдера ) -->
                                    <div> <?= $variables_right['footer_right'] ?> </div>
                                <!-- / FORK -->
                                <? if ((count($variables_right['images_right']) > 1)): ?>
                                        <div class="slider__right-item-other-imgs">
                                            <? foreach ($variables_right['images_right'] as $image_right): ?>
                                                <div class="slider__right-item-other-img slider_images" style="background-image: url(<?=$image_right?>)"
                                                    data-description_left   = "<?= htmlspecialchars($variables_left['description_left'])?>"
                                                    data-description_right  = "<?= htmlspecialchars($variables_right['description_right'])?>"
                                                    data-img_left           = "<?= htmlspecialchars($variables_left['img_left'])?>"
                                                    data-img_right          = "<?= htmlspecialchars($image_right)?>"
                                                    data-footer_left        = "<?= htmlspecialchars($variables_left ['footer_left'])?>"
                                                    data-footer_right       = "<?= htmlspecialchars($variables_right['footer_right'])?>"
                                                    data-count_images_right = "<?= htmlspecialchars($variables_right['count_images_right'])?>"                                                                                                            
                                                ></div>
                                            <? endforeach; ?>
                                        </div>
                                <? endif;?>
                            </td>

                            <td style="text-align: right;">
                                <? $link = '/product/compare?id='.$product_id.'&source_id='.$source['source_id'].'&node='.($index+1).'&status='.Comparison::STATUS_PRE_MATCH.$current?>
                                <div
                                    class="slider__yellow_button -v-2 <?= $comparisons[$index]->status === 'PRE_MATCH' ? '-hover' : '' ?>"
                                    data-link = "<?= Html::encode($link) ?>"
                                >
                                </div>

                                <? $link = '/product/compare?id='.$product_id.'&source_id='.$source['source_id'].'&node='.($index+1).'&status='.Comparison::STATUS_MISMATCH.$current?>
                                <div
                                    class="slider__red_button -v-2 <?= $comparisons[$index]->status === 'MISMATCH' ? '-hover' : '' ?>"
                                    data-link = "<?= Html::encode($link) ?>"
                                >
                                </div>
                            </td>
                        </tr>
                    </table>
                </div> <!-- td-4 -->

                <div class="td -btn" style="display: none">
                    <? $link = '/product/compare?id='.$product_id.'&source_id='.$source['source_id'].'&node='.($index+1).'&status='.Comparison::STATUS_MISMATCH.$current?>
                    <div
                        class="slider__red_button -v-2"
                        data-link = "<?= Html::encode($link) ?>"
                    >
                    </div>
                </div>

                <div class="td -btn" style="display: none">
                    <? $link = '/product/compare?id='.$product_id.'&source_id='.$source['source_id'].'&node='.($index+1).'&status='.Comparison::STATUS_MATCH.$current?>
                    <div
                        class="slider__green_button -v-2"
                        data-link = "<?= Html::encode($link) ?>"
                    >
                    </div>
                </div>
            </div>
        </div>
    <? endforeach;?>
</div>