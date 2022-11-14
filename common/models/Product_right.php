<?php

namespace common\models;


use backend\models\Settings__source_fields;
use common\models\Source;



class Product_right extends \yii\base\DynamicModel{   
  /**
   * @var array|float|int|mixed|string|string[]
   */
  private $compare_status;
  public  Source $source;

  public function __construct(array $attributes = [], $config = []){
    parent::__construct($attributes, $config);

    //$this->source = Source::get_source();
  }
  
  public static function getById($id){
      return self::findOne(['id' => $id]);
  }
  

  private function calculate_step_1($str){
    $re = '/\(\((.+?)\)\)|\{\{(.+?)\}\}|(\[\[.+?\]\])|(.+?)/m';

    preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

    $out = [];
    foreach ($matches as $m){
      $v = $m[0];
      if (trim($v) === '') continue;

      if (strpos($v,'((') !== false){

        $out[] = $this->calculate_step_1(trim($v,'()'));

      }

      if (strpos($v,'{{') !== false){
        $v = str_replace('{{','',$v);
        $v = str_replace('}}','',$v);

        if ($this->hasAttribute($v)) {
          $out[] = parent::__get($v);
        }elseif($parent_item = (isset($this->get_('parent_item')[$v])) ? $this->get_('parent_item')[$v] : false){
          $out[] = $parent_item;
        }
      }
      else{
        $out[] = $v;
      }
    }

    /* убираем пустые поля */
    $out_ = [];
    foreach ($out as $v){
      if (is_string($v) && trim($v) === '') continue;
      else $out_[] = $v;
    }
    $out = $out_;


    return $out;
  }

  private function reset_array_keys($arr){
    $out = [];
    foreach ($arr as $value) {
      $out[] = $value;
    }
    return $out;
  }

  private function calculate_step_2(array $array){
    $convert_to_string = 0;

    if (count($array) == 1 && strpos($array[0],'[[') !== false){
      /*    [0] => Array (
                          [0] => [[+]]
                          )
      */
      return '';
    }
    //if ( !isset($array[2]) && !isset($array[1]) && isset($array[0]) ) return $array[0];

    $operator = $array[1];
    if ($operator === '[[.]]') {
      $convert_to_string = 1;
      $v_1 = $array[0]?: '';
      $v_2 = $array[2]?: '';
    }else{
      $v_1 = $array[0]?: 0;
      $v_2 = $array[2]?: 0;
    }


    if (is_array($v_1)) $v_1 = $this->calculate_step_2($v_1);
    if (is_array($v_2)) $v_2 = $this->calculate_step_2($v_2);

    $res = $v_1 . $v_2; //     if ($operator === '[[.]]')

    if ($operator === '[[+]]'){
      $res = (float)$v_1 + (float)$v_2;
    }
    if ($operator === '[[/]]'){
      $res = (float)$v_1 / (float)$v_2;
    }
    if ($operator === '[[*]]'){
      $res = (float)$v_1 * (float)$v_2;
    }
    if ($operator === '[[-]]'){
      $res = (float)$v_1 - (float)$v_2;
    }

    unset($array[1]);
    unset($array[2]);
    $array[0] = $res;
    $array = $this->reset_array_keys($array);

    return $res;
    /*
    if (count($array) > 2) {
      //$this->calculate_step_2($array);
    }
    */


  }



  public function get_($name){
    if ($this->hasAttribute($name)) return parent::__get($name);
    return '';
  }
  public function __get($name){
    //echo '<pre>'.PHP_EOL;

    $d = Settings__source_fields::data_for_source($name, $this->source->id);

    /*
    [settings__source_fields_id] => 2
    [settings__source_fields_settings__common_fields_id] => 1
    [settings__source_fields_source_id] => 2
    [settings__source_fields_type] => compare
    [settings__source_fields_name] => Title_Ali
    [settings__source_fields_field_action] => 0
    [settings__common_fields_id] => 1
    [settings__common_fields_name] => r_Title
    [settings__common_fields_description] => название главного товара
    */
    /*
    [settings__source_fields_id] => 6
    [settings__source_fields_settings__common_fields_id] => 2
    [settings__source_fields_source_id] => 3
    [settings__source_fields_type] => compare
    [settings__source_fields_name] => results.info.image_google
    [settings__source_fields_field_action] => 0
    [settings__common_fields_id] => 2
    [settings__common_fields_name] => srcKey
    [settings__common_fields_description] => ссылка на изображение правый товар
    */

    if ($name === 'srcKey'){
//      //echo '<pre>'.PHP_EOL;
//      print_r($this);
//      print_r($d);
//      //echo PHP_EOL;
//      exit;

    }


    if ($d['settings__source_fields_field_action'] == 'formula'){

      $step_1 = $this->calculate_step_1( $str = $d['settings__source_fields_name'] );
      return $this->calculate_step_2( $step_1 );

    }


    if ($d['settings__source_fields_field_action'] == 'replace') {

      $str = $d['settings__source_fields_name'];

      $re = '/\{\{(.+?)\}\}/m';
      preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

      foreach ($matches as $m){
        $v_ex = explode('::',$m[1]);
        $v_type = 'string';

        if (isset($v_ex[1])){
          $m[1] = $v_ex[0];
          if ($v_ex[1] === 'int') $v_type = 'int';
          if ($v_ex[1] === 'float') $v_type = 'float';
          if ($v_ex[1] === 'string') $v_type = 'string';
          if ($v_ex[1] === 'function') $v_type = 'function';
        }

        if (!$v = $this->get_($m[1])){
          //if ($v_type === 'int' || $v_type === 'float') $v = 0;

          $parent_item = $this->get_('parent_item');

          $v = $parent_item[$m[1]] ?: '';
          if ($v_type === 'int') $v = (int)$parent_item[$m[1]] ?: 0;
          if ($v_type === 'float') $v = (float)$parent_item[$m[1]] ?: 0;

        };
        if (!$v && $v_type === 'string' && isset($v_ex[2])){
          $v = $v_ex[2];

        }

        if ($v_type === 'function'){
          //
          $method_name = $v_ex[2];
          $v = $this->$method_name($v);
        }


                      // {{E_rating}}| |{{E_rating}} ({{E_ratingS}})
        $str = str_replace($m[0], $v, $str);
      }
      return $str;
    }

    $k = $d['settings__source_fields_name'];


    if ($this->hasAttribute($d['settings__source_fields_name']) && $d['settings__source_fields_name']) return $p_name = parent::__get($d['settings__source_fields_name']);
    else {

      if (isset($this->get_('parent_item')[$k]) && $k) {
        return $this->get_('parent_item')[$k];
      }

      return ($this->hasAttribute($name)) ? parent::__get($name) : '';
    }
  }

  public function get_first_image(){
    return $image_right = preg_split("/[; |,|\|]/", $this->srcKey)[0];
  }

  private function parse_ebay_item_id($data){
    // https://www.ebay.com/itm/132989098597

    $arr = explode('/', $data);

    if ($arr){
      $last_id = count($arr)-1;
      return $arr[$last_id];
    }
    return $data;
  }

  public function get_status(){
    return $this->compare_status;
  }
  public function set_status($status){
    $this->compare_status = $status;
    return $this;
  }
  public function __set($name, $value){
    $this->_attributes[$name] = $value;

    //$this->defineAttribute($name, $value);
    //parent::__set($name, $value);
  }


  public function parse_ebay_e_categories_tree($data){
    if (!is_array($data)) $data = json_decode($data);
    return str_replace('|',' > ',$data[0]);
  }

}