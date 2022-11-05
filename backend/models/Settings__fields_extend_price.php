<?php

namespace backend\models;


use common\models\Product;
use yii\db\ActiveRecord;
use common\models\Source;


class Settings__fields_extend_price extends ActiveRecord{

  public function rules(){
    return [
      [['id','source_id','name','title','default'],'trim'],
      [['name'],'required'],
    ];
  }


  public static function get_default_price(int $source_id){
    return self::find()->where(['default' => '1', 'source_id' => $source_id ])->limit(1)->one();
  }

  public static function get_html(Product $product, $source_id = false){

    if (!$source_id){
      $source = Source::get_source($source_id);
      $source_id = $source['source_id'];
    }

    $keys = Settings__fields_extend_price::find()->where(['source_id' => $source_id])
      ->all();

    $out = [];
    foreach ($keys as $k){
      $out[$k] = $product->$k;
    }

    return json_encode($out);
  }

}