<?php

namespace backend\models;


use yii\db\ActiveRecord;

class Stats__import_export extends ActiveRecord{

  public function rules(){
    return [
      [
        ['id','type','file_name','comparison','cnt','raw','source_id','profile','created'],'safe'
      ],
    ];
  }

}