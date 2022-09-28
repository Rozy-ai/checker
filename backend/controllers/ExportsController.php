<?php
namespace backend\controllers;
use backend\models\Exports__saved_keys;
use backend\models\Source;
use backend\models\Stats__import_export;
use common\models\Comparison;
use common\models\Product;
use Yii;
use yii\db\ActiveRecord;
use yii\web\Controller;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class ExportsController extends Controller{
  public $source_id;
  /**
   * @var Product
   */
  public $source_class;
  public $source_table_name;
  public $source_table_name_2;

  public function actionIndex(){


    // вывести таблицу список источников
    // [X] ebay   [ ] china   [ ] google
    // ---------------------------------
    // [X] match  [ ] mismatch  [ ] other  [ ] nocompare
    // [Далее→]   /exports/step_2?source_id=1&compare=match

      //  /exports/step_2


    return $this->render('index', [
      'dataProvider' => [],
    ]);

  }

  public function actionStep_2(){
    $data = $this->request->post('DynamicModel');
    $source_id = $data['source_id'];
    $comparisons = $data['comparisons'];
    $this->get_source($source_id);

    $q = $this->source_class::find()->distinct(true)->select(['profile'])->asArray();

    $profiles_list = Product::profiles_list($source_id);

    return $this->render('exports_step_2', [
      'data' => $data,
      'source_id' => $source_id,
      'profiles_list' => $profiles_list,
    ]);
  }



  public function actionStep_3(){

    $data = $this->request->post('DynamicModel');
    $ignore_step_3 = $data['ignore_step_3'];
    $source_id = $data['source_id'];
    $profile = $data['profile'];

    $comparisons = $data['comparisons'];
    if (!$source_id || !$comparisons) {
      echo '<pre>'.PHP_EOL;
      print_r('нет source_id или comparisons');
      echo PHP_EOL;
      exit;
    }

    $this->get_source($source_id);


    //     [DynamicModel] => Array
    //        (
    //            [source_id] => 1
    //            [comparisons] => Array
    //                (
    //                    [0] => match
    //                )
    //
    //        )

    // todo:: вытягиваем сохраненные ранее для этого источника ключи

    // таблица ключей
    $amazon_keys = [];
    $source_data_keys = [];
    $table_items = [];


    $q_ = Exports__saved_keys::find()
      ->where(['source_id' => $source_id])
      ->orderBy(['position' => SORT_ASC])
    ;
    $res = $q_->all();

    // [use_previous_saved] => 0
    if (!$data['use_previous_saved'] || !$res) { // создаем заново

      $q = $this->source_class::find()
        //->select('*')
        ->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
        ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
        ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
        ->leftJoin('p_updated','p_updated.p_id = '.$this->source_table_name.'.id ')
        ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
        ->leftJoin('messages','messages.id = comparisons.messages_id')

      //      ->where([$this->source_table_name.'.ASIN' => 'B0012NGQS4'])
      ;
      $q->limit(1000);
      $res = $q->all();

      if (!$res) {echo '<pre>'.PHP_EOL;print_r('-------');echo PHP_EOL;exit;}

      foreach ($res as $k => $item){
        $getBaseInfo = $item->getBaseInfo() ?? [];
        $amazon_keys = array_unique(array_merge(array_keys($getBaseInfo),$amazon_keys));
        $source_data_keys = array_unique(array_merge(array_values($item->getAddInfo() ? $item->getAddInfo()[0]->attributes() : []),$source_data_keys));
      }

      $amazon_keys = $this->format_keys($amazon_keys,$source_id,'left_item');
      $source_data_keys = $this->format_keys($source_data_keys,$source_id,'right_item');

      $table_items = array_merge($amazon_keys,$source_data_keys);

      Exports__saved_keys::deleteAll(['source_id' => $source_id]);

      foreach ($table_items as $k => $t_item) {

        if ($t_item['name'] !== 'parent_item'){
          $e_insert = new Exports__saved_keys();
          $e_insert->name = $t_item['name'];
          $e_insert->type = $t_item['type'];
          $e_insert->selected = $t_item['selected'];
          $e_insert->source_id = $t_item['source_id'];
          $e_insert->position = $k;
          $e_insert->insert();
        }
      }

/*    [id] => 1
      [name] => url_ebay
      [source_id] => 1
      [type] => left_item
      [selected] => 0
      [position] => 0
*/

      $q_ = Exports__saved_keys::find()
        ->where(['source_id' => $source_id])
        ->orderBy(['position' => SORT_ASC])
      ;
      $res = $q_->all();

    } // if (!$data['use_previous_saved'])

    $table_items_out = [];
    if ($res){
      foreach ($res as $k => $db_item){
        foreach ($db_item as $db_k => $db_value)
          $table_items_out[$k][$db_k] = $db_value;
      }
    }

    return $this->render('exports_step_3_compare_table', [
//      'amazon_keys' => $amazon_keys,
//      'source_data_keys' => $source_data_keys,
      'table_items' => $table_items_out,
      'comparisons' => $comparisons,
      'source_id' => $source_id,
      'profile' => $profile,
      'ignore_step_3' => $ignore_step_3,
    ]);

    // [Экспортировать→]

  }

  public function actionSelect_one(){
    $source_id = $this->request->post('source_id');
    $checked = $this->request->post('checked');
    $id = $this->request->post('id');


    $e = Exports__saved_keys::findOne(['id' => $id]);
    $e->selected = $checked;
    $e->update();

    // json
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
      'res' => 'ok',
    ];
  }

  public function actionSelect_all(){
    $source_id = $this->request->post('source_id');
    $checked = $this->request->post('checked');

    Exports__saved_keys::updateAll(['source_id' => $source_id,'selected' => $checked], 'source_id = '.$source_id);

    // json
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
      'res' => 'ok',
    ];
  }

  public function actionChange_position(){
    $data = $this->request->post('id_position');

    if ($data)
    foreach ($data as $item){
      $e = Exports__saved_keys::findOne(['id' => $item['id']]);
      $e->position = $item['position'];
      $e->save();
    }

    // json
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
      'res' => 'ok',
    ];
  }


  /**
   * @param $comparison
   * @return \yii\db\ActiveQuery
   */
  private function prepare_record_1($comparison){

    /* @var $source_class yii\db\ActiveRecord */
    $source_class = $this->source_class;

    $q = $source_class::find()
      ->select('*')
      ->addSelect($this->source_table_name.'.id as id')
      //->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
      ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
      ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
      ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ');

    //->where(['<=>','hidden_items.source_id', $this->source_id])
    //->where('comparisons_aggregated.source_id = '.(int)$this->source_id );


    if (0 && $this->source_table_name === 'parser_trademarkia_com') {
      $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
      $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
    }else{
      //$q->innerJoin($this->source_table_name_2,$this->source_table_name_2.'.`asin` = '.$this->source_table_name.'.asin');
    }

    //$q->addGroupBy('`'.$this->source_table_name.'`.`id`');


    $where_0 = [];
    if (0 && $this->source_table_name === 'parser_trademarkia_com') {
      $where_0 = ['like','info','add_info'];
    }

    $where_2 = [];
    $where_3 = ['and',
      ['hidden_items.p_id' => null],
      ['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]],
    ];  // $item_1__ignore_red = 1


    if ($comparison === 'ALL'){

    }elseif($comparison === 'ALL_WITH_NOT_FOUND'){

      $where_3 = [];

    }elseif($comparison === 'YES_NO_OTHER'){
      /*
      $where_2 =
      [ 'OR' ,
        ['`comparisons`.`status`' => 'MATCH'],
        ['`comparisons`.`status`' => 'MISMATCH'],
        ['`comparisons`.`status`' => 'OTHER'],
        ['`comparisons`.`status`' => 'PRE_MATCH'],
      ];
      */
      $where_2 = ['and', "`comparisons`.`status` IS NOT NULL AND comparisons.`status` <> 'MISMATCH'"];

    }elseif ($comparison === 'NOCOMPARE'){
      $where_2 = ['and',['p_all_compare.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]];
    }else{
      $where_2 = [ '`comparisons`.`status`' => $comparison ];
    }

    $where = ['and',  $where_0, $where_2, $where_3];
    $q ->andWhere($where);

    return $q;
  }




  public function actionStep_4(){
    // http://checker.loc/exports/step_4?source_id=2&comparisons=match&profile={{all}}

    $method = 'POST';
    /*
Array (   [0] => Array (
                    [id] => 119
                    [checked] => 0
                    [name] => Locale
                    [type] => left_item
        ) [1] => Array (
                    [id] => 123
                    [checked] => 1
                    [name] => Title
                    [type] => left_item
        ) ... */
    $ids_keys = $this->request->post('items');
    $source_id = $this->request->post('source_id');
    $comparison = $this->request->post('comparisons');
    $profile = $this->request->post('profile');



    if (!$ids_keys || !$source_id || !$comparison || !$profile) {
      // http://checker.loc/exports/step_4?source_id=2&comparisons=match&profile={{all}}

      $source_id = $this->request->get('source_id');
      $comparison = serialize($this->request->get('comparisons'));
      $profile = $this->request->get('profile');
      $ids_keys = $this->get_keys_from_db($source_id);
      $method = 'GET';
   }
    $comparison = strtoupper(unserialize($comparison));


    if ($comparison === 'NOCOMPARE' || $comparison === 'ALL'){
      echo '<pre>'.PHP_EOL;
      print_r($comparison.' не поддерживается, выберите другой вариант');
      echo PHP_EOL;
      exit;
    }

    if (!$ids_keys) {
      echo '<pre>'.PHP_EOL;
      print_r('нет $ids_keys');
      echo PHP_EOL;
      exit;
    }

    if (!$source_id || !$comparison) {
      echo '<pre>'.PHP_EOL;
      print_r('нет source_id или comparisons');
      echo PHP_EOL;
      exit;
    }

    $this->get_source($source_id);

/*
    $q = $this->source_class::find()
      ->select('*')
      //->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
      ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
    ;
    if ($comparison === 'YES_NO_OTHER'){
      $where_2 = ['and', "`comparisons`.`status` IS NOT NULL AND comparisons.`status` <> 'MISMATCH' AND '`comparisons`.`source_id`' = '$source_id'"];
    }else{
      $q->where(['comparisons.status' => $comparison, 'comparisons.source_id' => $source_id]);
    }
*/
    $q = $this->prepare_record_1($comparison);

    if (trim($profile) && $profile !== '{{all}}'){
      $q->andWhere( ['like', $this->source_table_name.'.`profile`' , $profile]);
    }

    $q->asArray();

    $connection = Yii::$app->getDb();
    $command = $connection->createCommand($q->createCommand()->getRawSql());

    $res = $command->queryAll();



    /*
            [id] => 6528
            [title] =>
            [categories] =>
            [asin] => B00MJ8PYNY
            [info] => {"Price Amazon":"28.75","ASIN":"B00MJ8PYNY","URL_Amazon":"https:\/\/www.amazon.com\/dp\/B00MJ8PYNY","URL_Ebay":"https:\/\/www.ebay.com\/itm\/401916642082 \nhttps:\/\/www.ebay.com\/itm\/133723868640","trademark_url":"https:\/\/www.trademarkia.com\/trademarks-search.aspx?tn=Flagdom","A+E_url":"https:\/\/www.amazon.com\/dp\/B00MJ8PYNY \nhttps:\/\/www.ebay.com\/itm\/232417843877 \nhttps:\/\/www.ebay.com\/itm\/322603238720 \nhttps:\/\/www.ebay.com\/itm\/303250724684 \nhttps:\/\/www.ebay.com\/itm\/303223274509 \nhttps:\/\/www.ebay.com\/itm\/323682264275 \nhttps:\/\/www.ebay.com\/itm\/231067300269 \nhttps:\/\/www.ebay.com\/itm\/401918694801 \nhttps:\/\/www.ebay.com\/itm\/303623778949 \nhttps:\/\/www.ebay.com\/itm\/401922795144 \nhttps:\/\/www.ebay.com\/itm\/401909910685 \nhttps:\/\/www.ebay.com\/itm\/323682264252 \nhttps:\/\/www.ebay.com\/itm\/402815276240 \nhttps:\/\/www.ebay.com\/itm\/402268940174 \nhttps:\/\/www.ebay.com\/itm\/303213786806 \nhttps:\/\/www.ebay.com\/itm\/303623708596 \nhttps:\/\/www.ebay.com\/itm\/303222006670 \nhttps:\/\/www.ebay.com\/itm\/303223284222 \nhttps:\/\/www.ebay.com\/itm\/401902602139 \nhttps:\/\/www.ebay.com\/itm\/402814031311 \nhttps:\/\/www.ebay.com\/itm\/303238803697 \nhttps:\/\/www.ebay.com\/itm\/303222003123 \nhttps:\/\/www.ebay.com\/itm\/401916642082 \nhttps:\/\/www.ebay.com\/itm\/232417843877 \nhttps:\/\/www.ebay.com\/itm\/322603238720 \nhttps:\/\/www.ebay.com\/itm\/303623778949 \nhttps:\/\/www.ebay.com\/itm\/303250724684 \nhttps:\/\/www.ebay.com\/itm\/402813853784 \nhttps:\/\/www.ebay.com\/itm\/401902602139 \nhttps:\/\/www.ebay.com\/itm\/303223274509 \nhttps:\/\/www.ebay.com\/itm\/401909910685 \nhttps:\/\/www.ebay.com\/itm\/403061079558 \nhttps:\/\/www.ebay.com\/itm\/402268943485 \nhttps:\/\/www.ebay.com\/itm\/402268940174 \nhttps:\/\/www.ebay.com\/itm\/321221667982 \nhttps:\/\/www.ebay.com\/itm\/401922795144 \nhttps:\/\/www.ebay.com\/itm\/232417848418 \nhttps:\/\/www.ebay.com\/itm\/303702658954 \nhttps:\/\/www.ebay.com\/itm\/401916620546 \nhttps:\/\/www.ebay.com\/itm\/231067299911 \nhttps:\/\/www.ebay.com\/itm\/274765605099 \nhttps:\/\/www.ebay.com\/itm\/360756873322 \nhttps:\/\/www.ebay.com\/itm\/303223284222 \nhttps:\/\/www.ebay.com\/itm\/133723868640","Ebay_search":"https:\/\/www.ebay.com\/sch\/i.html?_nkw=Tire+Sale+Swooper+Feather+Flag+Only \nhttps:\/\/www.ebay.com\/sch\/i.html?_nkw=Tire+Sale+Swooper+Feather+Flag+Only","eBay_stock":"10\/10\/4*\/4*\/10*\/10*\/9*\/10*\/8*\/9*\/10*\/3*\/8*\/7*\/9*\/6*\/10*\/7*\/10*\/10*\/10*\/10*\/10\/10\/10*\/4*\/8*\/7*\/4*\/8*\/5*\/9*\/10*\/10*\/8*\/10*\/10*\/9*\/10*\/10*\/10*\/9*\/19*","E_stock":"\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/","URL: Ebay":"https:\/\/www.ebay.com\/itm\/232417843877 \nhttps:\/\/www.ebay.com\/itm\/322603238720 \nhttps:\/\/www.ebay.com\/itm\/303250724684 \nhttps:\/\/www.ebay.com\/itm\/303223274509 \nhttps:\/\/www.ebay.com\/itm\/323682264275 \nhttps:\/\/www.ebay.com\/itm\/231067300269 \nhttps:\/\/www.ebay.com\/itm\/401918694801 \nhttps:\/\/www.ebay.com\/itm\/303623778949 \nhttps:\/\/www.ebay.com\/itm\/401922795144 \nhttps:\/\/www.ebay.com\/itm\/401909910685 \nhttps:\/\/www.ebay.com\/itm\/323682264252 \nhttps:\/\/www.ebay.com\/itm\/402815276240 \nhttps:\/\/www.ebay.com\/itm\/402268940174 \nhttps:\/\/www.ebay.com\/itm\/303213786806 \nhttps:\/\/www.ebay.com\/itm\/303623708596 \nhttps:\/\/www.ebay.com\/itm\/303222006670 \nhttps:\/\/www.ebay.com\/itm\/303223284222 \nhttps:\/\/www.ebay.com\/itm\/401902602139 \nhttps:\/\/www.ebay.com\/itm\/402814031311 \nhttps:\/\/www.ebay.com\/itm\/303238803697 \nhttps:\/\/www.ebay.com\/itm\/303222003123 \nhttps:\/\/www.ebay.com\/itm\/401916642082 \nhttps:\/\/www.ebay.com\/itm\/232417843877 \nhttps:\/\/www.ebay.com\/itm\/322603238720 \nhttps:\/\/www.ebay.com\/itm\/303623778949 \nhttps:\/\/www.ebay.com\/itm\/303250724684 \nhttps:\/\/www.ebay.com\/itm\/402813853784 \nhttps:\/\/www.ebay.com\/itm\/401902602139 \nhttps:\/\/www.ebay.com\/itm\/303223274509 \nhttps:\/\/www.ebay.com\/itm\/401909910685 \nhttps:\/\/www.ebay.com\/itm\/403061079558 \nhttps:\/\/www.ebay.com\/itm\/402268943485 \nhttps:\/\/www.ebay.com\/itm\/402268940174 \nhttps:\/\/www.ebay.com\/itm\/321221667982 \nhttps:\/\/www.ebay.com\/itm\/401922795144 \nhttps:\/\/www.ebay.com\/itm\/232417848418 \nhttps:\/\/www.ebay.com\/itm\/303702658954 \nhttps:\/\/www.ebay.com\/itm\/401916620546 \nhttps:\/\/www.ebay.com\/itm\/231067299911 \nhttps:\/\/www.ebay.com\/itm\/274765605099 \nhttps:\/\/www.ebay.com\/itm\/360756873322 \nhttps:\/\/www.ebay.com\/itm\/303223284222 \nhttps:\/\/www.ebay.com\/itm\/133723868640","Margin":6,"ROI":36,"Ebay_count":58,"Ebay_check":57,"Ebay_find":7,"%Ebay_check":0,"Ebay_noncheck":[],"E_noncheck#":"","E_noncheck+":"","Pack":1,"eBay_title":"Nails Swooper Flag Advertising Feather Flag Nail Salon-New","eBay_price":"17.95\/17.95","E_count":"","E_check":"","E_find":"","E_countG":"","E_checkG":"","E_findG":"","E_count#":"","E_check#":"","E_find#":"","E_count+":58,"E_check+":57,"E_find+":7,"Ebay_item":"232417843877\/322603238720\/151074011851\/174272708274\/133822676157\/303250724684\/303223274509\/323682264275\/303223275194\/231067300269\/401918694801\/303623778949\/401922795144\/401909910685\/323682264252\/402815276240\/402268940174\/161939163395\/303213786806\/303623708596\/303222006670\/303223284222\/401902602139\/402814031311\/303238803697\/303222003123\/401916642082\/232417843877\/322603238720\/151074011851\/133822676157\/174272708274\/303623778949\/303250724684\/402813853784\/401902602139\/303223274509\/401909910685\/403061079558\/402268943485\/402268940174\/321221667982\/401922795144\/232417848418\/303223275194\/303702658954\/401916620546\/231067299911\/274765605099\/360756873322\/161939163395\/303223284222\/163838711730\/303592342368\/133822666122\/133822672561\/133723868640","E_Result":"PartNumber: SWFL-TS \nPartNumber \u043d\u0435 \u043f\u043e\u0434\u0445\u043e\u0434\u0438\u0442 \u043f\u043e \u043f\u0430\u0440\u0430\u043c\u0435\u0442\u0440\u0430\u043c \u0434\u043b\u0438\u043d\u044b \u0438 \u043d\u0430\u043b\u0438\u0447\u0438\u0438 \u0446\u0438\u0444\u0440 (SWFL-TS) - \n\u043f\u0430\u0440\u0441\u0438\u043c \u043f\u043e UPC: 'https:\/\/www.ebay.com\/sch\/i.html?_nkw=(634030598987)' \nresult: 0 \nresult: 0 \n\u041f\u043e\u0438\u0441\u043a \u043f\u043e partNum \u0437\u0430\u043f\u0440\u0435\u0449\u0435\u043d. \n\u043f\u0430\u0440\u0441\u0438\u043c \u043f\u043e Title: 'https:\/\/www.ebay.com\/sch\/i.html?_nkw=Tire+Sale+Swooper+Feather+Flag+Only' \nresult: 6 \nhttps:\/\/www.ebay.com\/itm\/293889249797: shipping = (15.50) + 9.45 \nhttps:\/\/www.ebay.com\/itm\/293889249797: New price: (24.95 - 24.95) \nhttps:\/\/www.ebay.com\/itm\/390623707771: shipping = (15.95) + 6.95 \nhttps:\/\/www.ebay.com\/itm\/390623707771: New price: (22.9 - 22.9) \nhttps:\/\/www.ebay.com\/itm\/322648548476: shipping = (49.00) + 9.95 \nhttps:\/\/www.ebay.com\/itm\/322648548476: New price: (58.95 - 58.95) \n\u041d\u0430\u0439\u0434\u0435\u043d\u043e \u0442\u043e\u0432\u0430\u0440\u043e\u0432: 58 \nhttps:\/\/www.ebay.com\/itm\/232417843877: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/142175269625: price 23.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/142175269625: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.95 \nhttps:\/\/www.ebay.com\/itm\/233500262168: price 49.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/233500262168: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 49.95 \nhttps:\/\/www.ebay.com\/itm\/321470878610: price 40.00 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/321470878610: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 40.00 \nhttps:\/\/www.ebay.com\/itm\/322603238720: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/151074011851: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 16.99 \nhttps:\/\/www.ebay.com\/itm\/174272708274: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.15 \nhttps:\/\/www.ebay.com\/itm\/133822676157: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 15.00 \nhttps:\/\/www.ebay.com\/itm\/303250724684: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/360887979293: price 21.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/360887979293: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 21.95 \nhttps:\/\/www.ebay.com\/itm\/303223274509: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/362920036510: price 49.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/362920036510: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 49.95 \nhttps:\/\/www.ebay.com\/itm\/293889249797: price 24.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/293889249797: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 24.95 \nhttps:\/\/www.ebay.com\/itm\/231132480621: price 29.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/231132480621: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 29.99 \nhttps:\/\/www.ebay.com\/itm\/323682264275: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/322072397087: price 29.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/322072397087: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 29.99 \nhttps:\/\/www.ebay.com\/itm\/303223275194: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/231067300269: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/401918694801: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/303623778949: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/401922795144: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/321358118260: price 21.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/321358118260: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 21.95 \nhttps:\/\/www.ebay.com\/itm\/300521064140: price 24.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/300521064140: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 24.95 \nhttps:\/\/www.ebay.com\/itm\/401909910685: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/322111166467: price 49.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/322111166467: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 49.95 \nhttps:\/\/www.ebay.com\/itm\/323682264252: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/390623707771: price 22.9 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/390623707771: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 22.9 \nhttps:\/\/www.ebay.com\/itm\/290837480791: price 21.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/290837480791: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 21.95 \nhttps:\/\/www.ebay.com\/itm\/402815276240: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/133326775911: price 23.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/133326775911: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.95 \nhttps:\/\/www.ebay.com\/itm\/141292131574: price 67.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/141292131574: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 67.99 \nhttps:\/\/www.ebay.com\/itm\/322059902477: price 24.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/322059902477: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 24.95 \nhttps:\/\/www.ebay.com\/itm\/402268940174: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/161939163395: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 16.45 \nhttps:\/\/www.ebay.com\/itm\/232553783495: price 21.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/232553783495: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 21.95 \nhttps:\/\/www.ebay.com\/itm\/131003244078: price 23.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/131003244078: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.95 \nhttps:\/\/www.ebay.com\/itm\/303213786806: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/311329726970: price 23.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/311329726970: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.95 \nhttps:\/\/www.ebay.com\/itm\/303623708596: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/132086256106: price 23.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/132086256106: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.99 \nhttps:\/\/www.ebay.com\/itm\/303222006670: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/291300654761: price 24.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/291300654761: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 24.95 \nhttps:\/\/www.ebay.com\/itm\/322648548476: price 58.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/322648548476: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 58.95 \nhttps:\/\/www.ebay.com\/itm\/303223284222: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/131226420631: price 23.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/131226420631: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.99 \nhttps:\/\/www.ebay.com\/itm\/351186269411: price 150.00 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/351186269411: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 150.00 \nhttps:\/\/www.ebay.com\/itm\/401902602139: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/300843062561: (price_procent) 14 < 14.375 - \nhttps:\/\/www.ebay.com\/itm\/133078963454 Pack: '1 \/ 2' \u041f\u0440\u043e\u043f\u0443\u0441\u043a\u0430\u0435\u043c \u0442\u043e\u0432\u0430\u0440 \nhttps:\/\/www.ebay.com\/itm\/402814031311: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/132448861566: price 67.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/132448861566: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 67.99 \nhttps:\/\/www.ebay.com\/itm\/302522161486 Pack: '1 \/ 4' \u041f\u0440\u043e\u043f\u0443\u0441\u043a\u0430\u0435\u043c \u0442\u043e\u0432\u0430\u0440 \nhttps:\/\/www.ebay.com\/itm\/142690835373: price 23.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/142690835373: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.99 \nhttps:\/\/www.ebay.com\/itm\/303238803697: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/141745316595: price 23.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/141745316595: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.99 \nhttps:\/\/www.ebay.com\/itm\/303222003123: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/401916642082: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/132476282753: price 23.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/132476282753: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.99 \n\u041d\u0430\u0439\u0434\u0435\u043d\u043e \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0449\u0438\u0445 \u0442\u043e\u0432\u0430\u0440\u043e\u0432: 27 \n- https:\/\/www.ebay.com\/itm\/151074011851 \u041f\u0440\u043e\u043f\u0443\u0441\u043a\u0430\u0435\u043c \u0442\u043e\u0432\u0430\u0440 (check_categories) \n- https:\/\/www.ebay.com\/itm\/174272708274 \u041f\u0440\u043e\u043f\u0443\u0441\u043a\u0430\u0435\u043c \u0442\u043e\u0432\u0430\u0440 (check_categories) \nhttps:\/\/www.ebay.com\/itm\/133822676157: eBay_stock_min 'eBay_stock' == '2' (3) - \nhttps:\/\/www.ebay.com\/itm\/303223275194: eBay_stock_min 'eBay_stock' == '2' (3) - \n- https:\/\/www.ebay.com\/itm\/161939163395 \u041f\u0440\u043e\u043f\u0443\u0441\u043a\u0430\u0435\u043c \u0442\u043e\u0432\u0430\u0440 (check_categories) \n\u041f\u043e\u0441\u043b\u0435 \u043f\u0440\u043e\u0432\u0435\u0440\u043a\u0438 \u043a\u0430\u0440\u0442\u043e\u0447\u0435\u043a \u0442\u043e\u0432\u0430\u0440\u043e\u0432 \u043d\u0430\u0439\u0434\u0435\u043d\u043e \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0449\u0438\u0445: 22 \n\u041f\u043e\u0438\u0441\u043a \u043f\u043e Images(Google) \u0437\u0430\u043f\u0440\u0435\u0449\u0435\u043d. \n\u041d\u0430\u0439\u0434\u0435\u043d\u043e \u0442\u043e\u0432\u0430\u0440\u043e\u0432: 0 \n\u041d\u0430\u0439\u0434\u0435\u043d\u043e \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0449\u0438\u0445 \u0442\u043e\u0432\u0430\u0440\u043e\u0432: 0 \ngood_tov = 0 \n\u041f\u043e\u0441\u043b\u0435 \u043f\u0440\u043e\u0432\u0435\u0440\u043a\u0438 \u043a\u0430\u0440\u0442\u043e\u0447\u0435\u043a \u0442\u043e\u0432\u0430\u0440\u043e\u0432 \u043d\u0430\u0439\u0434\u0435\u043d\u043e \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0449\u0438\u0445: 22 \n - https:\/\/www.ebay.com\/itm\/401916642082: Start \nMPN_percent \u043d\u0435 \u043f\u0440\u0438\u043d\u044f\u0442 \u043a \u0440\u0430\u0441\u0441\u0447\u0435\u0442\u0443. \u041d\u0435 \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0442 \u043f\u0430\u0440\u0430\u043c\u0435\u0442\u0440\u044b. \nModel_percent \u043d\u0435 \u043f\u0440\u0438\u043d\u044f\u0442 \u043a \u0440\u0430\u0441\u0441\u0447\u0435\u0442\u0443. \u041d\u0435 \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0442 \u043f\u0430\u0440\u0430\u043c\u0435\u0442\u0440\u044b. \nBrand_percent = 0 \/ 9 * 100 \nMPN: 'SWFL-TS' \/ E_MPN: '' \/ MPN_percent: \nModel: 'SWFL-TS' \/ E_Model: '' \/ Model_percent: \nBrand: 'Flagdom' \/ E_Brand: 'Unbranded' \/ Brand_percent: 0 \n - https:\/\/www.ebay.com\/itm\/401916642082: End \nPartNumber: SWFL-TS \nPartNumber \u043d\u0435 \u043f\u043e\u0434\u0445\u043e\u0434\u0438\u0442 \u043f\u043e \u043f\u0430\u0440\u0430\u043c\u0435\u0442\u0440\u0430\u043c \u0434\u043b\u0438\u043d\u044b \u0438 \u043d\u0430\u043b\u0438\u0447\u0438\u0438 \u0446\u0438\u0444\u0440 (SWFL-TS) - \n\u043f\u0430\u0440\u0441\u0438\u043c \u043f\u043e UPC: 'https:\/\/www.ebay.com\/sch\/i.html?_nkw=(634030598987)' \nresult: 0 \nresult: 0 \n\u041f\u043e\u0438\u0441\u043a \u043f\u043e partNum \u0437\u0430\u043f\u0440\u0435\u0449\u0435\u043d. \n\u043f\u0430\u0440\u0441\u0438\u043c \u043f\u043e Title: 'https:\/\/www.ebay.com\/sch\/i.html?_nkw=Tire+Sale+Swooper+Feather+Flag+Only' \nresult: 7 \n\u041d\u0430\u0439\u0434\u0435\u043d\u043e \u0442\u043e\u0432\u0430\u0440\u043e\u0432: 58 \nhttps:\/\/www.ebay.com\/itm\/232417843877: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/322603238720: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/321470878610: price 65.18 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/321470878610: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 65.18 \nhttps:\/\/www.ebay.com\/itm\/151074011851: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 16.99 \nhttps:\/\/www.ebay.com\/itm\/133822676157: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 15.00 \nhttps:\/\/www.ebay.com\/itm\/142175269625: price 23.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/142175269625: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.95 \nhttps:\/\/www.ebay.com\/itm\/233500262168: price 65.18 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/233500262168: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 65.18 \nhttps:\/\/www.ebay.com\/itm\/174272708274: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.15 \nhttps:\/\/www.ebay.com\/itm\/360887979293: price 21.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/360887979293: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 21.95 \nhttps:\/\/www.ebay.com\/itm\/303623778949: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/303250724684: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/402813853784: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/401902602139: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/231290122710: price 65.18 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/231290122710: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 65.18 \nhttps:\/\/www.ebay.com\/itm\/303223274509: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/401909910685: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/303796032100: price 23.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/303796032100: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.99 \nhttps:\/\/www.ebay.com\/itm\/322110942322: price 21.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/322110942322: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 21.95 \nhttps:\/\/www.ebay.com\/itm\/403061079558: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/402268943485: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/402268940174: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/321221667982: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/313038256080: (price_procent) 14 < 14.375 - \nhttps:\/\/www.ebay.com\/itm\/401922795144: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/402778756284: price 34.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/402778756284: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 34.95 \nhttps:\/\/www.ebay.com\/itm\/232417848418: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/132295910455: price 23.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/132295910455: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.99 \nhttps:\/\/www.ebay.com\/itm\/231946967135: price 21.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/231946967135: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 21.95 \nhttps:\/\/www.ebay.com\/itm\/321358118260: price 21.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/321358118260: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 21.95 \nhttps:\/\/www.ebay.com\/itm\/303223275194: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/303702658954: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/131188183712: price 67.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/131188183712: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 67.99 \nhttps:\/\/www.ebay.com\/itm\/232963760858: price 66.80 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/232963760858: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 66.80 \nhttps:\/\/www.ebay.com\/itm\/401916620546: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/231067299911: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/131059781760: price 67.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/131059781760: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 67.99 \nhttps:\/\/www.ebay.com\/itm\/274765605099: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/402067423772: price 57.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/402067423772: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 57.99 \nhttps:\/\/www.ebay.com\/itm\/300560301788: price 24.95 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/300560301788: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 24.95 \nhttps:\/\/www.ebay.com\/itm\/360756873322: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 20.95 \nhttps:\/\/www.ebay.com\/itm\/161939163395: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 16.45 \nhttps:\/\/www.ebay.com\/itm\/303223284222: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/324249208840: price 21.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/324249208840: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 21.99 \nhttps:\/\/www.ebay.com\/itm\/361560670514: price 69.58 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/361560670514: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 69.58 \nhttps:\/\/www.ebay.com\/itm\/231132480621: price 29.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/231132480621: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 29.99 \nhttps:\/\/www.ebay.com\/itm\/142638051077: price 23.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/142638051077: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.99 \nhttps:\/\/www.ebay.com\/itm\/165162627742: price 48.90 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/165162627742: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 48.90 \nhttps:\/\/www.ebay.com\/itm\/323820595491: price 66.80 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/323820595491: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 66.80 \nhttps:\/\/www.ebay.com\/itm\/163838711730: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.15 \nhttps:\/\/www.ebay.com\/itm\/303592342368: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 19.95 \nhttps:\/\/www.ebay.com\/itm\/133822666122: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 15.00 \nhttps:\/\/www.ebay.com\/itm\/133822672561: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 15.00 \nhttps:\/\/www.ebay.com\/itm\/133723868640: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 17.95 \nhttps:\/\/www.ebay.com\/itm\/131996767886: price 23.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/131996767886: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.99 \nhttps:\/\/www.ebay.com\/itm\/303012572622: price 57.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/303012572622: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 57.99 \nhttps:\/\/www.ebay.com\/itm\/131226420631: price 23.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/131226420631: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 23.99 \nhttps:\/\/www.ebay.com\/itm\/141292131574: price 67.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/141292131574: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 67.99 \nhttps:\/\/www.ebay.com\/itm\/132523491127: price 67.99 > max_price: 21.25 - \nhttps:\/\/www.ebay.com\/itm\/132523491127: price_percent: -5 \/ list_price_current: 28.75 \/ max_price: 21.25 \/ eBay_price: 67.99 \n\u041d\u0430\u0439\u0434\u0435\u043d\u043e \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0449\u0438\u0445 \u0442\u043e\u0432\u0430\u0440\u043e\u0432: 30 \n- https:\/\/www.ebay.com\/itm\/151074011851 \u041f\u0440\u043e\u043f\u0443\u0441\u043a\u0430\u0435\u043c \u0442\u043e\u0432\u0430\u0440 (check_categories) \nhttps:\/\/www.ebay.com\/itm\/133822676157: eBay_stock_min 'eBay_stock' == '2' (3) - \n- https:\/\/www.ebay.com\/itm\/174272708274 \u041f\u0440\u043e\u043f\u0443\u0441\u043a\u0430\u0435\u043c \u0442\u043e\u0432\u0430\u0440 (check_categories) \nhttps:\/\/www.ebay.com\/itm\/303223275194: eBay_stock_min 'eBay_stock' == '2' (3) - \n- https:\/\/www.ebay.com\/itm\/161939163395 \u041f\u0440\u043e\u043f\u0443\u0441\u043a\u0430\u0435\u043c \u0442\u043e\u0432\u0430\u0440 (check_categories) \n- https:\/\/www.ebay.com\/itm\/163838711730 \u041f\u0440\u043e\u043f\u0443\u0441\u043a\u0430\u0435\u043c \u0442\u043e\u0432\u0430\u0440 (check_categories) \nhttps:\/\/www.ebay.com\/itm\/303592342368: eBay_stock_min 'eBay_stock' == '2' (3) - \nhttps:\/\/www.ebay.com\/itm\/133822666122: eBay_stock_min 'eBay_stock' == '' (3) - \n- https:\/\/www.ebay.com\/itm\/133822672561 \u041f\u0440\u043e\u043f\u0443\u0441\u043a\u0430\u0435\u043c \u0442\u043e\u0432\u0430\u0440 (check_categories) \n\u041f\u043e\u0441\u043b\u0435 \u043f\u0440\u043e\u0432\u0435\u0440\u043a\u0438 \u043a\u0430\u0440\u0442\u043e\u0447\u0435\u043a \u0442\u043e\u0432\u0430\u0440\u043e\u0432 \u043d\u0430\u0439\u0434\u0435\u043d\u043e \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0449\u0438\u0445: 21 \n\u041f\u043e\u0438\u0441\u043a \u043f\u043e Images(Google) \u0437\u0430\u043f\u0440\u0435\u0449\u0435\u043d. \n\u041d\u0430\u0439\u0434\u0435\u043d\u043e \u0442\u043e\u0432\u0430\u0440\u043e\u0432: 0 \n\u041d\u0430\u0439\u0434\u0435\u043d\u043e \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0449\u0438\u0445 \u0442\u043e\u0432\u0430\u0440\u043e\u0432: 0 \ngood_tov = 0 \n\u041f\u043e\u0441\u043b\u0435 \u043f\u0440\u043e\u0432\u0435\u0440\u043a\u0438 \u043a\u0430\u0440\u0442\u043e\u0447\u0435\u043a \u0442\u043e\u0432\u0430\u0440\u043e\u0432 \u043d\u0430\u0439\u0434\u0435\u043d\u043e \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0449\u0438\u0445: 21 \n - https:\/\/www.ebay.com\/itm\/133723868640: Start \nMPN_percent \u043d\u0435 \u043f\u0440\u0438\u043d\u044f\u0442 \u043a \u0440\u0430\u0441\u0441\u0447\u0435\u0442\u0443. \u041d\u0435 \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0442 \u043f\u0430\u0440\u0430\u043c\u0435\u0442\u0440\u044b. \nModel_percent \u043d\u0435 \u043f\u0440\u0438\u043d\u044f\u0442 \u043a \u0440\u0430\u0441\u0441\u0447\u0435\u0442\u0443. \u041d\u0435 \u043f\u043e\u0434\u0445\u043e\u0434\u044f\u0442 \u043f\u0430\u0440\u0430\u043c\u0435\u0442\u0440\u044b. \nBrand_percent = 0 \/ 9 * 100 \nMPN: 'SWFL-TS' \/ E_MPN: '' \/ MPN_percent: \nModel: 'SWFL-TS' \/ E_Model: '' \/ Model_percent: \nBrand: 'Flagdom' \/ E_Brand: 'Unbranded' \/ Brand_percent: 0 \n - https:\/\/www.ebay.com\/itm\/133723868640: End","eBay_rating":"","E_ratingS":"4231\/4702","E_Feedb":"99.3","%MPN":"","%brand":"0\/0","E_MPN":"","E_Model":"","E_brand":"","Brand_A":"no","Brand_E":"no","images_E":"https:\/\/i.ebayimg.com\/images\/g\/ScEAAOSwKPNTyGAc\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/31cAAOSwhglTyF~B\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/rXcAAOSwi8VZUsMR\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/B50AAOSwiQ9ZUsMR\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/zP0AAOSwsCddyxz9\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/4V8AAOSw~K5esu7v\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/z8MAAOSwvjVesu7w\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/d0wAAOSwE9xdyx0D\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/0vgAAOSwuuVdyx0F\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/QBQAAOSwklVdyx0H\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/4eMAAOSwJE1dyx0Q\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/JLsAAOSwkfRdyx0X\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/ivgAAOSwrD9dyx0Y\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/4~wAAOSwJfFdyx0b\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/U~cAAOSwai5dyx0d\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/a40AAOSwRiJdyx0g\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/47gAAOSwxUJdyx0k\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/9hMAAOSwc~ddyx0k\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/0w0AAOSwuuVdyx0m\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/DdYAAOSwNHldyx0p\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/pNgAAOSwLIZdyx0s\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/Yc8AAOSwgV1dyx0w\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/cowAAOSwTbRdyx0x\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/cC4AAOSwL-Bdyung\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/E-AAAOSw9fZesu7D\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/9L0AAOSwd~Fesu2v\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/4Y4AAOSw6INdyupJ\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/auUAAOSwyyZdyuss\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/jVYAAOSw-uddyupa\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/OK0AAOSwSeVdyurz\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/JhQAAOSwuWpdyupS\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/DEsAAOSw5WRdyupN\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/Wc8AAOSwl-FdyuqL\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/HXEAAOSwovNdyurc\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/G28AAOSwTm1dyusL\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/psMAAOSwqIxdyupW\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/l6IAAOSwTg5dyurL\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/rysAAOSwb59dyuq8\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/jC4AAOSw~b1dyuoV\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/fYMAAOxy8F1RGQqS\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/-8EAAOSw42JZCNCP\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/nxQAAOSwlndZCNCe\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/PRQAAOxywXFScqjg\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/WtUAAOSwaZddoOD8\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/0wYAAOSwuj1euwM8\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/etUAAOSwCiddTbnI\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/LPsAAOSwdnNdoOEI\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/l04AAOSwT9JgjI6Z\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/HSIAAOSwichgjI6e\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/9xsAAOSwh-ZgjI6i\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/Cx8AAOSwqtRdoOES\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/xeMAAOSwWz1doOES\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/2ukAAOSwlQddoOEU\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/bssAAOSwvAhgjI6n\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/ORoAAOSwKD5gjI68\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/sB0AAOSwpdhgjI8M\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/X5gAAOSw1QpdoOEk\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/wLUAAOSwNHldoOEm\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/HzUAAOSw0d1gjI7U\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/8tAAAOSwU~RfDkNt\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/WW8AAOSwHxFfDiFk\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/WGUAAOSwTARescab\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/eHYAAOSwDexfDkSH\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/U6YAAOSwvDJfDkSJ\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/XlkAAOSw549fDkSM\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/XI4AAOSwISFfDkSO\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/2KgAAOSwHNxfDkSQ\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/T24AAOSwmHhfDkST\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/-6EAAOSwByFfDkSW\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/d4sAAOSwvBRfDkSZ\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/bfQAAOSwgxhfDkSb\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/RfwAAOSwCP5fDkSe\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/vXEAAOSwyE9fDkSg\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/nQEAAOSwqLFfDkSk\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/nXQAAOSwO95fDkSo\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/TIEAAOSw~-RfDkSp\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/cm8AAOSwkV5fDkSs\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/6N8AAOSwHGZdpf4X\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/SZAAAOSwZrteuwXf\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/etUAAOSwCiddTbnI\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/Y5cAAOSw-v5dpf7H\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/yjIAAOSwCOJdpf7J\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/U2cAAOSwkwxdpf7L\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/XBMAAOSwgqZdpf7O\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/k64AAOSwqNxdpf7Q\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/R5UAAOSwTpldpf7T\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/pa8AAOSwTeJdpf7V\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/SaAAAOSw08xdpf7Z\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/nFMAAOSwf~Ndpf7b\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/pbgAAOSwTeJdpf7d\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/WdEAAOSwISddpf7l\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/yTUAAOSw0-5dpf7o\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/RXcAAOSwVZ9dpf7t\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/KjAAAOSwbXpdpf7y\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/HjgAAOSwny1dpf71\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/hGIAAOSwFZddpf74\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/PK8AAOSwWOJdpf76\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/nzoAAOSwtPRd6utB\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/OX0AAOSwIXxdljHr\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/etUAAOSwCiddTbnI\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/5acAAOSwhglTyF~p\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/n80AAOSw-I9giuFL\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/M2wAAOSwL9tgidL-\/s-l400.png \nhttps:\/\/i.ebayimg.com\/images\/g\/dR0AAOSw0YxexYm6\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/etUAAOSwCiddTbnI\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/LAwAAOSwJeVdwK6a\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/7hMAAOSwLSxesu6I\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/T5EAAOSw4MBesu6J\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/FQ8AAOSwrL9dwK9I\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/4owAAOSwA3ddwK9F\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/cZ8AAOSwsvVdwK~D\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/vuwAAOSwUeVdwK9-\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/8CkAAOSw0-5dwK9R\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/YWgAAOSw-qNdwK-V\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/2CMAAOSw1X1dwK-b\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/WWAAAOSwEeVdwK-d\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/4L8AAOSwSxtdwK-~\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/Q40AAOSw4GZfDiic\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/WW8AAOSwHxFfDiFk\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/WGUAAOSwTARescab\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/GBMAAOSw-ZFfDijh\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/0mQAAOSw4SFfDijj\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/0ToAAOSwUmxfDijo\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/3BUAAOSwaPhfDijq\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/eKUAAOSwOIpdyu3E\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/-uEAAOSwRXxesu4F\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/~20AAOSw87xesu4H\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/6EIAAOSw93xdyu5a\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/CroAAOSwd9hdyu6e\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/QnsAAOSwIi5dyu8T\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/WfsAAOSwvJVdyu6-\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/v8kAAOSwlFFdyu56\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/z-wAAOSwkYZdyu5-\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/zNMAAOSwO6hdyu50\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/1BsAAOSwiJRdywv5\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/~68AAOSwmuhesu83\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/V9cAAOSwSjlesu85\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/MmYAAOSwRl1dywwV\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/I5kAAOSw5WRdywwX\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/ktYAAOSwPqBdywwZ\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/EuUAAOSwoJhdywwe\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/bNUAAOSwZTldywwd\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/q8cAAOSwh4tdywwf\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/78sAAOSw-w9dywwh\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/O6MAAOSwYoZdywwl\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/04oAAOSwmENdywwm\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/ufUAAOSw9RZdywwn\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/JIUAAOSw7Wxdywwq\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/wioAAOSwc8Zdywws\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/zSIAAOSwMytdywwv\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/An4AAOSwNHldywwx\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/l70AAOSwZGldyww0\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/WX0AAOSwLtZdyww2\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/Hv0AAOSwISVhgHRG\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/g8kAAOSwHiBeuwSE\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/etUAAOSwCiddTbnI\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/rucAAOSwHwFhgHTm\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/ru0AAOSwHwFhgHTp\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/bNgAAOSwwn9hgHTt\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/oEUAAOSw1G9hgHT6\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/OU8AAOSwkAFhgHUI\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/H-UAAOSw0LlhgHUN\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/WTcAAOSwiiphgHUW\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/6bUAAOSwIUthgHUb\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/krUAAOSw34xhgHUg\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/rhcAAOSwJrxhgHU3\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/XDkAAOSwLQRhgHU~\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/f-UAAOSwZZRhgHVC\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/N4EAAOSwOT9hgHVF\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/WksAAOSwB~VhgHVI\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/sGkAAOSw~JhhgHVO\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/zukAAOSwIiBgidCd\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/v~IAAOSwyVBgQR5d\/s-l400.png \nhttps:\/\/i.ebayimg.com\/images\/g\/N0wAAOSwNc5dywNs\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/hEsAAOSwGAVesu8h\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/jgwAAOSwdkVesu8i\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/4P8AAOSwFINdywOu\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/XxQAAOSwdL5dywOw\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/8bAAAOSwnhddywOy\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/vo0AAOSwH51dywO0\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/gEAAAOSwKWhdywO2\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/G0MAAOSwd9hdywO5\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/3dcAAOSwkYZdywO7\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/GP4AAOSwzL9dywPB\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/m7wAAOSw6S5dywPD\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/GvIAAOSwlXddywPF\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/VaMAAOSwR8pdywPH\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/tRYAAOSwfOBdyeq4\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/CTUAAOSwgGlesu4n\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/ii4AAOSwiFhesu4p\/s-l400.png,https:\/\/i.ebayimg.com\/images\/g\/5QgAAOSwVt9dyfBk\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/-aAAAOSw9RZdyfAt\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/1DcAAOSwsFZdyfDc\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/23kAAOSwZb9dyfDt\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/DCUAAOSwmjZdyfBN\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/zP8AAOSwyuVdyfBv\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/4PcAAOSwy21dyfCF\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/OE8AAOSwjZFd6EDQ\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/-1gAAOSwqIldnm6r\/s-l400.jpg,https:\/\/i.ebayimg.com\/images\/g\/etUAAOSwCiddTbnI\/s-l400.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/ScEAAOSwKPNTyGAc\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/31cAAOSwhglTyF~B\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/8tAAAOSwU~RfDkNt\/s-l300.png \nhttps:\/\/i.ebayimg.com\/images\/g\/zP0AAOSwsCddyxz9\/s-l300.png \nhttps:\/\/i.ebayimg.com\/images\/g\/InYAAOSwnRZgiaqe\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/Hv0AAOSwISVhgHRG\/s-l300.png \nhttps:\/\/i.ebayimg.com\/images\/g\/cC4AAOSwL-Bdyung\/s-l300.png \nhttps:\/\/i.ebayimg.com\/images\/g\/nzoAAOSwtPRd6utB\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/2UIAAOSwY3FhErzC\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/lLUAAOSwSQxexYrw\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/dR0AAOSw0YxexYm6\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/FD0AAMXQL99Sc~BD\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/Y~kAAOSw8FdhhGrd\/s-l300.png \nhttps:\/\/i.ebayimg.com\/images\/g\/P2oAAOSwKPNTyF~Z\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/ENsAAOSwV65fa7I4\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/JZAAAOSwGIJd6ENM\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/X~0AAOxyRNJSc~GP\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/jWQAAOSw6ShZVm79\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/6X4AAOSwFFxcp4u6\/s-l300.jpg \nhttps:\/\/i.ebayimg.com\/images\/g\/1BsAAOSwiJRdywv5\/s-l300.png \nhttps:\/\/i.ebayimg.com\/images\/g\/Z4EAAOSw9otg9hGz\/s-l300.jpg","url_ebay_all":"https:\/\/www.ebay.com\/itm\/232417843877,https:\/\/www.ebay.com\/itm\/322603238720,https:\/\/www.ebay.com\/itm\/303250724684,https:\/\/www.ebay.com\/itm\/303223274509,https:\/\/www.ebay.com\/itm\/323682264275,https:\/\/www.ebay.com\/itm\/231067300269,https:\/\/www.ebay.com\/itm\/401918694801,https:\/\/www.ebay.com\/itm\/303623778949,https:\/\/www.ebay.com\/itm\/401922795144,https:\/\/www.ebay.com\/itm\/401909910685,https:\/\/www.ebay.com\/itm\/323682264252,https:\/\/www.ebay.com\/itm\/402815276240,https:\/\/www.ebay.com\/itm\/402268940174,https:\/\/www.ebay.com\/itm\/303213786806,https:\/\/www.ebay.com\/itm\/303623708596,https:\/\/www.ebay.com\/itm\/303222006670,https:\/\/www.ebay.com\/itm\/303223284222,https:\/\/www.ebay.com\/itm\/401902602139,https:\/\/www.ebay.com\/itm\/402814031311,https:\/\/www.ebay.com\/itm\/303238803697,https:\/\/www.ebay.com\/itm\/303222003123,https:\/\/www.ebay.com\/itm\/401916642082,https:\/\/www.ebay.com\/itm\/232417843877,https:\/\/www.ebay.com\/itm\/322603238720,https:\/\/www.ebay.com\/itm\/303623778949,https:\/\/www.ebay.com\/itm\/303250724684,https:\/\/www.ebay.com\/itm\/402813853784,https:\/\/www.ebay.com\/itm\/401902602139,https:\/\/www.ebay.com\/itm\/303223274509,https:\/\/www.ebay.com\/itm\/401909910685,https:\/\/www.ebay.com\/itm\/403061079558,https:\/\/www.ebay.com\/itm\/402268943485,https:\/\/www.ebay.com\/itm\/402268940174,https:\/\/www.ebay.com\/itm\/321221667982,https:\/\/www.ebay.com\/itm\/401922795144,https:\/\/www.ebay.com\/itm\/232417848418,https:\/\/www.ebay.com\/itm\/303702658954,https:\/\/www.ebay.com\/itm\/401916620546,https:\/\/www.ebay.com\/itm\/231067299911,https:\/\/www.ebay.com\/itm\/274765605099,https:\/\/www.ebay.com\/itm\/360756873322,https:\/\/www.ebay.com\/itm\/303223284222,https:\/\/www.ebay.com\/itm\/133723868640","ebay maxPrice":21.25,"Cat_A_E":"","add_info":"{\"https:\\\/\\\/www.ebay.com\\\/itm\\\/232417843877\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/232417843877\",\"eBay_stock\":\"10\",\"E_stock\":\"\",\"Margin\":4,\"ROI\":22,\"eBay_title\":\"TIRE SALE Advertising Flutter Feather Sign Swooper Banner Flag Only\",\"eBay_rating\":\"\",\"E_ratingS\":\"41943\",\"E_Feedb\":\"99.1\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"NEOPlex Manufacturing\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/ScEAAOSwKPNTyGAc\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"41\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/322603238720\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/322603238720\",\"eBay_stock\":\"10\",\"E_stock\":\"\",\"Margin\":4,\"ROI\":22,\"eBay_title\":\"TIRE SALE CHECKERED Advertising Flutter Feather Sign Swooper Banner Flag Only\",\"eBay_rating\":\"\",\"E_ratingS\":\"41943\",\"E_Feedb\":\"99.1\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/31cAAOSwhglTyF~B\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"47\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/303623778949\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/303623778949\",\"eBay_stock\":\"10*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Auto Repair Advertising Swooper Flutter Feather Flag Detailing Mufflers Tire\",\"eBay_rating\":\"\",\"E_ratingS\":\"1833\",\"E_Feedb\":\"99.5\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/8tAAAOSwU~RfDkNt\\\/s-l300.png\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"366\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/303250724684\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/303250724684\",\"eBay_stock\":\"4*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Welcome Advertising Feather Flag Swooper Flutter Banner Super Flag Open 24\\\/7\",\"eBay_rating\":\"\",\"E_ratingS\":\"1833\",\"E_Feedb\":\"99.5\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/zP0AAOSwsCddyxz9\\\/s-l300.png\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"Multi\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"573\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/402813853784\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/402813853784\",\"eBay_stock\":\"8*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Oil Change Windless Swooper Advertising Feather Flag Mechanic Auto Service RED\",\"eBay_rating\":\"\",\"E_ratingS\":\"4281\",\"E_Feedb\":\"98.9\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/InYAAOSwnRZgiaqe\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"Multi-Color\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"54\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/401902602139\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/401902602139\",\"eBay_stock\":\"7*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Christmas Swooper Flag Advertising Feather Flag Holiday Sale Santa Feliz Navidad\",\"eBay_rating\":\"\",\"E_ratingS\":\"4281\",\"E_Feedb\":\"98.9\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/Hv0AAOSwISVhgHRG\\\/s-l300.png\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"36\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/303223274509\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/303223274509\",\"eBay_stock\":\"4*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Auto Body Advertising Feather Flag Flutter Swooper Sign Banner Mechanic Services\",\"eBay_rating\":\"\",\"E_ratingS\":\"1833\",\"E_Feedb\":\"99.5\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/cC4AAOSwL-Bdyung\\\/s-l300.png\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"Multi\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"449\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/401909910685\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/401909910685\",\"eBay_stock\":\"8*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"New & Used Tires Swooper Flag Advertising Feather Flag Tires for Sale Tire Sale\",\"eBay_rating\":\"\",\"E_ratingS\":\"4281\",\"E_Feedb\":\"98.9\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/nzoAAOSwtPRd6utB\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"46\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/403061079558\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/403061079558\",\"eBay_stock\":\"5*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Now Open Windless Swooper Advertising Feather Flag NowOpen Red & Yellow Sign\",\"eBay_rating\":\"\",\"E_ratingS\":\"4281\",\"E_Feedb\":\"98.9\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/2UIAAOSwY3FhErzC\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"Red\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"66\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/402268943485\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/402268943485\",\"eBay_stock\":\"9*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Venta de Llantas Swooper Feather Flutter Advertising Flag Tire Sale Flag\",\"eBay_rating\":\"\",\"E_ratingS\":\"4281\",\"E_Feedb\":\"98.9\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/lLUAAOSwSQxexYrw\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"Multi-Color\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"16\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/402268940174\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/402268940174\",\"eBay_stock\":\"10*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Tires Swooper Feather Flutter Advertising Flag Bandera Llanta Tire Sale Wheels\",\"eBay_rating\":\"\",\"E_ratingS\":\"4281\",\"E_Feedb\":\"98.9\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/dR0AAOSw0YxexYm6\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"Multi-Color\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"28\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/321221667982\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/321221667982\",\"eBay_stock\":\"10*\",\"E_stock\":\"\",\"Margin\":4,\"ROI\":22,\"eBay_title\":\"OPEN Banner Flag Only RED YELLOW Windless Full Sleeve Swooper Feather\",\"eBay_rating\":\"\",\"E_ratingS\":\"41943\",\"E_Feedb\":\"99.1\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/FD0AAMXQL99Sc~BD\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"17\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/401922795144\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/401922795144\",\"eBay_stock\":\"8*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Car Dealership Swooper Flag Advertising Flag Feather Flag Used Cars Trade Ins\",\"eBay_rating\":\"\",\"E_ratingS\":\"4281\",\"E_Feedb\":\"98.9\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/Y~kAAOSw8FdhhGrd\\\/s-l300.png\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"1,058\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/232417848418\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/232417848418\",\"eBay_stock\":\"10*\",\"E_stock\":\"\",\"Margin\":4,\"ROI\":22,\"eBay_title\":\"AUTO REPAIR Advertising Flutter Feather Sign Swooper Banner Flag Only\",\"eBay_rating\":\"\",\"E_ratingS\":\"41943\",\"E_Feedb\":\"99.1\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/P2oAAOSwKPNTyF~Z\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"47\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/303702658954\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/303702658954\",\"eBay_stock\":\"10*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Look! Swooper Flutter Advertising Feather Flag Promotional Sale Dealership Flag\",\"eBay_rating\":\"\",\"E_ratingS\":\"1833\",\"E_Feedb\":\"99.5\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/ENsAAOSwV65fa7I4\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"Multi-Color\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"33\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/401916620546\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/401916620546\",\"eBay_stock\":\"9*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Fresh Hot Dogs Swooper Flag Advertising Flag Feather Flag Food Concessions\",\"eBay_rating\":\"\",\"E_ratingS\":\"4281\",\"E_Feedb\":\"98.9\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/JZAAAOSwGIJd6ENM\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"25\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/231067299911\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/231067299911\",\"eBay_stock\":\"10*\",\"E_stock\":\"\",\"Margin\":4,\"ROI\":22,\"eBay_title\":\"OPEN Banner Flag Only RAINBOW WHITE Windless Full Sleeve Swooper Feather\",\"eBay_rating\":\"\",\"E_ratingS\":\"41943\",\"E_Feedb\":\"99.1\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/X~0AAOxyRNJSc~GP\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"45\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/274765605099\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/274765605099\",\"eBay_stock\":\"10*\",\"E_stock\":\"\",\"Margin\":4,\"ROI\":22,\"eBay_title\":\"RV Sale Windless Full Sleeve Swooper Flag Feather Banner\",\"eBay_rating\":\"\",\"E_ratingS\":\"892\",\"E_Feedb\":\"99.4\",\"E_MPN\":\"WFFL\",\"E_Model\":\"WFFL\",\"E_brand\":\"Flagdom\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/jWQAAOSw6ShZVm79\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"Red, Blue, Yellow, Black, White\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"10\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/360756873322\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/360756873322\",\"eBay_stock\":\"10*\",\"E_stock\":\"\",\"Margin\":3,\"ROI\":17,\"eBay_title\":\"MOTORCYCLES Banner Flag Only BLUE 3\\u2019 Wide Flutter Advertising Swooper Feather\",\"eBay_rating\":\"\",\"E_ratingS\":\"41943\",\"E_Feedb\":\"99.1\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"NEOPlex Manufacturing\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/6X4AAOSwFFxcp4u6\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"17\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/303223284222\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/303223284222\",\"eBay_stock\":\"9*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Food Advertising Feather Flutter Swooper Banner Flag Burgers BBQ Hot Dog Wings\",\"eBay_rating\":\"\",\"E_ratingS\":\"1833\",\"E_Feedb\":\"99.5\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/1BsAAOSwiJRdywv5\\\/s-l300.png\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"Multi\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"531\"},\"https:\\\/\\\/www.ebay.com\\\/itm\\\/133723868640\":{\"URL: Ebay\":\"https:\\\/\\\/www.ebay.com\\\/itm\\\/133723868640\",\"eBay_stock\":\"19*\",\"E_stock\":\"\",\"Margin\":6,\"ROI\":36,\"eBay_title\":\"Nails Swooper Flag Advertising Feather Flag Nail Salon-New\",\"eBay_rating\":\"\",\"E_ratingS\":\"4702\",\"E_Feedb\":\"99.3\",\"E_MPN\":\"\",\"E_Model\":\"\",\"E_brand\":\"Unbranded\",\"Brand_E\":\"no\",\"images_E\":\"https:\\\/\\\/i.ebayimg.com\\\/images\\\/g\\\/Z4EAAOSw9otg9hGz\\\/s-l300.jpg\",\"Cat_A_E\":\"\",\"E_Categories.Tree\":\"\",\"E_color\":\"\",\"E_weight\":\"\",\"E_package\":\"\",\"E_Sales\":\"22\"}}","":"","Locale":"com","Image":"https:\/\/images-na.ssl-images-amazon.com\/images\/I\/41rrdBchPQL.jpg","Title":"Tire Sale Swooper Feather Flag Only","Sales Rank: Current":"133441","Sales Rank: 30 days avg.":"169789","Sales Rank: 30 days drop %":"0.21","Sales Rank: 90 days drop %":"0.28","Sales Rank: Drops last 30 days":"7","Reviews: Rating":"4","Reviews: Review Count":"12","Reviews: Ratings - Format Specific":"0","Reviews: Reviews - Format Specific":"0","Last Price Change":"2021\/10\/24 23:56","Amazon: 30 days avg.":"","Amazon: 90 days avg.":"","Amazon out of stock percentage: 90 days OOS %":"100","New, 3rd Party FBA: Current":"","New, 3rd Party FBA: 30 days drop %":"0","FBA Fees:":"","New, 3rd Party FBM: Current":"28.75","New, 3rd Party FBM: 30 days drop %":"-0.24","List Price: Current":"","Count of retrieved live offers: New, FBA":"0","Count of retrieved live offers: New, FBM":"1","Buy Box: Current":"28.75","Buy Box: 30 days avg.":"23.29","Buy Box out of stock percentage: 90 days OOS %":"0","Buy Box: Stock":"30","Buy Box Seller":"FlagPad (86% A19C7W6POXZ2X2)","Buy Box: Is FBA":"no","eBay New: Current":"","eBay New: Lowest":"","Listed since":"2014\/08\/08","URL: Amazon":"https:\/\/www.amazon.com\/dp\/B00MJ8PYNY","URL: Keepa":"https:\/\/keepa.com\/#!product\/1-B00MJ8PYNY","Categories: Root":"Office Products","Categories: Tree":"Office Products \u203a Office & School Supplies \u203a Store Signs & Displays \u203a Store Signs","Product Codes: EAN":"634030598987","Product Codes: UPC":"634030598987","Product Codes: PartNumber":"SWFL-TS","Manufacturer":"Flagdom","Brand":"Flagdom","Model":"SWFL-TS","Color":"Red\/Black\/Yellow","Size":"","Author":"","Package: Length (cm)":"0","Package: Width (cm)":"0","Package: Height (cm)":"0","Package: Weight (g)":"0","Prime Eligible (Amazon offer)":"no","-52":"","Brand_R":"available","Brand_date":"2011","Ebay_url":"https:\/\/www.ebay.com\/sch\/i.html?_nkw=(634030598987)","trademarkia_url":"https:\/\/www.trademarkia.com\/trademarks-search.aspx?tn=Flagdom","trademarks.justia_url":"https:\/\/trademarks.justia.com\/search?q=Flagdom","trademarks.justia_query":null,"trademarks.justia_count":null,"trademarks.justia_category":null,"trademarks.justia_owner":null}
            [comparsion_info] =>
            [results_all_all] =>
            [results_1_1] =>
            [images] =>
            [images_url] =>
            [item_url] =>
            [date_add] => 2021-11-02 15:12:28
            [user_id] => 1
            [product_id] => 6528
            [node] => 6
            [status] => MATCH
            [message] =>
            [messages_id] => -1
            [created_at] => 1654833471
            [updated_at] => 1654833471
            [url] => 6
            [source_id] => 1

            + [item_right] = common\models\Product_right Object
     *
     * */

    $out = [];



    foreach ($res as $k => $r){

      $_array = [];
      $id = $r['id'];
      $node_id = $r['node'];
      $res_ = $this->source_class::findOne(['id' => $id ]);

      if (!$res_){
        $addInfo = [];
      }else{
        $addInfo = $res_->getAddInfo();
      }

      $item = $addInfo[$node_id];
      $r['item_right'] = $item;

      $out[] = $r;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();


/*
    $sheet->fromArray(
      [1, 2, 3],
      null,
      'A1'
    );
*/
    unset($item);


    foreach ($out as $item) { // row
      $itm = $item['item_right'];

      $cell = 1;
      foreach ($ids_keys as $id_key){ // cell
        if ((int)$id_key['checked'] === 1){
          $key_name = $id_key['name'];
          $val = $key_name;

          $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cell, 1, $val);
          $range_1 = $spreadsheet->getActiveSheetIndex();


          $coo = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($cell,1)->getCoordinate();

          if ($id_key['type'] === 'left_item'){
            $style_2 = 1;

            $color = new \PhpOffice\PhpSpreadsheet\Style\Color();
            $spreadsheet->getActiveSheet()->getStyle($coo.":".$coo)->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->setStartColor($color->setRGB('E0E000'))
              ;
          }
          if ($id_key['type'] === 'right_item'){
            $style_1 = 2;

            $color = new \PhpOffice\PhpSpreadsheet\Style\Color();
            $spreadsheet->getActiveSheet()->getStyle($coo.":".$coo)->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->setStartColor($color->setRGB('04AC00'))
              ;
          }

          //echo '<pre>'.PHP_EOL;
          //print_r($activeCell->getStyle()->getFill()->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_GREEN));
          //echo PHP_EOL;
          $cell++;
        }
      }

//      $start = $spreadsheet->getActiveSheet()->getCell([1,1])->getCoordinate();
//      $end = $spreadsheet->getActiveSheet()->getCell([$cell,1])->getCoordinate();
//      $spreadsheet->getActiveSheet()->getStyle($start.':'.$end)->getFill()
//        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
//        ->getStartColor()->setARGB('FFFF0000');
    }
    /*
    $conditional2 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
    $conditional2->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
    $conditional2->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
    $conditional2->addCondition(10);
    $conditional2->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
    $conditional2->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $conditional2->getStyle()->getFill()->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);

    //$conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('A1:A10')->getConditionalStyles();
    $conditionalStyles[] = $conditional2;
    $spreadsheet->getActiveSheet()->getStyle('A1:A10')->setConditionalStyles($conditionalStyles);
    */

    $row = 2;
    foreach ($out as $item){ // row
      $itm = $item['item_right'];

      $cell = 1;
      foreach ($ids_keys as $id_key){ // cell

        if ((int)$id_key['checked'] === 1){
          $key_name = $id_key['name'];

          $val = '---';
          if ($id_key['type'] === 'right_item'){
            $val = $itm->$key_name;
          }
          if ($id_key['type'] === 'left_item'){
            $val = $itm['parent_item'][$key_name];
          }

          if (is_array($val)) $val = 'ARRAY!!!';
          if (is_object($val)) $val = 'OBJECT!!!';

          $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cell, $row, $val);
          $cell++;
        }
      }
      $row++;
    }

    // При выгрузке даенных в файл, в имени файла писать:
    // Источник.check.profile_дд.мм.гггг._кол.записей,
    // напимер:
    // Ebay.check.general_21.06.2022_11, profile пишем если он был выбран

    // EBAY.MATCH.2022_07_01__12_40.27.xlsx
    $source_name = Source::get_source($source_id)['source_name'];
    $comparison_db = $comparison;
    if ($comparison === 'YES_NO_OTHER') $comparison = 'RESULT';
    $_comparison = '_'.$comparison ?: '';
    $_profile = $profile ?: '';
    if ($_profile === '{{all}}') $_profile = '_ALL'; else $_profile = '_'.$_profile;
    $date = '_'.date('Y.m.d_H.i',time());
    $cnt_items = '_'.($row - 2);

    // EBAY_MATCH_All_2022.07.02_12.59_19

    $filename = $source_name.$_comparison.$_profile.$date.$cnt_items.'.xlsx';

    $stat_log = new Stats__import_export();
    $stat_log->type = 'EXPORT';
    $stat_log->file_name = $filename;
    $stat_log->comparison = $comparison_db;
    $stat_log->cnt = $row - 2;
    //$stat_log->raw = json_encode($out);
    $stat_log->raw = '';
    $stat_log->source_id = $source_id;
    $stat_log->profile = $profile;
    $stat_log->created = date('Y-m-d H:i:s',time());
    $stat_log->insert();


    $writer = new Xlsx($spreadsheet);
    $writer->save('export/'.$filename);


    if ($method === 'POST'){
      // json
      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return [
        'res' => 'ok',
        'file' => 'export/'.$filename
      ];
    }

    if ($method === 'GET'){
      //header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; filename="''"');
      header("Location: /export/".$filename);
      //header('Content-Disposition: attachment; filename="'.$filename.'"');
    }

    // https://www.codexworld.com/export-data-to-excel-in-php/
    exit;


  }

  private function format_keys($data,$source_id,$type){
    /*
    * [id] => 1
      [name] => url_ebay
      [source_id] => 1
      [type] => left_item
      [selected] => 0
      [position] => 0
     * */

    $out = [];
    foreach ($data as $k => $value){
      $out[$k]['id'] = -1;
      $out[$k]['name'] = $value;
      $out[$k]['source_id'] = $source_id;
      $out[$k]['type'] = $type;
      $out[$k]['selected'] = 0;
      $out[$k]['position'] = 0;
    }
    return $out;
  }

  private function get_source($source_id){
    $s = Source::get_source($source_id);
    if ($s){
      $this->source_id = $s['source_id'];
      $this->source_class = $s['source_class'];
      $this->source_table_name = $s['source_table_name'];
      $this->source_table_name_2 = $s['source_table_name_2'];
    }
  }

  private function get_keys_from_db($source_id){
    $out = [];
    $res = Exports__saved_keys::find()->where(['source_id' => $source_id])->orderBy(['position' => SORT_ASC])->all();
    if ($res) {
      foreach ($res as $k => $item){
        $_arr['id'] = $item->id;
        $_arr['checked'] = $item->selected;
        $_arr['name'] = $item->name;
        $_arr['type'] = $item->type;

        $out[] = $_arr;
      }
    }

    return $out;
  }

}