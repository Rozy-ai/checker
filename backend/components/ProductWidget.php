<?php

namespace backend\components;

use yii\base\Widget;
use yii\helpers\Html;
use common\helpers\AppHelper;

class ProductWidget extends Widget
{
    private const LEFT_ALLOWED = [
            "Image",
            "Title",
            "Cat_A_E",
            "Categories: Tree",
            "Sales Rank: Current",
            "Price Amazon",
            "Sales Rank: Drops last 30 days",
            "Brand",
            "trademarks.justia_url",
            "trademarks.justia_query",
            "trademarks.justia_count",
            "trademarks.justia_category",
            "trademarks.justia_owner",
            "Package: Weight (g)",
            "Package: Length (cm)",
            "Package: Width (cm)",
            "Package: Height (cm)",
            "Product Codes: PartNumber",
            "Model",
            "Color",
            "Product Codes: EAN",
            "Product Codes: UPC",
            "Manufacturer",
            "Reviews: Rating",
            "Reviews: Review Count",
            "Sales Rank: 30 days avg.",
            'ASIN',
            'URL: Amazon',
            "eBay_price",
            "Brand",
            "Brand_R",
            "trademarkia_url",
            "trademarks.justia_url",
            "trademarks.justia_owner",
            "Brand_date",
            "Sales30", // -
            "Sales30,$",  // -
            "Margin",
            "Profit30", //-
        ];
    private const RIGHT_ALLOWED = [
            "eBay_title",
            "E_Categories.Tree",
            "eBay_stock",
            "ebay maxPrice",
            "E_Sales",
            "E_Brand",
            "Brand_E",
            "E_weight",
            "E_package",
            "E_MPN",
            "E_Model",
            "E_color",
            "E_ratingS",
            "E_Feedb",
            "images_E",
            'E_Stock',
            'Margin',
            'ROI',
            'URL: Ebay'
    ];
    protected $_left = [];
    protected $_right = [];

    public $right_info = [];
    public $extended = false;
    public $comparison;
    public $canCompare;
    public $product_id;
    public $item_id;
    public $node_idx;
    public $p_item;
    public $arrows;
    public $model;
    public $compare_items;
    public $compare_item;
    public $source;
    public $is_admin;

  public function __construct($config = []){
    $this->_left = array_fill_keys(self::LEFT_ALLOWED, "");
    $this->_right = array_fill_keys(self::RIGHT_ALLOWED, "");

    parent::__construct($config);
  }
  public function get_LEFT_ALLOWED(){
    return $this::RIGHT_ALLOWED;
  }

  public function setLeft($values){
    $this->_left = $values;
//    $this->_left = AppHelper::replaceValues($this->_left, $values);
  }

  public function setRight($values){
    $this->_right = $values;
//    $this->_right = AppHelper::replaceValues($this->_right, $values);
  }

    public function run(){

      return $this->render('product', [
        'left' => $this->_left,
        'right' => $this->_right,
        'right_info' => $this->right_info, // array товаров для сравнения
        'extended' => $this->extended,
        'comparison' => $this->comparison,
        'canCompare' => $this->canCompare,
        'product_id' => $this->product_id,
        'item_id' => $this->item_id,
        'node_idx' => $this->node_idx,  // get node          
        'p_item' => $this->p_item,
        'arrows' => $this->arrows,
        'model' => $model,          
        'compare_item' => $this->compare_item,
        'compare_items' => $this->compare_items,
        'source' => $this->source,
        'is_admin' => $this->is_admin
      ]);
    }
}

