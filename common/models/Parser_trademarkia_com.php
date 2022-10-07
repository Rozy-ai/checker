<?php

namespace common\models;

use backend\models\P_updated;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%parser_trademarkia_com}}".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $categories
 * @property string $asin
 * @property string|null $info
 * @property string|null $comparsion_info
 * @property string|null $results_all_all
 * @property string|null $results_1_1
 * @property string|null $images
 * @property string|null $images_url
 * @property string|null $item_url
 * @property string|null $date_add
 * @property string|null $statuses
 */
class Parser_trademarkia_com extends Product{
  protected $_baseInfo = [];
  protected $_addInfo = [];

  public static function tableName(){
    return '{{%parser_trademarkia_com}}';
  }


}

