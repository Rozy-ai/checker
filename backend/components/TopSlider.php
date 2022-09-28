<?php
/**
 * Created by PhpStorm.
 * User: Professional
 * Date: 14.03.2022
 * Time: 14:11
 */

namespace backend\components;

use backend\models\Settings__source_fields;
use common\models\Comparison;
use common\models\Product;
use yii\base\Widget;

class TopSlider extends Widget
{
    private $_options = [

    ];

    public $page;
    public $product;
    public $options = [];
    public $hide_red = false;
    public $no_compare;
    public $compare_item;
    public $right_item_show;
    public $get_;
    public $is_filter_items;


    private function hide_red($items){
      $return = [];
      $pid =$this->product->id;
      $source_id = \backend\models\Source::get_source()['source_id'];

      $res = Comparison::find()->where(['product_id' => $this->product->id, 'status' => 'MISMATCH','source_id'=> $source_id])->all();

      $out = [];
      if ($res){
        foreach ($res as $r){
          $out[] = $r->node;
        }
      }

      $cnt = -1;
      foreach ($items as $idx => $item){
        $cnt++;
        if (in_array($cnt,$out)){
          continue;
        }

        $return[$idx] = $item;
      }

      return $return;

      //$hidden_items = '';
    }

    public function run(){

      $this->_options['srcKey'] = $srcKey = Settings__source_fields::name_for_source('srcKey');
      $this->_options['urlKey'] = Settings__source_fields::name_for_source('urlKey');
      $this->_options['class'] = '_sliderTop';
      $this->_options['gradeKey'] = Settings__source_fields::name_for_source('gradeKey');
      $this->_options['price'] = Settings__source_fields::name_for_source('price');
      //$this->_options['salesKey'] = 'E_Sales';
      $this->_options['salesKey'] = Settings__source_fields::name_for_source('salesKey');

//      [Find_PriceMin_Ali] => 1
//      [Find_PriceMax_Ali] => 14.47

      $this->_options['salesKey'] = 'E_Sales';


      if ($this->hide_red && $this->get_['filter-items__comparisons'] !== 'MISMATCH' && $this->get_['filter-items__comparisons'] !== 'ALL' && $this->get_['filter-items__comparisons'] !== 'ALL_WITH_NOT_FOUND')
        $items = $this->hide_red($this->product->getAddInfo());
      else
        $items = $this->product->getAddInfo();

      return $this->render('slider', [
        'items' => $items,
        'no_compare' => $this->no_compare,
        //'items' => $this->product->getAddInfo(),
        'comparisons' => $this->product->comparisons,
        'product' => $this->product,
        'page' => $this->page,
        'product_id' => $this->product->id,
        'options' => array_merge($this->_options, $this->options),
        'hide_red' => $this->hide_red,
        'compare_item' => $this->compare_item,
        'right_item_show' => $this->right_item_show,
        'is_filter_items' => $this->is_filter_items,
        'get_' => $this->get_,
        'source' => $source = \backend\models\Source::get_source(),

      ]);

    }
}
