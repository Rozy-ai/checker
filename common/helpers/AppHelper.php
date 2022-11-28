<?php

namespace common\helpers;

use common\models\Source;
use common\models\Product_right;

class AppHelper{
  public static function replaceValues($defaults, $values){


    return array_replace($defaults, array_intersect_key($values, $defaults));
  }

  public static function get_item_by_number_key($array,$n){
    $cnt = 1;
    foreach ($array as $item){
      if ($cnt === (int)$n) {
        //$item->setAttributeLabel('node_id', $cnt);
        //$item['node_id'] = $cnt;
        return $item;
      }
      $cnt++;
    }
    return false;
  }

  public static function get_next_item_by_key($array, $n){
    $out = [];

    foreach ($array as $k => $item){
      /* @var $item Product_right */
      if ($k > (int)$n) {
        //$item->setAttributeLabel('node_id', $k);
        //$item['node_id'] = $k;
        //$item->node_id = $k;
        return $item;
      }
    }
    return false;
  }

  public static function plus_1_to_keys($array){
    $out = [];
    if (is_array($array))
    foreach ($array as $k => $a){
      $out[$k + 1] = $a;
    }
    return $out;
  }

  public static function ebay_big(string $str){
    // /s-l1600
    $out = '';
    if (Source::get_source()['source_name'] === 'EBAY'){
      $out = str_replace('/s-l300','/s-l1600',$str);
    };

    // https://i.ebayimg.com/images/g/6cwAAOSwakdhG8ZH/s-l300.png
    //echo '<pre>'.PHP_EOL;
//    print_r($str);
    //echo PHP_EOL;
//    exit;

    //$str
    //return $str;
    return $out;
  }

}
