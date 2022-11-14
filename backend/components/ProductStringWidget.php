<?php

namespace backend\components;

use backend\models\Settings__fields_extend_price;
use yii\base\Widget;
use yii\helpers\Html;

class ProductStringWidget extends Widget
{
    public $leftTitle = false;
    public $leftValue = null;
    public $rightTitle = false;
    public $rightValue = null;
    public $middleVal = "";
    public $options = [];
    public $comparison;
    public $compare_items;
    public $p_item;
    public $p_right;
    public $source;

    public function run(){

      if (strtolower($this->leftTitle) === 'price') {
        $addition_info_for_price = $this->p_item->addition_info_for_price();
      } else $addition_info_for_price = '';

      // если значенияпустые то не выводить
      if (!$this->leftValue && !$this->leftValue) return false;

      return $this->render('product-string', [
        'leftTitle' => $this->leftTitle,
        'leftValue' => $this->leftValue,
        'middleVal' => $this->middleVal,
        'rightTitle' => $this->rightTitle,
        'rightValue' => $this->rightValue,
        'options' => $this->options,
        'comparison' => $this->comparison,
        'compare_items' => $this->compare_items,
        'p_item' => $this->p_item,
        'p_right' => $this->p_right,
        'addition_info_for_price' => $addition_info_for_price,
        'source' => $this->source
      ]);
    }
}
