<?php

namespace common\models;

use backend\models\P_updated;
use Yii;
use yii\helpers\Json;


class Parser_china extends Product{
  protected $_baseInfo = [];
  protected $_addInfo = [];

  public static $filters = [
    'Reviews: Rating' => [
      'name' => 'f_reviews',
      'label' => 'Reviews Rating',
      'type' => 'number',
      'range' => false,
      'json_column' => 'info',
    ],
    'Reviews: Review Count' => [
      'name' => 'f_review_count',
      'label' => 'Reviews Count',
      'type' => 'number',
      'range' => false,
      'json_column' => 'info',
    ],
    'Sales Rank: Current' => [
      'name' => 'f_bsr',
      'label' => 'BSR',
      'type' => 'number',
      'range' => false,
      'json_column' => 'info',
    ],
    'Sales Rank: Drops last 30 days' => [
      'name' => 'f_drops_30',
      'label' => 'Drops (30)',
      'type' => 'number',
      'range' => false,
      'json_column' => 'info',
    ],
    'Count of retrieved live offers: New, FBA' => [
      'name' => 'f_fba',
      'label' => 'FBA',
      'type' => 'number',
      'range' => true,
      'json_column' => 'info',
    ],
    'Count of retrieved live offers: New, FBM' => [
      'name' => 'f_fbm',
      'label' => 'FBM',
      'type' => 'number',
      'range' => true,
      'json_column' => 'info',
    ],
    'Brand' => [
      'name' => 'f_brand',
      'label' => 'Brand_R',
      'type' => 'text',
      'range' => false,
      'json_column' => 'info',
    ],
];

  public static function tableName(){
    return '{{%parser_china}}';
  }

}

