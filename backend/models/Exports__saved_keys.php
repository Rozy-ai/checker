<?php

namespace backend\models;


class Exports__saved_keys extends \yii\db\ActiveRecord{

  public function rules()
  {
    return [
        [
          ['id','name','source_id','type','selected','position'],'safe'
        ],
    ];
  }

}