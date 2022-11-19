<?php

namespace common\helpers;

use common\models\Source;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use common\models\Comparison;

class AddInfoHelper{
  public static function valueList($model, $index, $field){
    $source_id = Source::get_source()['source_id'];
    $urls = array_keys($model->addInfo);
    $stocks = ArrayHelper::getColumn($model->addInfo, $field);

    $html_stocks = '';
    $idx = 0;
    foreach ($stocks as $url => $stock):
      $idx ++;
      $options_anchor = ['class' => ''];
      $options = ['class' => 'list-inline-item'];
      if (isset ($model->comparisons [$idx - 1])):
        switch ($model->comparisons [$idx - 1]->status):
        case Comparison::STATUS_MATCH:
          $options ['class'] .= ' match';
          $options_anchor ['class'] .= ' text-light';
          break;
        case Comparison::STATUS_MISMATCH:
          $options ['class'] .= ' mismatch';
          $options_anchor ['class'] .= ' text-light';
          break;
        case Comparison::STATUS_OTHER:
          $options ['class'] .= ' other';
          $options_anchor ['class'] .= ' text-light';
          break;
        endswitch;
      endif;
      if ($url == $urls[$index]){
        $options ['class'] .= ' current';
        $options ['selected'] .= 'selected';
      }

      $options ['data-pid'] .= $model->id;
      $options ['data-nid'] .= $idx;
      $options ['data-source_id'] .= $source_id;


      $html_stocks .=
        Html::tag('option',
                    Html::a(
                              $stock ?: '&#160;',
                              ['view', 'id' => $model->id, 'node' => $idx,'source_id' =>$source_id],
                              $options_anchor
                            ),
                  $options);
    endforeach;

    $ul = Html::tag('select', $html_stocks, ['class' => 'addinfo-list list-unstyled', 'id' => 's_'.$field]);
    return $ul;
  }
}
