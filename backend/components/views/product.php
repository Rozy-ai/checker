<?php

use backend\components\ComparisonWidget;
use backend\components\ProductStringWidget;
use backend\models\Settings__fields_extend_price;
use common\helpers\AppHelper;
use common\models\Comparison;
use yii\helpers\Html;
use common\models\Source;


/**
 * @var $left array
 * @var $right array
 * @var $this yii\web\View
 * @var common\models\Comparison $comparison
 * @var $right_info
 * @var $product_id
 * @var $p_item
 * @var $arrows
 * @var $compare_item
 * @var $compare_items
 * @var Source $source
 * @var bool   $is_admin
 */

$get_ = $this->params['get_'];

$images_left = preg_split("/[; ]/", $left["Image"]);
$images_right = preg_split("/[; |,|\|]/", $right->srcKey);
?>


<!--  [ PRODUCT_VIEW ]  -->
<div class="tbl two_col [ COMPARE ]">

  <div class="rw lg-view__two-items-title">
    <div class="td">
      <!-- left title -->
      <p class="compare__left-item-path-url">
        <span style="color: #007bff;">
          <?= $left["Categories: Tree"] ?>
        </span>
      </p>

      <p class="compare__left-item-title">
        <a style="color: black" target="_blank" href="<?= $left['URL: Amazon'] ?>">
          <?= $left["Title"] ?>
        </a>

        <?
        $url = '';
        if ($source->name === 'EBAY') $url = $left['Ebay_search'];
        if ($source->name === 'CHINA') $url = $right['Url_Search_Ali'];
        ?>
        <? if ($url):?>
          <a href="<?=$url?>" target="_blank" class="compare__left-item-title-l-1 ">
            <svg
              style="height: 100%; width: 100%; top: -1px; position: relative; color: black;"
              xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
              <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
          </a>
        <? endif;?>
        <!--
        <a href="<?=$left['Ebay_search']?>" target="_blank" class="compare__left-item-title-l-2 bi bi-arrow-up-right-circle">

          <svg style="height: 100%; width: 100%;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-arrow-up-right-circle" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.854 10.803a.5.5 0 1 1-.708-.707L9.243 6H6.475a.5.5 0 1 1 0-1h3.975a.5.5 0 0 1 .5.5v3.975a.5.5 0 1 1-1 0V6.707l-4.096 4.096z"/>
          </svg>

        </a>
        -->
      </p>



      <!-- / left title -->
    </div>

    <div class="td">
      <!-- right title -->
      <p class="compare__right-item__title">
        <span style="color: #007bff;"
           class="<?//= $comparison->status !== null ? ($comparison->status === 'MATCH' ? ' match' : ($comparison->status === 'MISMATCH' ? ' mismatch' : ' other')) : '' ?>
                            right-item__link_1
                           "
        >
          <?= $right->urlKey ?>
        </span>
      </p>

      <? if (0): ?>
        <p class="text">
          <?= $right->r_Title ?>
        </p>
      <? endif; ?>



        <div class="custom-select-my">
          <select
            name="select-item-2"
            id="compare-table__select-item-2"
            style="width: 100%"
            class=""
            aria-labelledby="jobLabel"
          >
            <? foreach ($compare_items as $k => $r_item):?>
              <?
              $class = 'nocompare';
              switch ($model->comparisons [$k]->status):
                case Comparison::STATUS_MATCH:
                  $class = ' match';
                  break;
                case Comparison::STATUS_PRE_MATCH:
                  $class = ' pre_match';
                  break;
                case Comparison::STATUS_MISMATCH:
                  $class = ' mismatch';
                  break;
                case Comparison::STATUS_OTHER:
                  $class = ' other';
                  break;
              endswitch;
              ?>
              <? $index = (int)$k+1 ?>
              <option
                class="<?=$class?>"
                data-pid="<?=$product_id?>"
                data-nid="<?=$index?>"
                data-source_id="<?=$source->id?>"
                value="<?=$index?>"
                <?=((int)$node_idx === $index)? 'selected="true"' : '' ?>
              >#<?=$index?> | <?=$r_item->r_Title?></option>


            <? endforeach; ?>

          </select>
        </div>



      <? if (0): ?>
      <p class="title">
        <a target="_blank" href="<?= $right->urlKey ?>">
          <?= $right->Categories_tree ?>
        </a>
      </p>
      <? endif; ?>
      <!-- / right title -->
    </div>

  </div>



  <div class="tbl_row">

    <!-- LEFT ITEM -->
    <div class="js__root tbl_cell col col1 [ LEFT-ITEM ] compare__left-item">
      <div class="-wrapper">
        <div class="-wrapper_2 js__p-a-arrows-wrapper">

          <? if (0): ?>
            <a
              data-direction="prev"
              class="-hidden prev js__arrow prev-arr navigation-image-arr arrow left-img"
              title="Previous image - Item images"
            >
              <svg class="gallery-svg" viewBox="0 0 12 20" width="8" height="14" focusable="false" aria-hidden="true">
                <path d="M10.0002222 0L0 9.9997778 10.0002222 20l1.6142581-1.6142581-8.4370764-8.3859641 8.4370764-8.4375209" fill-rule="evenodd" fill="#545658"></path>
              </svg>
            </a>

  <!--
            <a data-direction="prev" class="-hidden prev prev-arr navigation-image-arr arrow left-img " title="Previous image - Item images">
              <svg class="gallery-svg" viewBox="0 0 12 20" width="8" height="14" focusable="false" aria-hidden="true">
                <path d="M10.0002222 0L0 9.9997778 10.0002222 20l1.6142581-1.6142581-8.4370764-8.3859641 8.4370764-8.4375209" fill-rule="evenodd" fill="#545658"></path>
              </svg>
            </a>
  -->

            <a
              data-direction="next"
              class="-hidden next js__arrow next-arr navigation-image-arr arrow left-img"
              title="Next image - Item images"
            >
              <svg class="gallery-svg" viewBox="0 0 12 20" width="8" height="14" focusable="false" aria-hidden="true">
                <path d="M1.5622222 0L0 1.5622222 8.3853333 10 0 18.3857778 1.5622222 20l10-10" fill-rule="evenodd" fill="#545658"></path>
              </svg>
            </a>

            <!--
            <button class="js__a-arrow-right -hidden next next-arr navigation-image-arr arrow left-img" title="Next image - Item images">
              <svg class="gallery-svg" viewBox="0 0 12 20" width="8" height="14" focusable="false" aria-hidden="true">
                <path d="M1.5622222 0L0 1.5622222 8.3853333 10 0 18.3857778 1.5622222 20l10-10" fill-rule="evenodd" fill="#545658"></path>
              </svg>
            </button>
            -->
          <? endif;?>

          <div class="sm-view__two-items-title">
            <p class="compare__left-item-path-url " >
              <span style="color: #007bff;">
                <?= $left["Categories: Tree"] ?>
              </span>
            </p>

            <p class="compare__left-item-title">
              <?= $left["Title"] ?>
            </p>
          </div>


          <!-- < -->
          <!--
          <div
            class="prev js__arrow slick-prev slick-arrow"
            data-direction="prev"
            data-arrow_ignore_checked="<?=$arrows['left']['ignore_checked']?>"
            data-arrow_ignore_dont_checked="<?=$arrows['left']['ignore_dont_checked']?>"
          ></div>
          -->
          <?//= Html::a("", ['view', 'id' => $this->params['prev']->id], ['class' => 'slick-prev slick-arrow']) ?>

          <div
            class="js__show-on-hover left-item__img_wrapper"
          >
            <!-- IMG -->
            <div class="left-item__img_wrapper-2">

              <a class="left-item__img _slider-for js__item-img"
                 <? if (0):?>
                 style="background-image: url('<?= $images_left[0] ?>')"
                 <? endif;?>
                 data-link="<?=$left['URL: Amazon']?>"
                 href="<?=$left['URL: Amazon']?>"
                 target="_blank"
              >

                <img id="zoom_01" class="left-item__img-tag" src="<?= $images_left[0] ?>" alt="">


              </a>


            </div>

          </div>


          <!-- > -->
          <?//= Html::a("", ['view', 'id' => $this->params['next']->id], ['class' => 'slick-next slick-arrow']) ?>




          <div
            style="width: 100%; max-width: 620px; overflow: hidden;"
            class="swiper-container [ L-I-SLIDER ] left-item__l-i-slider _slider-nav">

            <div class="swiper-wrapper" style="width: auto">

              <?php foreach ($images_left as $image): ?>
                <div class="swiper-slide item l-i-slider__item " style="width: auto">
                  <a href="#">
                    <img
                      style="width: auto; max-height: 81px;"
                      class="js__slider-img l-i-slider__img"
                      src="<?= $image ?>"
                      data-image-place="img-left-big">
                  </a>
                </div>
              <?php endforeach; ?>

            </div>

          </div>

        </div>
      </div>

      <? if ($canCompare): ?>
        <div class="control">
          <span
            <? if (0):?>
            href="/product/missall?id=<?=$product_id?>&source_id=<?=$source->id?>&return=1&"
            <? endif;?>
            data-url = "/product/missall"
            data-id = "<?=$product_id?>"
            data-url_next = ""
            href="#"
            class="left-item__img btn del js-missall"
          ></span>

          <?//= Html::a("", ['missall', 'id' => $product_id], ['class' => 'btn del']); ?>
        </div>
      <? endif; ?>

    </div>
    <!-- /LEFT ITEM -->


    <!-- RIGHT ITEM -->
    <div class="js__root tbl_cell col col2 [ RIGHT-ITEM ] compare__right-item ">
      <div class="-wrapper">
        <div class="-wrapper_2 js__p-r-arrows-wrapper">
          <? if ($compare_items): ?>

            <? if (0): ?>
              <button
                data-direction="prev"
                data-arrow_ignore_checked="<?//=$arrows['right']['ignore_checked']?>"
                data-arrow_ignore_dont_checked="<?//=$arrows['right']['ignore_dont_checked']?>"
                class="prev js__arrow_2 prev-arr navigation-image-arr arrow js__p-arrow-l "
                role="button" aria-label="Previous image - Item images" title="Previous image - Item images"
              >
                <svg class="gallery-svg" viewBox="0 0 12 20" width="8" height="14" focusable="false" aria-hidden="true">
                  <path d="M10.0002222 0L0 9.9997778 10.0002222 20l1.6142581-1.6142581-8.4370764-8.3859641 8.4370764-8.4375209" fill-rule="evenodd" fill="#545658"></path>
                </svg>
              </button>
              <button
                data-direction="next"
                data-arrow_ignore_checked="<?//=$arrows['right']['ignore_checked']?>"
                data-arrow_ignore_dont_checked="<?//=$arrows['right']['ignore_dont_checked']?>"
                class="next js__arrow_2 next-arr navigation-image-arr arrow js__p-arrow-r"
                role="button" aria-label="Next image - Item images" title="Next image - Item images"
              >
                <svg class="gallery-svg" viewBox="0 0 12 20" width="8" height="14" focusable="false" aria-hidden="true">
                  <path d="M1.5622222 0L0 1.5622222 8.3853333 10 0 18.3857778 1.5622222 20l10-10" fill-rule="evenodd" fill="#545658"></path>
                </svg>
              </button>
            <? endif; ?>


          <div class="sm-view__two-items-title">
            <p class="compare__right-item__title">
              <span style="color: #007bff;"
                 class="<?//= $comparison->status !== null ? ($comparison->status === 'MATCH' ? ' match' : ($comparison->status === 'MISMATCH' ? ' mismatch' : ' other')) : '' ?>
                        right-item__link_1
                       "
              >
                <?//= $right['URL: Ebay'] ?>
                <?= $right->urlKey ?>
              </span>
            </p>


            <div class="custom-select-my">
              <select
                name="select-item-2"
                id="compare-table__select-item-2"
                style="width: 100%"
                class=""
                aria-labelledby="jobLabel"
              >



                <? foreach ($compare_items as $k => $r_item):?>


                  <?
                  $class = 'nocompare';
                  switch ($model->comparisons [$k]->status):
                    case Comparison::STATUS_MATCH:
                      $class = ' match';
                      break;
                    case Comparison::STATUS_PRE_MATCH:
                      $class = ' pre_match';
                      break;
                    case Comparison::STATUS_MISMATCH:
                      $class = ' mismatch';
                      break;
                    case Comparison::STATUS_OTHER:
                      $class = ' other';
                      break;
                  endswitch;
                  ?>
                  <? $index = (int)$k+1 ?>
                  <option
                    class="<?=$class?>"
                    data-pid="<?=$product_id?>"
                    data-nid="<?=$index?>"
                    data-source_id="<?=$source->id?>"
                    value="<?=$index?>"
                    <?=((int)$node_idx === $index)? 'selected="true"' : '' ?>
                  >#<?=$index?> | <?=$r_item->r_Title?></option>


                <? endforeach; ?>

              </select>
            </div>


          </div>


            <div
              class="js__show-on-hover right-item__img_wrapper"
            >
              <div class="right-item__img_wrapper-2">

                <a  class="js__item-img right-item__img _slider-for2"
                    data-link="<?=$right->Url_table?>"
                    href="<?=$right->Url_table?>"
                    target="_blank"
                >
                  <img
                    id="zoom_02"
                    <? if (0):?>
                    data-zoom-image="<?= AppHelper::ebay_big($images_right[0]) ?>"
                    <? endif;?>
                    class="right-item__img-tag"
                    src="<?= $images_right[0] ?>"
                    alt="">

                </a>

              </div>
            </div>

            <div
              style="width: 100%; max-width: 620px; overflow: hidden;"
              class="swiper-container [ R-I-SLIDER ] right-item__r-i-slider ">

              <div class="swiper-wrapper" style="width: auto">


                <?php foreach ($images_right as $image): ?>
                  <div class="swiper-slide r-i-slider__item item" style="width: auto" >
                    <img
                      style="width: auto; max-height: 81px;"
                      class="js__slider-img r-i-slider__img"
                      src="<?= $image?>" />
                  </div>
                <?php endforeach; ?>


              </div>
            </div>



          <? else:?>
            нет информации для сравнения
          <? endif;?>

        </div>
      </div>

      <div class="compare-btn-wrapper tbl" style="width: 100%;">

        <div class="td" style="vertical-align: bottom; padding-right: 10px">
          <? if ($comparison->message && (int)$comparison->messages->settings__table_rows_id === -1): ?>
            <div class="[ MESSAGE ] right-item__message td">
              <?= ($comparison->status === 'OTHER')? $comparison->message : ''?>
              <div class="message__info_btn"> !
                <div class="message__img-message"><?= ($comparison->status === 'OTHER')? $comparison->messages->description : ''?>
                  <div class="message__img-message-arrow"></div>
                </div>
              </div>
            </div>
          <? endif; ?>
        </div>

        <div class="td" style="width: 208px; vertical-align: top;">
          <?= ComparisonWidget::widget([
            'comparison' => $comparison,
            'canCompare' => $canCompare,
            'product_id' => $product_id,
            'node_idx' => $node_idx,
            'source_id' => $source->id
          ]) ?>
        </div>
      </div>



    </div><!-- /RIGHT ITEM -->

  </div>
</div>


<div class="table-responsive">
<table class="tableInfo table my_table [ COMPARE-TABLE ] product-view__compare-table">

  <tbody>
    <?
      $E_Sales = $right_info[$node_idx - 1]->salesKey;
      $A_Sales = $left["Sales Rank: Drops last 30 days"];
    ?>


  <!-- CHANGE -->


    <?
    $table_rows = \backend\models\Settings__table_rows::find()->all();
    ?>

<!-- FOREACH PRODUCT TABLE -->

    <? if ($table_rows) foreach ($table_rows as $t_row): ?>

      <?php

      $options = [];
      $options['compare_items'] = $compare_items;
      $options['comparison'] = $comparison;
      $options['p_item'] = $p_item;
      $options['p_right'] = $right;
      $options['options']['id'] = $t_row->id;
      $options['options']['visible_for_user'] = $t_row->visible_for_user;

      $options['leftTitle'] = $t_row['title'];
      if ($t_row->item_1_key) $options['leftValue'] = $left[$t_row->item_1_key];
      if ($t_row->item_2_key) $options['rightValue'] = $right[$t_row->item_2_key];

      if ($t_row->title == 'EAN/UPC'){
        $options['leftValue'] = $left["Product Codes: EAN"] . '/' . $left["Product Codes: UPC"];
      }
      if ($t_row->title == 'Package (cm)'){
        $options['leftValue'] = $left["Package: Length (cm)"] . 'x' . $left["Package: Width (cm)"] . 'x' . $left["Package: Height (cm)"];
      }
      if ($t_row->title == 'Price'){
        $p_key = Settings__fields_extend_price::get_default_price($source->id)->name ?: 'Price Amazon' ;

        //$options['leftValue'] = '!!!!';
        $options['leftValue'] = $left[$p_key]?: '-';

        $options['middleVal'] = $right["table_price_middle"];

        $options['rightValue'] = (float)$right[$t_row->item_2_key];
      }
      if ($t_row->title == 'BSR (BSR 30)'){
        $options['middleVal'] = is_numeric($E_Sales) && is_numeric($A_Sales) ? $E_Sales - $A_Sales : 0;
        $options['leftValue'] = $left["Sales Rank: Current"] . " (" . $left["Sales Rank: 30 days avg."].')';
      }
      $options['source'] = $source;
      ?>


      <? if ((int)$t_row->visible):?>
        <? if ($p_item->user_visible || $t_row->visible_for_user || \Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) !== null):?>
          <?= ProductStringWidget::widget($options) ?>
        <? endif;?>
      <? endif;?>

    <? endforeach;?>
<!-- / FOREACH -->

  </tbody>
</table>

  <? if ($is_admin):?>
  <div
    data-pid="<?=$product_id?>"
    class="btn btn-primary [ USER-VISIBLE-FIELDS ] compare__user-visible-fields" style="margin-bottom: 10px;">
    <?= (!$p_item->user_visible)? 'Показать поля (*) пользователю' : 'Скрыть поля (*) для пользователя'?>
  </div>
  <? endif; ?>

</div>




