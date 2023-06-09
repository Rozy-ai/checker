<?php

 /** Отображение списка подробно */

 /** @var string $f_comparison_status                       */
 /** @var string $f_profile                                 */
 /** @var bool   $is_admin                                  */
 /** @var int    $number_page_current                       */
 /** @var $option_class_slider                              */
 /** @var $option_sales_key                                 */
 /** @var Product $product                                  */
 /** @var array $items                                      */


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

<div id="id_td3_title" class="main-item-title ">
    <?php if ($identity && ($identity->is_detail_view_for_items() || $is_admin)): ?><a target="_blank" href="<?=$product->baseInfo['URL: Amazon']?>"><?php endif; ?>
        <?= $variables_left['description_left'] ?>
    <?php if ($identity && ($identity->is_detail_view_for_items() || $is_admin)): ?></a><?php endif; ?>
</div>
        
<!-- VIEW 2 -->
<div class='slider__view-2 [ SLIDER ] product-view__slider'  >
    <?php foreach ($items as $index => $item): ?>
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
            // Инициализаця переменных:
            $variables_right = $this->context->getVariablesRight($product->source, $item, false);

            $urlKey_right = $item->urlKey;
            $current = ($number_page_current === $index) ? '&load_next=1' : '&load_next=0';
        ?>

        <div
            data-id_source="<?=$source_id?>"
            data-id_product="<?=$product->id?>"
            data-id_item="<?=$item->id?>"
            data-status="<?=$comparison->status?>"
            data-node_id="<?= $node_id ?>"
            class="tbl [ SLIDER-ITEM ] slider__slider-item -v-2 <?= $number_page_current === $index ? '-current' : '' ?> item<?= (int)$node === $index ? " slick-current" : '' ?>"
        >
            <div class="tr slider-item__border <?= $number_page_current === $index ? '-current' : '' ?> -v-2">
                <div class="[ color-marker ] vertical <?= isset($comparisons[$item->id]) ? ($comparisons[$item->id]->status === 'MATCH' ? ' match' : ($comparisons[$item->id]->status === 'MISMATCH' ? ' mismatch' : ($comparisons[$item->id]->status === 'PRE_MATCH' ? ' pre_match' : ' other'))) : ' nocompare' ?>">
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
                            ['view', 'id' => $product->id,
                                'node' => $node_id,
                                'source_id' => $source_id,
                                'comparisons' => $f_comparison_status,
                                'filter-items__profile' => $f_profile
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
                                <?php if ((count($variables_right['images_right']) > 1)): ?>
                                        <div class="slider__right-item-other-imgs">
                                            <?php foreach ($variables_right['images_right'] as $image_right): ?>
                                                <div class="slider__right-item-other-img slider_images" style="background-image: url(<?=$image_right?>)"
                                                    data-description_left   = "<?= htmlspecialchars($variables_left['description_left'])?>"
                                                    data-description_right  = "<?= htmlspecialchars($variables_right['description_right'])?>"
                                                    data-img_left           = "<?= htmlspecialchars($variables_left['img_left'])?>"
                                                    data-img_right          = "<?= htmlspecialchars($image_right)?>"
                                                    data-footer_left        = "<?= htmlspecialchars($variables_left ['footer_left'])?>"
                                                    data-footer_right       = "<?= htmlspecialchars($variables_right['footer_right'])?>"
                                                    data-count_images_right = "<?= htmlspecialchars($variables_right['count_images_right'])?>"                                                                                                            
                                                ></div>
                                            <?php endforeach; ?>
                                        </div>
                                <?php endif;?>
                            </td>

                            <td style="text-align: right;">
                                <div
                                    class="slider__yellow_button -v-2 <?= $comparisons[$item->id]->status === 'PRE_MATCH' ? '-hover' : '' ?>"
                                    data-url = "/product/compare"
                                    data-id_source = "<?=$source_id?>"
                                    data-id_product = "<?=$product->id?>"
                                    data-id_item = "<?=$item->id?>"
                                    data-status = "<?= Comparison::STATUS_PRE_MATCH ?>"
                                    data-is_last = "<?=$is_last?>"
                                >
                                </div>

                                <div
                                    class="slider__red_button -v-2 <?= $comparisons[$item->id]->status === 'MISMATCH' ? '-hover' : '' ?>"
                                    data-url = "/product/compare"
                                    data-id_source = "<?=$source_id?>"
                                    data-id_product = "<?=$product->id?>"
                                    data-id_item = "<?=$item->id?>"
                                    data-status = "<?= Comparison::STATUS_MISMATCH ?>"
                                    data-is_last = "<?=$is_last?>"                                
                                >
                                </div>
                            </td>
                        </tr>
                    </table>
                </div> <!-- td-4 -->

                <div class="td -btn" style="display: none">
                    <?php $link = Url::to(['product/compare',
                        'id'=>$product->id,
                        'source_id'=>$source_id,
                        'node'=>($index+1),
                        'status'=>Comparison::STATUS_MISMATCH],true).$current;
                    ?>
                    <div
                        class="slider__red_button -v-2"
                        data-link = "<?= Html::encode($link) ?>"
                    >
                    </div>
                </div>

                <div class="td -btn" style="display: none">
                    <?php $link = Url::to(['product/compare',
                        'id'=>$product->id,
                        'source_id'=>$source_id,
                        'node'=>($index+1),
                        'status'=>Comparison::STATUS_MATCH],true).$current;
                    ?>
                    <div
                        class="slider__green_button -v-2"
                        data-link = "<?= Html::encode($link) ?>"
                    >
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach;?>
</div>
