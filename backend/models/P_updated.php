<?php

namespace backend\models;


class P_updated extends \yii\db\ActiveRecord{

  public static function date_updated($p_id,$source_id){

    $s = self::findOne(['p_id' => $p_id,'source_id' => $source_id]);
    if ($s){
      $s->date = date('Y-m-d H:i:s',time());
      $s->save();
    }else{
      $s = new self();
      $s->p_id = $p_id;
      $s->source_id = $source_id;
      $s->date = date('Y-m-d H:i:s',time());
      $s->insert();
    }

  }
}