<?php

namespace backend\models;


use yii\db\ActiveRecord;
//  public $id;
//  public $title;
//  public $item_1_key;
//  public $item_2_key;
//  public $visible_for_user;

class Settings__common_fields extends ActiveRecord{

  public function rules(){
    return [
      [['id','name','description'],'trim'],
      [['name'],'required'],
    ];
  }

}