<?php

namespace common\models;

use backend\models\P_updated;
use backend\models\Settings__fields_extend_price;
use backend\models\Source;
use Yii;
use yii\BaseYii;
use yii\db\ActiveRecord;
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
 *
 */
class Product extends \yii\db\ActiveRecord{
    protected $_baseInfo = [];
    protected $_addInfo = [];
    protected $_source;
    protected $_source_id;

    public function getSource_id() {
        return $this->_source_id;
    }
    
    public function setSource_id($value) {
        $this->_source_id = $value;
    }

    public function getBaseInfo()
    {
        if (!$this->_baseInfo && $this->info) {
            $this->_baseInfo = $this->info;
        }
        return $this->_baseInfo;
    }
    
    public function setBaseInfo($base_info) {
        $this->_baseInfo = $base_info;
    }

    public function getAddInfo(){
        if (!$this->_addInfo) {
            $this->initAddInfo();
        }
        return $this->_addInfo;
    }

    public function getSource() {
        return $this->_source ?? $this->_source = Source::findOne(['table_1' => str_replace('common\models\\', '', strtolower(get_called_class()))]);
    }
    
    public function setSource($source) {
        $this->_source = $source;
    }



    /**
     * {@inheritdoc}
     */
  public static function tableName(){
        return '!!!!';
        //return '{{%parser_trademarkia_com}}';
    }

    /**
     * Служит для того чтобы загрузить для продукта дополнительную инфу
     * $this->_addinfo = new Product_right(.....)
     */
    private function initAddInfo() {
        $asin = $this->asin;

        $source = $this->source;
        $class_2 = $source->get_class_2(); // Parser_trademarkia_com_result
        $table_2 = $class_2::find()->where(['asin' => $asin])
                        ->orderBy(['parse_at' => SORT_ASC])
                        ->all() ?: [];

        foreach ($table_2 as $item) {
            $out = [];
            $res = [];

            // Копирует все ключи кроме results 
            foreach ($item as $k => $value) {     //Parser_trademarkia_com_result object
                if ($k === 'results')
                    continue;
                $out[$k] = $value;
            }

            // Если есть ключ results, то
            if (isset($item->results)) {
                $data = Json::decode($item->results, true) ?: [];
                //$data = Json::decode($item->results, true) ?: [];
                //$res = $this->get_all_elements_in_array_to_first_level($data,'>>>');
                $res = $this->get_all_elements_in_array_to_first_level($data, '.');
            }

            $pr = new Product_right(array_merge($out, $res, ['parent_item' => $this->baseInfo]));
            $this->_addInfo[] = $pr;
        }
    }

    private function get_all_elements_in_array_to_first_level($array, $separator = '_', $level_prefix = '') {
        $_tmp = [];
        $from_deep = [];

    if (!is_array($array)) return $array;
    foreach ($array as $k => $val){

            if (is_array($val)) {
                $from_deep = $this->get_all_elements_in_array_to_first_level($val, $separator, $k);
                $_tmp = array_merge($_tmp, $from_deep);
            } else {
                if ($level_prefix)
                    $key = $level_prefix . $separator . $k;
                else
                    $key = $k;

                $_tmp[$key] = $val;
            }
        }

        return array_merge($_tmp, $from_deep);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['categories', 'info', 'comparsion_info', 'results_all_all', 'results_1_1', 'images', 'images_url'], 'string'],
            [['date_add'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['asin'], 'string', 'max' => 15],
            [['item_url'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('site', 'ID'),
            'title' => Yii::t('site', 'Title'),
            'categories' => Yii::t('site', 'Categories'),
            'asin' => Yii::t('site', 'Asin'),
            'info' => Yii::t('site', 'Info'),
            'comparsion_info' => Yii::t('site', 'Comparsion Info'),
            'results_all_all' => Yii::t('site', 'Results All All'),
            'results_1_1' => Yii::t('site', 'Results 1 1'),
            'images' => Yii::t('site', 'Images'),
            'images_url' => Yii::t('site', 'Images Url'),
            'item_url' => Yii::t('site', 'Item Url'),
            'date_add' => Yii::t('site', 'Date Add'),
        ];
    }

    /**
     * Gets query for [[Comparison]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComparisons() {
        return $this->hasMany(Comparison::className(), ['product_id' => 'id'])
                        ->where(['source_id' => $this->source_id])
                        ->indexBy('node');
    }

    /**
     * Gets query for [[Aggregated]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAggregated() {
        return $this->hasOne(Comparison\Aggregated::className(), ['product_id' => 'id'])->where(['source_id' => $this->source_id]);
    }

    public function getUpdated() {
        return $this->hasOne(P_updated::class, ['p_id' => 'id'])->where(['source_id' => $this->source_id]);
    }

    public function getUser_visible(){
        return $this->hasOne(P_user_visible::class, ['p_id' => 'id']);
    }

    /**
     * <code>
     * ->get_right_items(['PRE_MATCH', 'MATCH', 'OTHER', 'MISMATCH', 'NOCOMPARE']); // 0
     *
     * ->get_right_items(['PRE_MATCH', 'MATCH', 'OTHER', 'MISMATCH']); // nocompare
     * ->get_right_items(['NOCOMPARE']); // result
     *
     * ->get_right_items(['PRE_MATCH', 'OTHER',  'NOCOMPARE', 'MISMATCH']); // match
     * ->get_right_items(['PRE_MATCH', 'MATCH', 'OTHER', 'NOCOMPARE']); // mismatch
     * ->get_right_items(['MATCH', 'OTHER', 'MISMATCH', 'NOCOMPARE']); // pre_match
     * ->get_right_items(['PRE_MATCH', 'MATCH', 'MISMATCH', 'NOCOMPARE']); // other
     *
     *
     * @param array $del_with_status
     * @return array
     */
  public function get_right_items($del_with_status = []){
//$filter = [Result, NOCOMPARE, PRE_MATCH, MATCH, OTHER, MISMATCH, YES_NO_OTHER, ALL, ALL_WITH_NOT_FOUND,];
        $right_products = $this->addInfo;

        $res = Comparison::find()
                ->where(['product_id' => $this->id])
                ->all();

        $node_to_status = [];
        foreach ($this->addInfo as $k => $item) {
            $status = false;

            foreach ($res as $r) {
                if ((int) $k === (int) $r->node) {
                    $status = $r->status;
                }
            }
            $status = $status ?: 'NOCOMPARE';

            $this->addInfo[$k]->set_status($status);

            $node_to_status[$k] = $status;
            /*
              [0] => PRE_MATCH
              [1] => MISMATCH
              [2] => NOCOMPARE
              [3] => NOCOMPARE
              [4] => NOCOMPARE
              [5] => NOCOMPARE
              [6] => MISMATCH
              [7] => NOCOMPARE
             * */
        }
        $out = [];
        $ignore_nodes = [];
// Result = YES_NO_OTHER
//$del_with_status = ['=' => ['PRE_MATCH', 'MATCH', 'OTHER', 'MISMATCH']]; // result
//$del_with_status = ['PRE_MATCH', 'MATCH', 'OTHER', 'MISMATCH']; // nocompare
        foreach ($del_with_status as $name => $status) {
            foreach ($node_to_status as $node_id => $status_p) {
                if ($status === $status_p)
                    $ignore_nodes[] = $node_id;
            }
        }

        foreach ($ignore_nodes as $n_id_del) {
            unset($right_products[$n_id_del]);
        }

        return $right_products;
    }

    public function get_img_main() {
        $imgs = $this->get_img_all() ?? false;
        return $imgs[0] ?? false;
    }

    public function get_img_all() {
        return explode(';', $this->baseInfo['Image']);
    }

    public function addition_info_for_price() {
        $source_id = $this->source_id;

        $keys = Settings__fields_extend_price::find()->where(['source_id' => $source_id])
                        ->orderBy(['default' => SORT_DESC])->all();

        $b = $this->baseInfo;

        $out = [];
        foreach ($keys as $item) {
            if ($item->title)
                $k = $item->title;
            else
                $k = $item->name;
            $out[$k] = $b[$k];
        }

        return json_encode($out);
    }

    public static function profiles_list($source_id) {

        $s = Source::get_source($source_id);
        
        if (!$s) {
            echo '<pre>'.PHP_EOL;
            print_r('Products::profiles_list() ... не найден source');
            echo PHP_EOL;
            exit;
        }
        $source_class = $s['source_class'];
        $q = $source_class::find()->distinct(true)->select(['profile'])->asArray();

        $profile_list['{{all}}'] = 'Все';
        foreach ($q->column() as $item){
//$item = strtolower($item);
            $e_items = explode(',', $item);
            foreach ($e_items as $e_item){
                $e_item = trim($e_item);
                $profile_list[$e_item] = $e_item;
            }
        }

        return $profile_list;
    }

}
