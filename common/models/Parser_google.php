<?php

namespace common\models;

use backend\models\P_updated;
use backend\models\Source;
use Yii;
use yii\helpers\Json;


class Parser_google extends Product{
  protected $_baseInfo = [];
  protected $_addInfo = [];
  protected $_source_id = 3;


  public static function tableName(){
    return 'parser_google';
  }

}

