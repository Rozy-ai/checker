<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $leftTitle string */
/* @var $leftValue string */
/* @var $rightTitle string */
/* @var $rightValue string */
/* @var $comparison */
/* @var $compare_items */
/* @var $options */
/* @var $p_item */
/* @var $p_right */
/* @var $addition_info_for_price */
/* @var Source $source */

if(!isset($middleVal)) $middleVal = 0;

?>
  <tr class="my_tr">
      <td
        class="name <?=(strtolower($leftTitle) === 'price')? 'js-addition-info-for-price' : ''?>"
        <?php if (strtolower($leftTitle) === 'price'):?>
          data-addition_info_for_price='<?=$addition_info_for_price;?>'
        <?php endif;?>
      >
        <?php if ($leftTitle): ?><?= $leftTitle ?><?endif;?><?= ($options['visible_for_user'] ? '' : '*')?>

      </td>
      <td
        class="info <?=(strtolower($leftTitle) === 'price')? 'js-addition-info-for-price' : ''?>"
        <?php if (strtolower($leftTitle) === 'price'):?>
          data-addition_info_for_price='<?=$addition_info_for_price;?>'
        <?php endif;?>
      >


        <?php if ($leftValue !== null): ?>
        <?php $v1 = \backend\models\Helper::url_to_link($leftValue) ?>

        <?

          if ($leftTitle === 'EAN/UPC') {
            $v1['data'] = str_replace('/', ' / ', $v1['data']);
          }
        ?>
        <?= $v1['data'] // вывод!! ?>
        <?php else:?>
          —
        <?php endif;?>

        <?php if ($leftTitle === 'EAN/UPC'):?>
        <?
          $url = '';
          if ($source->name === 'EBAY') $url = $p_item->baseInfo['Ebay_url'];
          if ($source->name === 'CHINA') $url = $p_right['Url_Search_Ali'];
        ?>
          <?php if ($url) : ?>
          <a class="btn btn-outline-secondary" href="<?=$url?>" target="_blank"
             style="width: 25px; height: 25px; display: inline; padding: 3px 4px; ">
            <svg
              style="width: 15px; height: 15px; top: -1px; position: relative; color: black;"
              xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
              <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
          </a>
          <?php endif; ?>
        <?php endif;?>
        <?php if ($v1['cnt'] > 0):?>
        <span
          style="display: inline-block"
          data-original-title="Copy to clipboard"
          class="clipboard btn btn-outline-secondary btn-sm" data-clipboard-text="<?=$leftValue?>"></span>
        <?php endif; ?>



      </td>
      <td class="info2<?= is_numeric($middleVal) ? $middleVal > 0 ? ' plus' : ($middleVal < 0 ? " minus" : '') : ''?>">

        <?php if ($compare_items): ?>

          <?= \common\models\Message::compare_in_table((int)$options['id'],$leftValue,
            \common\models\Message::get_all_for_compare_in_table()
          ); ?>

        <?php endif;?>


        <?php if (0): ?>
          <!-- settings__table_rows_id -->
          <?php if ((int)$options['id'] === (int)$comparison->messages->settings__table_rows_id):?>
            <?//= $comparison->messages->id . ' | ' .$comparison->messages->description . ' | ' .$comparison->messages->settings__table_rows_id?>

            <?php if ($comparison->compare_table_fields($leftValue)): {
              if ($comparison->message): ?>
                <div class="[ MESSAGE ] right-item__message " style="margin-bottom: 10px">
                  <?= ($comparison->status === 'OTHER') ? $comparison->message : '' ?>
                  <div class="message__info_btn"> !
                    <div
                      class="message__img-message"><?= ($comparison->status === 'OTHER') ? $comparison->messages->description : '' ?>
                      <div class="message__img-message-arrow"></div>
                    </div>
                  </div>
                </div>
              <?php endif;
            } endif; ?>
          <?php endif;?>
        <?php endif;?>

        <?= is_numeric($middleVal) ? empty($middleVal) ? 0 : $middleVal : $middleVal?>

      </td>
      <td class="info3">
        <?php if ($compare_items): ?>

          <?php if ($rightValue !== null): ?>

            <?php $v2 = \backend\models\Helper::url_to_link($rightValue) ?>
            <?= $v2['data'] ?>
            <?//= $rightValue ?>
          <?php else:?>—<?php endif;?>

          <?php if ($v2['cnt'] > 0):?>
            <span
              style="display: inline-block"
              data-original-title="Copy to clipboard"
              class="clipboard btn btn-outline-secondary btn-sm" data-clipboard-text="<?=$rightValue?>"></span>
          <?php endif; ?>

        <?php endif; ?>
      </td>
  </tr>
