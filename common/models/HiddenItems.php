<?php

namespace common\models;

use yii\db\ActiveRecord;

class HiddenItems extends ActiveRecord{

  public static function tableName(){
    return 'hidden_items';
  }




}