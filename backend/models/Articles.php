<?php

namespace backend\models;

use yii\db\ActiveRecord;

class Articles extends ActiveRecord{

  public function rules(){
    return [
      [['id','title','html','date'],'trim'],
    ];
  }

}