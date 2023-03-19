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
class Parser_trademarkia_com extends Product
{
  protected $_baseInfo = [];
  protected $_addInfo = [];

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
      'range' => true,
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
      'key' => 'Brand_R',
      'name' => 'f_brand_r',
      'label' => 'Brand_R',
      'type' => 'text',
      'range' => false,
      'json_column' => 'info',
      'values' => [
        'available' => 'available',
        'brand' => 'brand',
      ]
    ],
    [
      'key' => 'Brand',
      'name' => 'f_brand',
      'label' => 'Brand',
      'type' => 'text',
      'range' => false,
      'json_column' => 'info',
    ],
  ];

  public static function tableName()
  {
    return '{{%parser_trademarkia_com}}';
  }
}
