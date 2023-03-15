<?php

namespace common\models;

use backend\models\P_updated;
use common\models\Source;
use Yii;
use yii\helpers\Json;

class Parser_google extends Product
{
  protected $_baseInfo = [];
  protected $_addInfo = [];
  protected $_source_id = 3;

  public static $filters = [
    [
      'key' => 'Reviews: Rating',
      'name' => 'f_reviews',
      'label' => 'Reviews Rating',
      'type' => 'number',
      'range' => false,
      'json_column' => 'info',
    ],
    [
      'key' => 'Reviews: Review Count',
      'name' => 'f_review_count',
      'label' => 'Reviews Count',
      'type' => 'number',
      'range' => false,
      'json_column' => 'info',
    ],
    [
      'key' => 'Sales Rank: Current',
      'name' => 'f_bsr',
      'label' => 'BSR',
      'type' => 'number',
      'range' => false,
      'json_column' => 'info',
    ],
    [
      'key' => 'Sales Rank: Drops last 30 days',
      'name' => 'f_drops_30',
      'label' => 'Drops (30)',
      'type' => 'number',
      'range' => false,
      'json_column' => 'info',
    ],
    [
      'key' => 'Count of retrieved live offers: New, FBA',
      'name' => 'f_fba',
      'label' => 'FBA',
      'type' => 'number',
      'range' => true,
      'json_column' => 'info',
    ],
    [
      'key' => 'Count of retrieved live offers: New, FBM',
      'name' => 'f_fbm',
      'label' => 'FBM',
      'type' => 'number',
      'range' => true,
      'json_column' => 'info',
    ],
    [
      'key' => 'Brand',
      'name' => 'f_brand',
      'label' => 'Brand_R',
      'type' => 'text',
      'range' => false,
      'json_column' => 'info',
    ],
  ];

  public static function tableName()
  {
    return 'parser_google';
  }
}
