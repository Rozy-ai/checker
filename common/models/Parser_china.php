<?php

namespace common\models;

use backend\models\P_updated;
use Yii;
use yii\helpers\Json;


class Parser_china extends Product{
  protected $_baseInfo = [];
  protected $_addInfo = [];

  public static function tableName(){
    return '{{%parser_china}}';
  }

}

