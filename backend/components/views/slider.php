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


use backend\models\User;
use common\models\Comparison;
use yii\helpers\Html;
$node = Yii::$app->request->get('node');
$canCompare = \Yii::$app->user->can('compare-products', ['product' => $product]);
$cnt = 1;

$r_title =  \backend\models\Settings__source_fields::name_for_source('r_Title');
$current = '';
?>


<? if (User::is_detail_view_for_items() || $is_admin = User::isAdmin()):?>

<div class="main-item-title ">
  <? if ($is_admin): ?><a target="_blank" href="<?=$product->baseInfo['URL: Amazon']?>"><? endif; ?>
    <?=$product->baseInfo['Title']?>
  <? if ($is_admin): ?></a><? endif; ?>
</div>
  <!-- VIEW 2 -->
  <div class='slider__view-2 [ SLIDER ] product-view__slider <?=((int)$right_item_show === 0) ? '-hide' : ''?>'  >
    <? foreach ($items as $index => $item): ?>
      <?
      if ($get_['filter-items__comparisons'] !== 'ALL')
        if ( ($no_compare && isset($comparisons[$index])) ){
          if ($comparisons[$index]->status === 'PRE_MATCH' || $comparisons[$index]->status === 'MATCH' || $comparisons[$index]->status === 'MISMATCH' || $comparisons[$index]->status === 'OTHER'){
            continue;
          }
        }

      if ($get_['filter-items__comparisons'] !== 'YES_NO_OTHER'){
        if ($get_['filter-items__comparisons'] !== 'ALL')
          //            bool(false)     1                     YES_NO_OTHER
          if (!$no_compare && $is_filter_items && $get_['filter-items__comparisons'] )
            //                    PRE_MATCH                     YES_NO_OTHER
            if ($comparisons[$index]->status !== $get_['filter-items__comparisons']) continue;
      }else{
        if (!$no_compare && $is_filter_items && $get_['filter-items__comparisons'] )
          //                    PRE_MATCH                     YES_NO_OTHER
          if (!in_array($comparisons[$index]->status,['PRE_MATCH','OTHER','MISMATCH','MATCH'])) continue;
      }


      $image_right = preg_split("/[; |,|\|]/", $item->srcKey)[0];
      ?>
      <? $current = ($page === $index) ? '&load_next=1' : '&load_next=0'?>

      <div
        data-node_id="<?=$index + 1?>"
        class="tbl [ SLIDER-ITEM ] slider__slider-item -v-2 <?= $page === $index ? '-current' : '' ?> item<?= (int)$node === $index ? " slick-current" : '' ?>"
      >

        <div
          <? if ($r_title): ?>
          data-text_brief="<?=Html::encode($item->r_Title)?>"
          <? endif; ?>
          <!--data-text_brief="--><?/*=Html::encode($item['eBay_title'])*/?>"
          class="tr slider-item__border <?= $page === $index ? '-current' : '' ?> -v-2"
        >

          <div class="[ color-marker ] vertical <?= isset($comparisons[$index]) ? ($comparisons[$index]->status === 'MATCH' ? ' match' : ($comparisons[$index]->status === 'MISMATCH' ? ' mismatch' : ($comparisons[$index]->status === 'PRE_MATCH' ? ' pre_match' : ' other'))) : ' nocompare' ?>"></div>
          <!--<div class="td td-2"></div>-->

          <div class="td -img">
            <?= Html::a(
              "<div class=\"slider-item__img\" data-img='".$image_right."' style=\"background-image: url('".$image_right."')\"></div>",
              ['view', 'id' => $product_id,
                       'node' => $index + 1,
                       'source_id' => $source['source_id'] ,
                       'comparisons' => $get_['filter-items__comparisons'],
                       'filter-items__profile' => $get_['filter-items__profile']
              ],

              ['class' => 'linkImg slider-item__link-img -v-2']
            )?>

          </div>
          <div class="td td-4">

            <table class="table_reset layout__item_detailed_view">
              <tr>
                <td>

                  <div class="-title"><a class="a-black" target="_blank" href="<?= $item->urlKey ?>"><?=$item->r_Title;?></a></div>
  <!-- FORK -->

                  <div class="js-copy-for-view-1">
                    <? if ($source['source_name'] === 'EBAY'): ?>

                      <? if ($item->salesKey): ?>
                        <span class="_slider__sales sales"><span class="__blue-title">Sales:</span><?= preg_replace('|\D|','', $item->salesKey) ?: 0 ?></span>
                      <? endif; ?>

                      <span class="_slider-item__cnt-1">
                        <span class="cnt-1__stock-title __blue-title">Stock:</span><span class="_grade cnt-1__stock-n"><?= $item->gradeKey ?: 0 ?></span>
                      </span>

                      <span><span class=" __blue-title">Price:</span><?=$item->price?></span>
                      <span><span class=" __blue-title">Rating:</span><?=$item->rating?></span>

                    <? elseif ($source['source_name'] === 'CHINA'): ?>
                      <span><span class=" __blue-title">MQO:</span><?=$item->MOQ_Ali?></span>
                      <span><span class=" __blue-title">Total:</span><?=$item->Total_Ali?> (<?=$item->Quantity_Ali?>) </span>
                      <span><span class=" __blue-title">Price:</span><?=$item->price?></span>
                      <span><span class=" __blue-title">ROI:</span><?=$item->ROI_Ali?></span>
                      <span><span class=" __blue-title">Rating:</span><?=$item->rating?></span>

                    <? endif; ?>
                  </div>


  <!-- / FORK -->
                </td>
                <td style="text-align: right;">

                  <? $link = '/product/compare?id='.$product_id.'&source_id='.$source['source_id'].'&node='.($index+1).'&status='.Comparison::STATUS_PRE_MATCH.$current?>
                  <div
                    class="slider__yellow_button -v-2 <?=$comparisons[$index]->status === 'PRE_MATCH' ? '-hover' : '' ?>"
                    data-link = "<?=Html::encode($link)?>"
                  >
                  </div>

                  <? $link = '/product/compare?id='.$product_id.'&source_id='.$source['source_id'].'&node='.($index+1).'&status='.Comparison::STATUS_MISMATCH.$current?>
                  <div
                    class="slider__red_button -v-2 <?=$comparisons[$index]->status === 'MISMATCH' ? '-hover' : '' ?>"
                    data-link = "<?=Html::encode($link)?>"
                  >
                  </div>


                </td>
              </tr>
            </table>

            <!--<div class="two__btn_floated"></div>-->


          </div> <!-- td-4 -->


          <div class="td -btn" style="display: none">
            <? $link = '/product/compare?id='.$product_id.'&source_id='.$source['source_id'].'&node='.($index+1).'&status='.Comparison::STATUS_MISMATCH.$current?>
            <div
              class="slider__red_button -v-2"
              data-link = "<?=Html::encode($link)?>"
            >
            </div>
          </div>

          <div class="td -btn" style="display: none">
            <? $link = '/product/compare?id='.$product_id.'&source_id='.$source['source_id'].'&node='.($index+1).'&status='.Comparison::STATUS_MATCH.$current?>
            <div
              class="slider__green_button -v-2"
              data-link = "<?=Html::encode($link)?>"
            >
            </div>
          </div>




       </div>

      </div> <!-- / [ SLIDER-ITEM ] -->

    <? endforeach;?>
  </div>
<? endif;?>








<!-- VIEW 1 -->
<div
  class='slider__view-1 <?= $options['class'] ?> [ SLIDER ] product-view__slider <?=((int)$right_item_show === 1) ? '-hide' : ''?>''>
    <? foreach ($items as $index => $item): ?>

      <?
      if ($get_['filter-items__comparisons'] !== 'ALL')
      if ($get_['filter-items__comparisons'] !== 'ALL_WITH_NOT_FOUND')
      if ( ($no_compare && isset($comparisons[$index])) ){
        if ($comparisons[$index]->status === 'PRE_MATCH' || $comparisons[$index]->status === 'MATCH' || $comparisons[$index]->status === 'MISMATCH' || $comparisons[$index]->status === 'OTHER'){
          continue;
        }
      }

      if ($get_['filter-items__comparisons'] !== 'YES_NO_OTHER'){
        if ($get_['filter-items__comparisons'] !== 'ALL')
        if ($get_['filter-items__comparisons'] !== 'ALL_WITH_NOT_FOUND')
  //            bool(false)     1                     YES_NO_OTHER
          if (!$no_compare && $is_filter_items && $get_['filter-items__comparisons'] )
  //                    PRE_MATCH                     YES_NO_OTHER
            if ($comparisons[$index]->status !== $get_['filter-items__comparisons']) continue;
      }else{
        if (!$no_compare && $is_filter_items && $get_['filter-items__comparisons'] )
          //                    PRE_MATCH                     YES_NO_OTHER
          if (!in_array($comparisons[$index]->status,['PRE_MATCH','OTHER','MISMATCH','MATCH'])) continue;
      }


      $image_right = preg_split("/[; |,|\|]/", $item->srcKey)[0];
      ?>

      <? $current = ($page === $index) ? '&load_next=1' : '&load_next=0'?>
        <div
          data-node_id="<?=$index + 1?>"
          class="[ SLIDER-ITEM ] slider__slider-item <?= $page === $index ? '-current' : '' ?> item<?= (int)$node === $index ? " slick-current" : '' ?>"
        >
          <div
            <? if ($r_title): ?>
            data-text_brief="<?=Html::encode($item->r_Title)?>"
            <? endif;?>
            class="slider-item__border <?= $page === $index ? '-current' : '' ?>">

            <div class="[ color-marker ] horizontal <?= isset($comparisons[$index]) ? ($comparisons[$index]->status === 'MATCH' ? ' match' : ($comparisons[$index]->status === 'MISMATCH' ? ' mismatch' : ($comparisons[$index]->status === 'PRE_MATCH' ? ' pre_match' : ' other'))) : ' nocompare' ?>"></div>

            <?= Html::a(
              "<div class=\"slider-item__img\" data-img='".$image_right."' style=\"background-image: url('".$image_right."')\"></div>",
              ['view', 'id' => $product_id, 'node' => $index + 1, 'source_id' => $source['source_id'] , 'comparisons' => $get_['filter-items__comparisons'], 'filter-items__profile' => $get_['filter-items__profile']],
              ['class' => 'linkImg slider-item__link-img']
            )?>


<!-- FORK -->
<? if ($source['source_name'] === 'EBAY'): ?>
            <div class="slider-item__cnt-1">
              <span class="cnt-1__stock-title __blue-title">Stock:</span><span class="grade cnt-1__stock-n"><?= $item->gradeKey;?></span>
            </div>


            <? if (!empty($options['salesKey'])): ?>
                <span class="slider__sales sales">
                <? if (\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) !== null): ?>
                   <span class="__blue-title">Sales:</span><?= preg_replace('|\D|','', $item->salesKey) ?: 0 ?>
                <? else: ?>
                  <span class="__blue-title">Sales:</span><?= preg_replace('|\D|','', $item->salesKey) ?: 0 ?>
                <? endif; ?>

                </span>
            <? endif; ?>
<? elseif ($source['source_name'] === 'CHINA'): ?>
  <div class="slider-item__cnt-1">
    <span class="cnt-1__stock-title __blue-title">ROI:</span><span class="grade cnt-1__stock-n"><?= $item->ROI_Ali;?></span>
  </div>
  <div class="slider__sales sales" style="margin: 0">
    <!--<span class="__blue-title">R:</span>--><?= $item->rating;?>
  </div>



<? endif; ?>
<!-- / FORK -->

              <? if (0 && !empty($options['delBtn']) && $options['delBtn'] && $canCompare): ?>
                  <?= Html::a("", [
                      'compare',
                      'id' => $product_id,
                      'node' => $index+1,
                      'status' => Comparison::STATUS_MISMATCH,
                      'return' => true,
                  ], ['class' => 'btn del']
                  ) ?>
              <? endif; ?>



            <? $link = '/product/compare?id='.$product_id.'&source_id='.$source['source_id'].'&node='.($index+1).'&status='.Comparison::STATUS_PRE_MATCH.$current?>
            <div
              class="slider__yellow_button _slider__green_button -v-2 -v-3 -min <?=$comparisons[$index]->status === 'PRE_MATCH' ? '-hover' : '' ?>"
              data-link = "<?=Html::encode($link)?>"

            >
            </div>
            <? $link = '/product/compare?id='.$product_id.'&source_id='.$source['source_id'].'&node='.($index+1).'&status='.Comparison::STATUS_MISMATCH.$current ?>
            <div
              class="slider__red_button -min <?=$comparisons[$index]->status === 'MISMATCH' ? '-hover' : '' ?>""
              data-link = "<?=Html::encode($link)?>"
            >

            </div>

            <? if (0): ?>
            <?= Html::a("", [
              'compare',
              'id' => $product_id,
              'node' => $index+1,
              'status' => Comparison::STATUS_MISMATCH,
              /*'return' => true,*/
            ], ['class' => 'slider__red_button','target' => '_blank']
            ) ?>
            <? endif; ?>

          </div>
          <a
            href="/product/view?id=<?=$product->id?>&source_id=<?=$source['source_id']?>&node=<?=$index + 1?>&comparisons=<?=$get_['filter-items__comparisons']?>&filter-items__profile=<?=$get_['filter-items__profile']?>"
            class="[ PAGE-N ]  slider__page-n <?= $page === $index ? '-current' : '' ?>"><?=$index + 1?><?//=$cnt?></a>

        </div>
    <? $cnt++; endforeach; ?>
</div>


