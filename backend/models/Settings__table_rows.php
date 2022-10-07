<?php

namespace backend\models;


use yii\db\ActiveRecord;
//  public $id;
//  public $title;
//  public $item_1_key;
//  public $item_2_key;
//  public $visible_for_user;

class Settings__table_rows extends ActiveRecord{

  public function rules(){
    return [
      [['id','title','item_1_key','item_2_key','visible_for_user','visible'],'trim'],
      [['visible_for_user'],'required'],
    ];
  }

}