<?php

namespace backend\controllers;


use backend\models\P_all_compare;
use backend\models\Source;
use backend\models\User;
use common\helpers\AppHelper;
use common\models\HiddenItems;
use common\models\P_user_visible;
use common\models\Parser_china;
use common\models\Stats_import_export;
use Yii;
use common\models\Comparison;
use common\models\Product;
use common\models\Message;
use common\models\ProductSearch;
use yii\db\ActiveRecord;
use yii\helpers\BaseUrl;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\db\Expression;
use common\models\Comparison\Aggregated;
use yii\data\ActiveDataProvider;
use yii\web\Request;
use yii\web\Session;
use yii\web\UrlManager;
use yii\web\UrlNormalizer;
use yii\web\UrlRule;


/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller{
  public $prev = null;
  public $next = null;

  /**
   * @var \yii\db\ActiveRecord
   */
  public $source_id;
  public $source_class;
  public $source_table_name;
  public $source_table_name_2;

  /**
   * @inheritDoc
   */
  public function behaviors(){
    $rules_3 = [];


    $rules_1 = ['allow' => true, 'actions' => ['index','get_products_by_params'], 'roles' => ['@'],];

    $rules_2 = [
      'allow' => true,
      'actions' => ['compare', 'view', 'result', 'missall', 'user_visible_fields','reset_compare','del_item','test1','get_products_by_params'],
      'roles' => ['compare-products'],
      'roleParams' => function(){
        return ['product' => $this->source_class::findOne(['id' => Yii::$app->request->get('id')])];
      },
    ];

    // если у товара есть 1. сообщение с 2. [X]_show_all

    $id = $this->request->get('id');
    /*
          $_model = Product::find()
            ->select('*')
            ->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = parser_trademarkia_com.id')
            ->leftJoin('hidden_items','hidden_items.p_id = parser_trademarkia_com.id ')
            ->limit(1)
            ->where(['like','info','add_info'])
            ->andWhere(['id' => $id])
            ->orderBy('id ASC');

          $item = $_model->one();
          $add_info = $compare_items = $item->addInfo;
    */ //      comparisons.messages_id = messages.id ?? settings__visible_all = 1
//      where product_id = $id AND messages.settings__visible_all = 1

    $res = Comparison::can_compare($id,$this->source_id);

    //echo '<pre>'.PHP_EOL;
//      print_r($res);
    //echo PHP_EOL;
//      exit;
    if ($res) {
      $rules_3 = ['allow' => true, 'actions' => ['compare', 'view', 'result', 'missall', 'user_visible_fields','test1','get_arrow'], 'roles' => ['@'],];
    }

    // после того как новый нажал MATCH MISMATCH OTHER   ( comaprison )
    // todo удалить comparison других?? или перезаписать на себя?

    return array_merge(parent::behaviors(),
      [
        'access' =>
         [
          'class' => AccessControl::class,
          'rules' => [$rules_1, $rules_2, $rules_3]
         ],
        //ImportController::className()
      ]
    );
  }

  public function set_source($source_id = false){

    if (!$source_id){
      $source_id = $this->request->get('filter-items__source', false);
      if (!$source_id) $source_id = $this->request->get('source_id', false);
      if (!$source_id) $source_id = $this->request->post('source_id', false);
    }
    if ($source_id === false){
/*
      if ($s_source = (new Session())->get('source')){
        $source_id = $s_source['id'];
      }else{
        $source_id = 1;
      }
*/
      $source_id = 1;
    }

    $source = Source::findOne(['id' => (int)$source_id]);

    //(new Session())->set('source',$source);
    if (!$source) {
      $url[] = $_SERVER['REDIRECT_URL'];
      $get_ = $this->request->get();
      $get_['filter-items__source'] = 1;
      $get_['source_id'] = 1;

      header('Location: '. Url::to(array_merge($url,$get_)));
      exit;
    }

    $this->view->params['source_id'] = $source_id;
    $this->source_id = $source_id;
    $this->source_table_name = $source->table_1;
    $this->source_table_name_2 = $source->table_2;
    return $this->source_class = 'common\models\\'.ucfirst($source->table_1);
  }


  public function actionIndex(){
    ini_set("memory_limit", "3024M");

    $where = [];
    $where_1 = [];
    $where_2 = [];
    $where_3 = [];
    $where_4 = []; // users
    $where_5 = []; // comparing_images
    $where_6 = []; // comparisons MATCH MISMATCH OTHER    + NOCOMAPRE

    $f_items__source = $this->request->get('filter-items__source',1);

    //$this->set_source(); в controller::beforeAction()

    $filter_items__profile = $this->request->get('filter-items__profile');

    $f_items__right_item_show = $this->request->get('filter-items__right-item-show');
    $f_items__show_n_on_page = $this->request->get('filter-items__show_n_on_page',10);
    $f_items__id = $this->request->get('filter-items__id');
    $f_items__comparing_images = $this->request->get('filter-items__comparing-images');
    $f_items__target_image = $this->request->get('filter-items__target-image');
    $f_items__user = $this->request->get('filter-items__user');

    /* объеденил [no compare и select] */
    $f_items__comparisons = $this->request->get('filter-items__comparisons'); // ! new
    $f_items__no_compare = $this->request->get('filter-items__no-compare');
    if ($f_items__no_compare === 'on') $f_items__no_compare = true;
    if (!$f_items__no_compare) $where_1 = ['and',['hidden_items.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]];

    $page_n = (int)$this->request->get('page',0);
    /**/
    $no_compare = false;

    if (User::isAdmin() && !$f_items__comparisons) {
      $no_compare = false;
      $f_items__comparisons = 'YES_NO_OTHER';

      $get_array = Yii::$app->request->get();
      $_url = ['product/index'];

      if ($page_n === 0) $_url['page'] = 1;
      $_url['filter-items__comparisons'] = "YES_NO_OTHER";

      return $this->redirect(array_merge($get_array,$_url));
    }
    if (!User::isAdmin() && !$f_items__comparisons) {
      $no_compare = true;
      $f_items__comparisons = 'NOCOMPARE';

      $get_array = Yii::$app->request->get();
      $_url = ['product/index'];

      if ($page_n === 0) $get_array['page'] = 1;
      $get_array['filter-items__comparisons'] = "NOCOMPARE";

      if ($page_n === 0) $_url['page'] = 1;
      $_url['filter-items__comparisons'] = "NOCOMPARE";

      return $this->redirect(array_merge($get_array,$_url));
    }



    $on_page_str = null;

    // тяжелый запрос
    $where_3_list = $this->where_3_list_for_filter();
    $where_4_list = $this->where_4_list_for_filter();

    if ($f_items__id){
      $where_2 = ['or',[$this->source_table_name.'.id' => $f_items__id], [$this->source_table_name.'.asin' => $f_items__id] ];
    }
    if ($f_items__target_image){
      $where_3 = ['like', 'info', '"Categories: Root": "'.$f_items__target_image.'"'];
    }
    if ($f_items__user){
      $where_4 = ['like', 'users', $f_items__user];
    }
    if ($f_items__comparing_images){ //
      $where_5 = ['like', 'info', str_replace('/','\/',$f_items__comparing_images)];
    }

    $where_6_list = $this->where_6_list_for_filter();

    $where_10 = [];
    if ($filter_items__profile && $filter_items__profile !== '{{all}}' && $filter_items__profile !== 'Все'){
      $where_10 = ['like', $this->source_table_name.'.`profile`', $filter_items__profile];
    }


    if ($f_items__comparisons){
      if ($f_items__comparisons === 'MATCH') $where_6 = ['or like', 'comparisons_aggregated.statuses', ['MATCH','%,MATCH,%','MATCH,%','%,MATCH'], false];
      if ($f_items__comparisons === 'MISMATCH') $where_6 = ['or like', 'comparisons_aggregated.statuses', 'MISMATCH'];
      if ($f_items__comparisons === 'PRE_MATCH') $where_6 = ['or like', 'comparisons_aggregated.statuses', 'PRE_MATCH'];
      if ($f_items__comparisons === 'OTHER') $where_6 = ['or like', 'comparisons_aggregated.statuses', 'OTHER'];

      // это когда
      if ($f_items__comparisons === 'YES_NO_OTHER') {
        $where_6 = ['and', "`comparisons`.`status` IS NOT NULL AND comparisons.`status` <> 'MISMATCH'"];
      }

      if ($f_items__comparisons === 'NOCOMPARE') {
        $no_compare = true;
        $where_6 = ['and',['p_all_compare.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]];
      }

      if($f_items__comparisons === 'ALL_WITH_NOT_FOUND'){
        // todo ebay не отработал
        $where_1 = [];
      }
    }

    $where_7 = [];
    if (0 && $this->source_table_name === 'parser_trademarkia_com') {
      $where_7 = ['like', 'info', 'add_info'];
    }

    $where_8 = [];
    if (!User::isAdmin()){
      $userId = \Yii::$app->getUser()->id;
      $where_8 = ["IN",'comparisons.user_id',[$userId, null]];

    }
    $where_9 = ['messages.settings__visible_all' => '1'];


    $where__1 = ['and', $where_1, $where_2, $where_3, $where_4, $where_5, $where_6,$where_7,$where_8,$where_10];
    $where__2 = ['and', $where_1, $where_2, $where_3, $where_4, $where_5, $where_6,$where_7,$where_9,$where_10];


    if (!$where_1 && !$where_2 && !$where_3 && !$where_4 && !$where_5 && !$where_6 && !$where_7 && !$where_8 && !$where_10){
      $where__1 = ['and', '1+1'];
    }
    if (!$where_1 && !$where_2 && !$where_3 && !$where_4 && !$where_5 && !$where_6 && !$where_7 && !$where_9 && !$where_10) {
      $where__2 = ['and', '1+1'];
    }

    $where = ['or', $where__1, $where__2];


    $get_array = Yii::$app->request->get();
    $_url[] = 'product/index';
    $get_array['page'] = 1;
    $url_construct = array_merge($_url,$get_array);
    $res_url = Url::toRoute($url_construct);
    if ($page_n === 0) return $this->redirect($res_url);

    /*
    $f_items__user = $this->request->get('filter-items__user_source',false);
    $uid = \Yii::$app->getUser()->id;
    User::find()->where(['id' => $uid])->limit(1)->one()->user__source_access;
    */

    $q = $this->source_class::find()
          //->select('*')
          ->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
          ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
          ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
          ->leftJoin('p_updated','p_updated.p_id = '.$this->source_table_name.'.id ')
          ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
          ->leftJoin('messages','messages.id = comparisons.messages_id')
          ->where($where);

    if (0 && $this->source_table_name === 'parser_trademarkia_com') {
      $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
      $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
    }else{
      $q->innerJoin($this->source_table_name_2,$this->source_table_name_2.'.`asin` = '.$this->source_table_name.'.asin');
    }

    $sort = $this->request->get('filter-items__sort');

    if ($sort){
      if( $sort === 'created_ASC' ) $q->orderBy($this->source_table_name.'.date_add ASC');
      elseif ( $sort === 'created_DESC' ) $q->orderBy($this->source_table_name.'.date_add DESC');
      elseif ( $sort === 'updated_ASC' ) $q->orderBy('p_updated.date ASC');
      elseif ( $sort === 'updated_DESC' ) $q->orderBy('p_updated.date DESC');
      else $q->orderBy($this->source_table_name.'.id');

    }else{
      $q->orderBy($this->source_table_name.'.id');
    }

    $q->addGroupBy('`'.$this->source_table_name.'`.`id`');

    $this->start_import();

    $q_for_cnt = clone $q;

    $cnt_all = $q->count();

    // $f_items__show_n_on_page
    if ($f_items__show_n_on_page !== 'ALL'){
      $f_items__show_n_on_page = (int)$f_items__show_n_on_page;
      $offset = ($page_n-1) * $f_items__show_n_on_page;
      $q->limit($f_items__show_n_on_page);
    }else{
      $offset = 0;
    }

               $q->offset($offset);

//    echo '<pre>'.PHP_EOL;
//    print_r($q);
//    print_r($q->createCommand()->getRawSql());
//    echo PHP_EOL;
//    exit;
//    echo '<pre>'.PHP_EOL;

    $list =    $q->all();

    //echo '<pre>'.PHP_EOL;
//    print_r($q->createCommand()->getRawSql());
    //echo PHP_EOL;
//    exit;


    $pages_cnt = 1;
    // если 8.3 → 9
    if ($f_items__show_n_on_page !== 'ALL') {
      $pages_cnt = $cnt_all / $f_items__show_n_on_page;
      $on_page_str = ($offset+1) . ' ─ ' . $f_items__show_n_on_page;
      $parse = explode('.',$pages_cnt.'');
      if (isset($parse[1]) && (int)$parse[1] > 0){
        $pages_cnt = $parse[0] + 1;
      }
    }else{
      $on_page_str = ($offset+1) . ' ─ ' . $cnt_all;
    }

    $pager = $this->simple_pager($pages_cnt,$page_n,5);


    $this->layout = 'products_list';
    $searchModel = new ProductSearch();
    $params = $this->request->queryParams;
    $data = &$params[$searchModel->formName()];
    if (\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) === null && empty($data['user'])) {
      $params ['user'] = \Yii::$app->user->identity->username;
      $params ['unprocessed'] = true;
    }
    //$dataProvider = $searchModel->search($params);

    $profiles_list = $this->profiles_list_cnt_2();
    //$profiles_list = $this->profiles_list_cnt();
    $this->getView()->params['filter_statuses'] = $this->cnt_filter_statuses($this->request->get('filter-items__profile'));

    $last_update = $this->get_last_local_import();
    
    $cnt_all_right = 0;
    foreach ($list as $k => $product){
        $items = $product->getAddInfo();
        $cnt_all_right += count($items);
    }

    return $this->render('index', [
      'get_' => $this->request->get(),
      'searchModel' => $searchModel,
      //'dataProvider' => $dataProvider,
      'list' => $list,
      'cnt_all' => $cnt_all,
      'cnt_all_right' => $cnt_all_right,
      'on_page' => $on_page_str,
      'pages_cnt' => $pages_cnt,
      'url_construct' => $url_construct,
      'page_n' => $page_n,
      'where_3_list' => $where_3_list,
      'where_4_list' => $where_4_list,
      'where_6_list' => $where_6_list,
      'profiles_list' => $profiles_list,
      'is_admin' => User::isAdmin($this->user->getIdentity()->id),
      'no_compare' => $no_compare,
      'pager' => $pager,
      'sort' => $sort,
      'right_item_show' => $f_items__right_item_show ? 1 : 0,
      'last_update' => $last_update,
    ]);
  }

  public function profiles_list_cnt(){

    /*
    $s = Source::get_source($source_id);
    if (!$s) {
      echo '<pre>'.PHP_EOL;
      print_r('Products::profiles_list() ... не найден source');
      echo PHP_EOL;
      exit;

    }
    */

    $source_class = $this->source_class;
    //$q = $source_class::find()->distinct(true)->select(['profile'])->asArray();
    /* @var $source_class ActiveRecord */
    $q2 = $source_class::find()

      ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
      ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
      ->innerJoin($this->source_table_name_2,$this->source_table_name_2.'.`asin` = '.$this->source_table_name.'.asin')

      ->where(['and',['hidden_items.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]])

      ->asArray();

    $q2->select($this->source_table_name.'.id, ' . $this->source_table_name.'.profile' )
        ->groupBy($this->source_table_name.'.id ');

    $q2_load = $q2->all();
    $q2_load_cnt = $q2->count();

    $res_handler = function($data){
      // нужно отдать
      //
      // array
      //    [profile] => General
      //    [cnt] => 267
      // array
      //    [profile] => Prepod_
      //    [cnt] => 1

      $out_1 = [];

      foreach ($data as $item){
        if (!isset($out_1[$item['profile']])) $out_1[$item['profile']] = 0;
        $out_1[$item['profile']] += 1;
      }

      $out_2 = [];

      foreach ($out_1 as $k => $v){
        $arr['profile'] = $k;
        $arr['cnt'] = $v;
        $out_2[] = $arr;
      }

      return $out_2;

    };

    $q2_res = $res_handler($q2_load);


    //$cnt_all = $q1->one()['cnt'] ?? 0;
    // $q2_res = $q2->all();
/*
     [0] => Array
        (
            [profile] => General
            [cnt] => 267
        )

    [1] => Array
        (
            [profile] => Prepod_
            [cnt] => 1
        )

    [2] => Array
        (
            [profile] => General_2
            [cnt] => 23
        )

    [3] => Array
        (
            [profile] => Prepod
            [cnt] => 41
        )
 *
 * */

    $profile_list = [];

    if ($q2_res)
    foreach ($q2_res as $item){
      //$item = strtolower($item);
      $e_items = explode(',', $item['profile']);
      $profile_list['cnt'][$item['profile']] = $item['cnt'];

      foreach ($e_items as $e_item){
        $e_item = trim($e_item);
        $profile_list[$e_item] = $e_item;

        if (count($e_items) > 1) $profile_list['cnt'][$e_item] += $item['cnt'];
      }
    }

    // это если считать каждое значение в товаре например и  General + Prepod_ + General_2 даже если они в одном товаре
    $all = function($profile_list){
      $out = 0;
      foreach ($profile_list as $k => $item) {
        if ($k === 'cnt') continue;
        $out += $profile_list['cnt'][$item];
      }
      return $out;
    };


    $concat_cnt_to_list = function($profile_list){
      $out = [];
      foreach ($profile_list as $k => $item) {
        if ($k === 'cnt') continue;
        $out[$k] = $item . ' (' . $profile_list['cnt'][$k]  . ')';
      }
      return $out;
    };

    $list_out = $concat_cnt_to_list($profile_list);

    $all_cnt = $all($profile_list);
    // !!!! что считать если один товар содержит 3 значения то товар же один ... дак выводить цифру 1 или 3
    $a['{{all}}'] = 'Все ('.$q2_load_cnt.')';

    return array_merge($a,$list_out);
  }

  public function profiles_list_cnt_2(){
    /* @var $source_class ActiveRecord */
    $source_class = $this->source_class;
    $q0 = $source_class::find()->distinct(true)->select(['profile'])->asArray();

    $res_1 = $q0->column();
//    echo '<pre>'.PHP_EOL;
//    print_r($res_1);
//    echo PHP_EOL;
//    exit;

    $find_uniq = function($data){
      $out = [];
      foreach ($data as $k => $item){
        $a = explode(',',$item);
        foreach ($a as $value){
          $out[$value] = $value;
        }
      }
      return $out;
    };

    $profiles_uniq = $find_uniq($res_1);

    $q2 = $source_class::find()

      ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
      ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
      ->innerJoin($this->source_table_name_2,$this->source_table_name_2.'.`asin` = '.$this->source_table_name.'.asin')

      ->where(['and',['hidden_items.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]]);

    $q2 ->asArray();

    $q2->select($this->source_table_name.'.id, ' . $this->source_table_name.'.profile' )
        ->groupBy($this->source_table_name.'.id ');

    $list_out = [];
    //$q2_load = $q2->all();
    $q2_load_cnt = $q2->count();
    foreach ($profiles_uniq as $p_name){
      $q2_tmp = clone $q2;
      $q2_tmp ->andWhere(['like', $this->source_table_name.'.`profile`', $p_name]);
      $list_out[$p_name] =  $p_name.' ('.$q2_tmp->count().')';
    }

    // !!!! что считать если один товар содержит 3 значения то товар же один ... дак выводить цифру 1 или 3
    $a['{{all}}'] = 'Все ('.$q2_load_cnt.')';


    return array_merge($a,$list_out);
  }



  public function get_last_local_import(){

    $s_import = Stats_import_export::find();
    $s_import->where(['type' => 'IMPORT']);
    $s_import->orderBy(['created' => SORT_DESC]);
    $s_import->limit(1);
    return $s_import->one();

  }


  /**
   * Get next or prev product
   * @param $currentId int id product
   * @param bool $prev bool prev or next pfroduct
   * @param int $item_1__ignore_red
   * @return array|\yii\db\ActiveRecord|null
   */
    public function getNextModel($currentId, $prev = false,$item_1__ignore_red = 0){

      $model = $this->source_class::find()
          ->where([$prev ? '<' : '>', 'id', $currentId])
          ->andWhere(['like','info','add_info'])
          ->orderBy(['id' => $prev ? SORT_DESC : SORT_ASC]);
      if ((int)$item_1__ignore_red === 1){

        $model->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = parser_trademarkia_com.id')
              ->leftJoin('hidden_items','hidden_items.p_id = parser_trademarkia_com.id ')
              ->andWhere(['and', ['`hidden_items`.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]]);
      }

      if (\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) === null) {
            $condition = 'FIND_IN_SET(:value, ' . Aggregated::tableName() . '.users) > 0';
            $username = \Yii::$app->user->identity->username;
            $model
                ->joinWith(['aggregated'])
                ->andWhere(['OR',
                    new Expression($condition, [':value' => $username]),
                    new Expression(Aggregated::tableName() . '.product_id IS NULL')
                ]);
        }
        return $model->one();
    }


  private function do_it_need_to_update($source,$p_date_in_parser){
    // [если дата в source]  меньше  [даты последнего товара(сортировка по дате) в базе парсера] → запускаем импорт

    if (!$source) return false;
    if (!$source['import_local__max_product_date']) $source['import_local__max_product_date'] = '0000-00-00 00:00:00';
    if (!$source['import_local__db_import_name']) return false;
    if (!$p_date_in_parser) return false;

    return $source['import_local__max_product_date'] < $p_date_in_parser;
  }


  private function start_import(){
    set_time_limit(60*5);
    $source_id = $this->source_id;
    $source = Source::get_source((int)$source_id);
    $p_date_in_parser = ImportController::get_max_product_date_in_parser($source);
    // [если дата в source]  меньше  [даты последнего товара(сортировка по дате) в базе парсера] → запускаем импорт
    if ($p_date_in_parser && $this->do_it_need_to_update($source,$p_date_in_parser)){
      \Yii::$app->runAction('import/local_import',['source_id' => (int)$source_id,'p_date_in_parser' => $p_date_in_parser]);
      // статистика в $this->getView()->params['local_import_stat']
    }
  }

  public function beforeAction($action){

    $this->set_source();
    if ($this->is_source_access() === false){

      $user_id = \Yii::$app->getUser()->id;

      $u = User::find()->where(['id' => $user_id])->limit(1)->one();
      $res = $u->user__source_access;
      if ($res){
        $url[] = 'product/index';
        $get_ = $this->request->get();
        $get_['filter-items__source'] = $res[0]->source_id;
        $get_['source_id'] = $res[0]->source_id;

        return $this->redirect(array_merge($url,$get_));
      }

//      echo '<pre>'.PHP_EOL;
//      header('Content-type: text/html; charset=utf-8');
//      print_r('у вас нет доступа к этому источнику');
//      echo PHP_EOL;
//      exit;
    }


    return parent::beforeAction($action);
  }

  public function actionTest1(){
    // http://checker.loc/product/view?id=2351&source_id=2&item_1__ignore_red=0
    $comparisons = $this->request->get('comparisons');
    $id = $this->request->get('id');
    $source_id = $this->request->get('source_id');

    echo '<pre>'.PHP_EOL;
    //print_r($f_items__source);
    echo PHP_EOL;
    exit;
  }

  public function actionView($id){

    // http://checker.loc/product/view?id=6369&direction=next&item_1__ignore_red=1
    // http://checker.loc/product/view?id=6369&direction=next
    // http://checker.loc/product/view?id=6369&item_1__ignore_red=1

    // todo /product/view?id=6369&item_1__ignore_red=0&direction=prev

    $this->layout = 'product';

    $f_items__source = $this->request->get('filter-items__source',1);


    $direction = Yii::$app->request->get('direction',false);
    $item_1__ignore_red = Yii::$app->request->get('item_1__ignore_red',0);
    $item_2__show_all = Yii::$app->request->get('item_2__show_all');


    $comparisons = $this->request->get('comparisons');
    // todo менять урл если пришли без comparisons

    if (!$comparisons) {
      $default = 'PRE_MATCH';
      // http://checker.loc/product/view?comparisons=PRE_MATCH&id=2351&source_id=2

      $get_array = Yii::$app->request->get();
      $_url = ['product/view'];
      $_url['comparisons'] = $default;

      return $this->redirect(array_merge($_url,$get_array));
    }



    if ($comparisons){
      if ($comparisons === 'MATCH')
        $where_6 = ['or like', 'comparisons_aggregated.statuses', ['MATCH','%,MATCH,%','MATCH,%','%,MATCH'], false];
      if ($comparisons === 'MISMATCH') $where_6 = ['or like', 'comparisons_aggregated.statuses', 'MISMATCH'];
      if ($comparisons === 'PRE_MATCH') $where_6 = ['or like', 'comparisons_aggregated.statuses', 'PRE_MATCH'];
      if ($comparisons === 'OTHER') $where_6 = ['or like', 'comparisons_aggregated.statuses', 'OTHER'];

      // это когда
      if ($comparisons === 'YES_NO_OTHER') {
        $where_6 = ['and', "`comparisons`.`status` IS NOT NULL AND comparisons.`status` <> 'MISMATCH'"];
      }
      if ($comparisons === 'NOCOMPARE') {
        $no_compare = true;
        $where_6 = ['and',['p_all_compare.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]];
      }
    }

    $_model = $this->source_class::find()
      ->select('*')
      ->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
      ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
      ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
      ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')

      //->where(['<=>','hidden_items.source_id', $this->source_id])
      ->where('comparisons_aggregated.source_id = '.(int)$this->source_id )

      ->limit(1);


    $where_0 = [];
    if (0 && $this->source_table_name === 'parser_trademarkia_com') {
      $where_0 = ['like','info','add_info'];
    }

    $where_2 = [];
    if ($item_1__ignore_red){
      // показывать только не скрытые
      $where_2 = ['and',['hidden_items.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]];
    }

    if (0 && $direction){

      $where = [];
      if ($direction === 'prev'){
        $where_1 = ['<', 'id', $id];
        $order = 'DESC';
        $_model ->where($where);
      }else{
        $where_1 = ['>', 'id', $id];
        $order = 'ASC';
      }

      $where = ['and',  $where_0,$where_1,$where_2];

      $_model ->where($where)
              ->orderBy('id '. $order);

      $model = $_model->one();

      $url[] = 'product/view';
      $get_ = $this->request->get();
      $get_['id'] = $model->id;
      unset($get_['direction']);
      return $this->redirect(array_merge($url,$get_));

    }else{

      $where_1 = [];
      $where_3 = ['id' => $id];

      $where = ['and', $where_0 ,$where_1,$where_2,$where_3];

      $_model ->where($where)
              ->orderBy('id ASC');

      $model = $_model->one();

      /**/

      $arrows['left']['ignore_checked'] = $this->get_arrows($id,$_model,'prev',1);
      $arrows['left']['ignore_dont_checked'] = $this->get_arrows($id,$_model,'prev',0);

      $arrows['right']['ignore_checked'] = $this->get_arrows($id,$_model,'next',1);
      $arrows['right']['ignore_dont_checked'] = $this->get_arrows($id,$_model,'next',0);


//      echo '<pre>'.PHP_EOL;
//      print_r('[\'left\'][\'ignore_checked\']: ');
//      print_r($arrows['left']['ignore_checked']->id);
//      echo PHP_EOL;
//      print_r('[\'left\'][\'ignore_dont_checked\']:');
//      print_r($arrows['left']['ignore_dont_checked']->id);
//      echo PHP_EOL;
//      print_r('[\'right\'][\'ignore_checked\']:');
//      print_r($arrows['right']['ignore_checked']->id);
//      echo PHP_EOL;
//      print_r('[\'right\'][\'ignore_dont_checked\']:');
//      print_r($arrows['right']['ignore_dont_checked']->id);
//      //echo PHP_EOL;
//      exit;

      if (!$model){
        $where_1 = ['>', 'id', $id];

        $where = ['and',  $where_0,$where_1,$where_2];
        $_model ->where($where)
                ->orderBy('id ASC');
        $model = $_model->one();

      }
      // todo а если последний

    }
    if (!$model) {
      //echo '<pre>'.PHP_EOL;
      print_r('такого товара нет');
      //echo PHP_EOL;
      exit;
    }

    //$model = $this->findModel($id,$item_1__ignore_red,$direction);
    //$prev = $this->getNextModel($id, true,$item_1__ignore_red);
    //$next = $this->getNextModel($id,null, $item_1__ignore_red);

    // http://checker.loc/product/view?id=6369&direction=next&item_1__ignore_red=1

    $prev = null;
    $next = null;

    $node = Yii::$app->request->get('node',1);


    /*
    if ($item_2__show_all === null){
      $item_2__show_all = (new Session())->get('item_2__show_all');
      if ($item_2__show_all === null) $item_2__show_all = 0;
    }else{
      (new Session())->set('item_2__show_all',$item_2__show_all);
    }
    */

    $this->getView()->params = [
        'prev' => $prev,
        'next' => $next,
          // 'item_2__show_all' => $item_2__show_all,
          // 'item_1__ignore_red' => $item_1__ignore_red,
        'arrows' => $arrows,
        'item' => $model,
        'source_id' => $this->source_id,
        'get_' => $this->request->get(),
      ];
    $add_info = $compare_items = $model->addInfo;

    //$_add_info = AppHelper::plus_1_to_keys($add_info);


    $item_2__show_all = 1; // !!! специально когда в скрытии красных отпала необходимсоть
    if ($item_2__show_all){
      $compare_item = AppHelper::get_item_by_number_key($add_info, $node);
      //$compare_items = $_add_info;

    }else{

      // убираем MISMATCH
      $a_without_mismatch = $compare_items = $this->remove_mismatch($add_info, $id);

      // выбираем ближайший node
      $compare_item = AppHelper::get_next_item_by_key($a_without_mismatch, $node);

      if (!isset($a_without_mismatch[$node-1]) && $compare_item){

        // https://checker.loc/product/view?id=6368&node=4&item_1__ignore_red=0&item_2__show_all=1

        $url[] = 'product/view';
        $get_ = $this->request->get();
        $get_['node'] = $compare_item['node_id'] ?: 0;

        //return $this->redirect(array_merge($url,$get_));

      }


    }


    $profiles_list = $this->profiles_list_cnt_2();
    /*
    [{{all}}] => Все (179)
    [Prepod] => Prepod (41)
    [General] => General (142)
    [Prepod_] => Prepod_ (0)
    [General_2] => General_2 (25)
    [General_55] => General_55 (1)

    [
      'label' => '11',
      'template' => '
        <select name="" id="">
            <option value="">1</option>
            <option value="">2</option>
        </select> ',
    ];

    */



    $_for_breadcrumbs = function($list){
      $out['label'] = 'profile_';
      $select[] = '<div class="product_page__filter-profile-wrapper">';
      $select[] = '<select name="" id="product_page__filter-profile" class="form-control">';
      foreach ($list as $k => $item){
        $selected = $k === $this->request->get('filter-items__profile') ? 'selected' : '';
        $select[] = '<option value="'.$k.'" '.  $selected   .' >'.$item.'</option>';
      }
      $select[] = '</select>';
      $select[] = '</div>';

      $out['template'] = implode('',$select);
      return $out;
    };

    $profile = $this->request->get('filter-items__profile');
    $this->getView()->params['filter_statuses'] = $this->cnt_filter_statuses($profile);

    $source = Source::get_source($this->source_id);
    $this->getView()->params['breadcrumbs'][] = ['label' => Yii::t('site', 'Products'), 'url' => ['index']];
    $this->getView()->params['breadcrumbs'][] = ['label' => Yii::t($source['source_name'], $source['source_name']), 'url' => ['index','filter-items__source'=> $source_id]];
    //$this->params['breadcrumbs'][] = $this->title;
    $this->getView()->params['breadcrumbs'][] = $_for_breadcrumbs($profiles_list);


    return $this->render('view', [
      'p_item' => $model,
      'compare_item' => $compare_item,
      'compare_items' => $compare_items,
      'node' => $node,
      'model' => $model,
      'prev' => $prev,
      'next' => $next,
        //'item_2__show_all' => $item_2__show_all,
      'arrows' => $arrows,
      'source_id' => $this->source_id,

    ]);
  }

  private function cnt_filter_statuses($profile){
    /*
    $out['YES_NO_OTHER'] = [
      'hex_color' => '',
      'name' => 'Result',
      'name_2' => 'Все отмеченные',
    ];
    */
    $statuses = Comparison::get_filter_statuses();

    foreach ($statuses as $name => $data){
      $q_cnt =  $this->prepare_record_1($name,$profile);
      $res_cnt = $q_cnt->count();
      $statuses[$name]['cnt'] = $res_cnt;
    }

    return $statuses;
  }

  public function actionResult($id){
    $model = $this->findModel($id);

    $query = Comparison::find();
    $query->with(['user', 'product']);
    $query->where(['product_id' => $model->id]);

    $dataProvider = new ActiveDataProvider(['query' => $query,]);

    return $this->render('result', ['model' => $model, 'dataProvider' => $dataProvider,]);
  }

  /**
   * Check the statuses of compare products
   * @param $id int current product id
   * @return bool|int|string
   * @throws NotFoundHttpException
   */
  public function isStatusSet($id){
    $model = $this->findModel($id);
    foreach (array_values($model->addinfo) as $index => $node) {
      if (Comparison::findOne(['product_id' => $id, 'node' => $index]) === null) {
        return $index;
      }
    }
    return true;
  }

  public function actionReset_compare($id){
    $p_id = Yii::$app->request->get('id');
    $source_id = Yii::$app->request->get('source_id');


    Comparison::deleteAll(['product_id' => $p_id,'source_id' => $source_id]);
    HiddenItems::deleteAll(['p_id' => $p_id,'source_id' => $source_id]);
    P_all_compare::deleteAll(['p_id' => $p_id,'source_id' => $source_id]);

    // json
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
      'res' => 'ok',
    ];

  }

  public function actionCompare($id){
    // http://checker.loc/product/compare?id=6377&node=2&status=MATCH

    //$id = Yii::$app->request->get('id');
    $node = Yii::$app->request->get('node') - 1;
    $is_list_page = Yii::$app->request->get('list',0);

    $model = $this->findModel($id);
    if (!$model){
      //echo '<pre>'.PHP_EOL;
      print_r('не нашел такого товара');
      //echo PHP_EOL;
      exit;
    }

    $comparisonModel = Comparison::findOne(['product_id' => $id, 'node' => $node,'source_id' => $this->source_id]);

    $nodes = array_values($model->addInfo);
    if ($comparisonModel === null && isset($nodes[$node])) {
      $comparisonModel = new Comparison(['product_id' => $model->id,'source_id' => $this->source_id, 'user_id' => Yii::$app->user->id, 'node' => $node]);
    }
    if ($comparisonModel === null) {
      throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    //$m_id = Yii::$app->request->get('msgid') ?: -1 ;
    $m_id = Yii::$app->request->get('msgid');


    $comparisonModel->setStatus(
      Yii::$app->request->get('status'),
      $m_id,
      array_keys($model->addInfo)[$node],$id
    );

    $comparisonModel->save();

    $a_url = parse_url($this->request->referrer);
    $part_1 = $a_url['path']; // /product/view
    //print_r($part_1);

    $get_array = Yii::$app->request->get();
    $_url[] = $part_1;
    $get_array['node'] = $node+1;
    unset($get_array['status']);
    $url_construct = array_merge($_url,$get_array);
    $res_url = Url::toRoute($url_construct);

    if ($this->request->get('return',0) && Yii::$app->request->get('status') === 'MISMATCH') {
      return $this->redirect($this->request->referrer);
    }

    //if (!$this->request->get('load_next',0) && Yii::$app->request->get('status') === 'MISMATCH') {
    if (Yii::$app->request->get('status') === 'MISMATCH') {
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

      return ['status' => 'ok'];
    }

    if ((int)Yii::$app->request->get('list') === 1 && Yii::$app->request->get('status') === 'MATCH') {
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return ['status' => 'ok'];
    }

    if ((int)Yii::$app->request->get('list') === 1 && Yii::$app->request->get('status') === 'PRE_MATCH') {
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return ['status' => 'ok'];
    }

    if (!Yii::$app->request->get('index', false)) {
      //Yii::$app->session->setFlash('success', 'The result of comparing products is saved.');

      if (Yii::$app->request->get('return')) {

        return $this->redirect($res_url);
        //return $this->redirect($this->request->referrer);
      }
      if (($index = $this->isStatusSet($id)) === true) {

        return $this->redirect($res_url);
        //return $this->redirect(['view', 'id' => $this->getNextModel($id)->id]);
      }

      if ($this->request->get('ignore-right-hidden')){
        /*
        $__right_ids = [];
        $__right_ids = array_keys($nodes);
        $right_ids = [];
        foreach ($__right_ids as $_right_id) {
          $right_ids[] = $_right_id + 1;
        }
        */
        $right_ids = array_keys($nodes);

        $res_1 = Comparison::find()
          //->where(['in', 'node', $right_ids])
          ->where(['product_id' => $id, 'status' => 'MISMATCH','source_id' => $this->source_id])
          ->all();

        $out_1 = [];
        if($res_1){
          foreach ($res_1 as $r_1){
            $out_1[] = $r_1->node;
          }
        }
/*
 *  MISMATCH
    [0] => 0
    [1] => 1
    [2] => 2
    [3] => 3
    [4] => 6
    [5] => 12
*/
        $res_2 = array_diff($right_ids,$out_1);

        $res_3 = null;
        $cnt = 0; $first = null;
        foreach ($res_2 as $r2){
          if ($cnt === 0) $first = $r2; $cnt++;
          if ($r2 > $node) {
            $res_3 = $r2;
            break;
          }
        }

        if (empty($res_3))  $res_3 = $first-1;
        if (!empty($res_3)) return $this->redirect(['view', 'id' => $id, 'node' => $res_3+1,'source_id'=>$this->source_id]);

      }

      return $this->redirect(['view', 'id' => $id, 'node' => $node + 1,'source_id' => $this->source_id]);

    } else {

      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return ['status' => 'OK'];
    }
  }

    public function actionMissall(){
      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

      $id = $this->request->post('id');
      if (!$id) {
        $id = $this->request->get('id');
          if (!$id) {
          echo '<pre>'.PHP_EOL;
          print_r('не указан id');
          echo PHP_EOL;
          exit;
        }
      }

      $model = $this->findModel($id);

      $nodes = array_values($model->addInfo);
      $urls = array_keys($model->addInfo);

      if ($nodes){
        foreach ($nodes as $node => $addInfo){
          $comparisonModel = Comparison::findOne(['product_id' => $id, 'node' => $node,'source_id' => $this->source_id]);

          if (!$comparisonModel && isset($nodes[$node])) {
            $comparisonModel = new Comparison([
              'product_id' => $model->id,
              'user_id' => Yii::$app->user->id,
              'node' => $node,
              'source_id' => $this->source_id
            ]);
          }
          $comparisonModel->setStatus(Comparison::STATUS_MISMATCH, null, $urls[$node]);

          $res = $comparisonModel->save();

        }
      }else{
        $comparisonModel = Comparison::findOne(['product_id' => $id,'node' => -1,'source_id' => $this->source_id]);
        if (!$comparisonModel) {
          $comparisonModel = new Comparison([
            'product_id' => $model->id,
            'user_id' => Yii::$app->user->id,
            'node' => -1,
            'source_id' => $this->source_id
          ]);

        }
        $comparisonModel->setStatus(Comparison::STATUS_MISMATCH, null, '');
        $res = $comparisonModel->save();

        $find = HiddenItems::find()->where(['p_id' => $id,'source_id' => $this->source_id])->one();
        if (!$find) {
          $h = new HiddenItems();
          $h->p_id = $id;
          $h->source_id = $this->source_id;
          $h->insert();
        }
      }


      $url['get'] = [];
      $url['scheme'] = ''; //'http';
      $url['host'] = ''; // 'checker.loc';
      $url['path'] = ''; // '/product/view';
      $url['query'] = ''; // id=2344&source_id=2
      if (isset($_SERVER['HTTP_REFERER'])){
        $parse_url = parse_url($_SERVER['HTTP_REFERER']);
        $url = array_merge($url,$parse_url);
        if (isset($parse_url['query'])){
          $query = $parse_url['query'];
          parse_str($query,$get_);
          $url['get'] = $get_;
        }
      }

      if (!$this->request->isPost){
        if ($url['path'] === '/product/view'){
          $this->get_model_for_next($id);
        }
      }

      return [
        'res' => 'ok',
      ];

      /*
      if (Yii::$app->request->get('return')){
          return $this->redirect($this->request->referrer);
      } else {
          return $this->redirect(['view', 'id' => $this->getNextModel($id)->id]);
      }
      */

    }


    private function get_model_for_next($id){
      $f_items__source = $this->request->get('filter-items__source',1);
      $direction = Yii::$app->request->get('direction',false);
      $item_1__ignore_red = Yii::$app->request->get('item_1__ignore_red',0);
      $item_2__show_all = Yii::$app->request->get('item_2__show_all');

      $_model = $this->source_class::find()
        ->select('*')
        ->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
        ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
        //->where(['<=>','hidden_items.source_id', $this->source_id])
        ->where('comparisons_aggregated.source_id = '.(int)$this->source_id )
        ->limit(1);

      $where_0 = [];
      if (0 && $this->source_table_name === 'parser_trademarkia_com') {
        $where_0 = ['like','info','add_info'];
      }
      $where_2 = [];
      if ($item_1__ignore_red){
        // показывать только не скрытые
        $where_2 = ['and',['hidden_items.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]];
      }

      /**/
      /*
      $where = [];

      if ($direction === 'prev'){
        $where_1 = ['<', 'id', $id];
        $order = 'DESC';
        $_model ->where($where);
      }else{
        $where_1 = ['>', 'id', $id];
        $order = 'ASC';
      }
      */

      $where_1 = ['>', 'id', $id];
      $order = 'ASC';


      $where = ['and',  $where_0,$where_1,$where_2];

      $_model ->where($where)
        ->orderBy('id '. $order);

      $model = $_model->one();

      $url[] = 'product/view';
      $get_ = $this->request->get();
      $get_['id'] = $model->id;
      unset($get_['direction']);

      return $this->redirect(array_merge($url,$get_));


    }


  /**
   * Finds the Product model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param int $id ID
   * @param $item_1__ignore_red
   * @param $direction
   * @return array|\yii\db\ActiveRecord
   */
    protected function findModel($id,$item_1__ignore_red = 0,$direction = 'next'){
      $model = null;
      if ((int)$item_1__ignore_red === 1){
        if (HiddenItems::find()->where(['p_id' => $id,'source_id' => $this->source_id])->limit(1)->one()){
          if ($direction === 'prev') $symbol = '<=';
          else $symbol = '>';

          $model = $this->source_class::find()
            ->select('*')
            ->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
            ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
            ->where(['and', ['`hidden_items`.p_id' => null],[$symbol,'`'.$this->source_table_name.'`.`id` ',$id]])
            ->andWhere(['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]);

            if (0 && $this->source_table_name === 'parser_trademarkia_com') {
              $model->andWhere(['like','info','add_info']);
            }

          $model = $model->limit(1)->one();
        }
      }else{
        $model = $this->source_class::find()
          ->with('comparisons')
          ->where(['id' => $id]);
        $model = $model->one();
      }

      return $model;
      //throw new NotFoundHttpException(Yii::t('site', 'The requested page does not exist.'));
    }



  //public function actionUser_visible_fields(){
  public function actionUser_visible_fields(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $pid = $this->request->post('pid');

    if (!User::isAdmin()) return false;

    $puv = P_user_visible::findOne(['p_id' => $pid]);

    if ($puv){
      $puv->delete();
      return [
        'res' => 'ok',
        'text' => 'Показать поля (*) пользователю'
      ];
    }else{
      $puv = new P_user_visible();
      $puv->p_id = $pid;
      $puv->save();
      return [
        'res' => 'ok',
        'text' => 'Скрыть поля (*) для пользователя'


      ];
    }



  }

  private function prepare_get_joined($source_class){
    return $q = $source_class::find()
      //->select('*')
      //->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
      ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
      ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
      ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ');
  }


  /**
   * @param $comparison
   * @return \yii\db\ActiveQuery
   */
  private function prepare_record_1($comparison,$profile){
    /* @var $source_class yii\db\ActiveRecord */
    $source_class = $this->source_class;

    $q = $this->prepare_get_joined($source_class);

    //->where(['<=>','hidden_items.source_id', $this->source_id])
    //->where('comparisons_aggregated.source_id = '.(int)$this->source_id );


    if (0 && $this->source_table_name === 'parser_trademarkia_com') {
      $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
      $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
    }else{
      $q->innerJoin($this->source_table_name_2,$this->source_table_name_2.'.`asin` = '.$this->source_table_name.'.asin');
    }

    $q->addGroupBy('`'.$this->source_table_name.'`.`id`');


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



    if ($profile && $profile !== '{{all}}' && $profile !== 'Все'){
      $q ->andWhere(['like', $this->source_table_name.'.`profile`', $profile]);
    }

    $q ->andWhere($where);

//    echo '<pre>'.PHP_EOL;
//    print_r($q->createCommand()->getRawSql());
//    echo PHP_EOL;
//    exit;

    return $q;
  }



  public function actionGet_products_by_params(){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    /*
    [p_id] => 2351
    [comparison] => MATCH
    [source_id] => 2
    [direction] => next
    */
    $post = $this->request->post();
    $p_id = $this->request->post('p_id');
    $comparison = $this->request->post('comparison');
    $source_id = $this->request->post('source_id');
    $profile = $this->request->post('profile');
//    $direction = $this->request->post('direction','next');

    if (!$p_id || !$comparison || !$source_id) return ['res' => 'fuck'];

    /* start */
    $q = $this->prepare_record_1($comparison,$profile);

//    if ($direction === 'prev' || $direction === 'next'){  }

    $prev = $this->prepare_2(clone $q, ['<', $this->source_table_name.'.id', $p_id],'DESC', $comparison, 'prev_items',5);
    $next = $this->prepare_2(clone $q, ['>', $this->source_table_name.'.id', $p_id],'ASC', $comparison, 'next_items',5);
    $this_item = $this->prepare_2($this->prepare_get_joined($this->source_class), [$this->source_table_name.'.id' => $p_id],'ASC', $comparison, 'this_item',1);


/* RIGHT ITEMS */
    $q_this_item = $this->prepare_get_joined($this->source_class);
    $q_this_item->andWhere([$this->source_table_name.'.id' => $p_id]);
    $res = $q_this_item->one();

    $ignore = [];
    if ($comparison === 'NOCOMPARE') $ignore = ['PRE_MATCH', 'MATCH', 'OTHER', 'MISMATCH']; // nocompare
    if ($comparison === 'YES_NO_OTHER') $ignore = ['NOCOMPARE']; // result
    if ($comparison === 'MATCH') $ignore = ['PRE_MATCH', 'OTHER',  'NOCOMPARE', 'MISMATCH']; // match
    if ($comparison === 'MISMATCH') $ignore = ['PRE_MATCH', 'MATCH', 'OTHER', 'NOCOMPARE']; // mismatch
    if ($comparison === 'PRE_MATCH') $ignore = ['MATCH', 'OTHER', 'MISMATCH', 'NOCOMPARE']; // pre_match
    if ($comparison === 'OTHER') $ignore = ['PRE_MATCH', 'MATCH', 'MISMATCH', 'NOCOMPARE']; // other

    $right_items = $res->get_right_items($ignore);

    $fill_right_item = function ($item,$node_id,$parent_id){

      $r_title =  \backend\models\Settings__source_fields::name_for_source('r_Title');
      if ($r_title) $_item['text_brief'] = Html::encode($item->r_Title);

      $_item['id'] = $item->id;
      $_item['asin'] = $item->asin;
      $_item['img_main'] = $item->get_first_image();
      $_item['status'] = $item->get_status();
      //   /product/view?id=8009&node=1&source_id=1&comparisons=PRE_MATCH
      $_item['link'] = '/product/view?id='.$parent_id.'&source_id='.(int)$this->source_id.'&comparisons='.$this->request->post('comparison').'&profile='.$this->request->post('profile');

      return $_item;
    };

    foreach ($right_items as $node_id => $right_item) {
      $out_right_items[$node_id] = $fill_right_item($right_item,$node_id,$p_id);
    }

    $this_item['items']['this_item_right_items'] = $out_right_items;
/* .RIGHT ITEMS */

    $out = array_merge_recursive($this_item,$prev,$next);
    $out['comparison_cnt'] = $this->cnt_filter_statuses($profile);;

    return $out;


    if ($all){

    }else return ['res' => 'fuck',];

    if ($model){
      return [
        'res' => 'ok',
        'link' => '/product/view?id='.$model->id.'&source_id='.(int)$this->source_id.'&comparisons='.$comparison,

      ];
    }
    else return ['res' => 'fuck',];
  }

  private function prepare_2($q, $where, $order, $comparison, $direction, $limit = false){
    if ($where) $q ->andWhere($where);
    if ($order) $q ->orderBy($this->source_table_name.'.id '. $order);

    if ($limit) $q->limit($limit);
    //$model = $q->one();
    $sql = $q->createCommand()->getRawSql();
    $all = $q->all();


    $fill_item = function($item,$source_id,$comparison){
      $_item['id'] = $item->id;
      $_item['asin'] = $item->asin;
      $_item['img_main'] = $item->get_img_main();
      $_item['link'] = '/product/view?id='.$item->id.'&source_id='.(int)$source_id.'&comparisons='.$comparison;

      $_item['title'] = Html::encode($item->baseInfo['Brand']);
      return $_item;
    };

    foreach ($all as $k => $item) {
      $next_items[] = $fill_item($item, $this->source_id, $comparison);
    }

    $items[$direction] = $next_items ?? [];

    return $out = [
//      'res' => 'ok',
//      'sql' => $sql,
      'items' => $items,
      //'link' => '/product/view?id='.$model->id.'&source_id='.(int)$this->source_id.'&comparisons='.$comparison,
    ];
  }

  private function get_arrows($id,$_model,$direction,$item_1__ignore_red){
    $where_0 = [];
    if (0 && $this->source_table_name === 'parser_trademarkia_com') {
      $where_0 = ['like', 'info', 'add_info'];
    }

    $where_1 = [];
    $where_2 = [];
    if ($item_1__ignore_red) $where_2 = ['and',
      ['hidden_items.p_id' => null],
      ['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]],
    ];  // $item_1__ignore_red = 1

    if ($direction === 'prev'){
      $where_1 = ['<', 'id', $id];
      $order = 'DESC';
    }else{
      $where_1 = ['>', 'id', $id];
      $order = 'ASC';
    }

    $where = ['and',  $where_0, $where_1, $where_2];
    $_model ->where($where)
            ->orderBy('id '. $order);

    $model = $_model->one();


    if ($model) return '/product/view?id='.$model->id.'&source_id='.(int)$this->source_id.'&item_1__ignore_red='.$item_1__ignore_red.'&direction='.$direction;
    else return null;

  }

  private function remove_mismatch($add_info,$id){
    $out = [];
    foreach ($add_info as $k => $a_info){
      $res = Comparison::findOne(['product_id' => $id,'node' => $k,'source_id' => $this->source_id]);
      if (!$res) $out[$k] = $a_info;
      else{
        if ($res->status !== 'MISMATCH') $out[$k] = $a_info;
      }
    }
    return $out;
  }

  private function simple_pager($pages_cnt,$page_n,$left_right_n = 3){
    // $pages_cnt 1_|2_3_4_[5]_6_7_8|_9_10_11
    // 5-(cnt3)=2    от 2...
    //                       5
    // (cnt3+1)+5=9  до        ...9

    //  $pages_cnt -1 0 1_2_3_4_5_|6_7_8_[9]_10 11 12 13
    //  from  9-(cnt3)=6    от -   значит   6...
    //             <0 →  |0|+1                      9
    //  to    9+(cnt3+1)+(↑+0)=13                              ...13
    // если $всего=13 < to=15
        // $pages_cnt(10)- $to(13) = add_from=3
        // $from = $page_n[9]-$left_right_n(cnt3)-3= from3  →  if (from3 < 0) = from = 1
        //

    //    $pages_cnt -1 0 1_2_3_4_5_6_7_8_9_[10]_11
    //  from  3-(cnt3)=0    от -   значит   1...
    //             <0 →  |0|+1                      5
    //  to    3+(cnt3+1)+(↑+1)=8                              ...8

    // 1_[2]_3_4_5_6_7_8|_9_10_11
              //2     3
    $from = $page_n-$left_right_n; //1
    $add_to_v2 = 0;
    if ($from <= 0){
      $add_to_v2 = abs($from)+1; //2
      $from = 1;
    }
    $to = $page_n + $left_right_n + $add_to_v2;

    if ($pages_cnt < $to){
      //$to = $pages_cnt;
      $add_to_from = $pages_cnt - $to;
      $from = $page_n-$left_right_n+$add_to_from;
      if ($from < 1) $from = 1;
    }

    if ($from > $pages_cnt && $from > $to) $from = 1;
    if ($to > $pages_cnt) $to = $pages_cnt;

    $pager['from'] = $from;
    $pager['to'] = $to;
    $pager['this_n'] = $page_n;
    $pager['in'] = ['pages_cnt' => $pages_cnt, 'left_right_n' => $left_right_n];

    return $pager;
  }


  private function where_3_list_for_filter(){
    $out = [];
    $cnt = [];
    //$all = Product::find()->all();

    //$q = Product::find()

    $q = $this->source_class::find()
      ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
      ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
      ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ');

    //$q->andWhere(['p_all_compare.p_id' => null]);
    if (0 && $this->source_table_name === 'parser_trademarkia_com'){
      $q->andWhere(['like','info','add_info']);
      $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
      $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
    }
    $q->andWhere(['and',['hidden_items.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]]);
    $q->addGroupBy('`'.$this->source_table_name.'`.`id`');
    $all = $q->all();

    foreach ($all as $a_item){
      $c_root = $a_item->baseInfo['Categories: Root'];
      if (isset($cnt[$c_root])) $cnt[$c_root]++; else $cnt[$c_root] = 1;

      $out[] = $c_root;
    }
    //return array_unique($out);
    return $cnt;
  }

  private function where_4_list_for_filter(){
    $out = [];
    $all = User::find()
      ->where('status > 0')
      ->all();
    foreach ($all as $user){

      //$q = Product::find()

      $q = $this->source_class::find()
        ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
        ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
        ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
        ->where(['comparisons.user_id' => $user->id]);

      //$q->andWhere(['p_all_compare.p_id' => null]);
      $q->andWhere(['like','info','add_info']);
      $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
      $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
      $q->andWhere(['and',['hidden_items.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]]);
      $q->addGroupBy('`'.$this->source_table_name.'`.`id`');
      $c = $q->count();
      if (!$c) $c = 0;


      $cnt[$user->username] = ['id' => $user->id, 'username' => $user->username, 'cnt' => $c];
      $out[$user->id] = $user->username;

    }
    //return $out;
    return $cnt;

  }



  private function where_6_list_for_filter(){
    //$q = Product::find()

    $q = $this->source_class::find()
      ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
      ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
      ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ');
    $q->where(['or like', 'status', ['MATCH','%,MATCH,%','MATCH,%','%,MATCH'], false]);


    $q->andWhere(['and',['p_all_compare.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]]);
    if (0 && $this->source_table_name === 'parser_trademarkia_com') {
      $q->andWhere(['like', 'info', 'add_info']);
      $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
      $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
    }
    $q->andWhere(['and',['hidden_items.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]]);
    $q->addGroupBy('`comparisons`.`product_id`');

    $match = $q->count();
    //echo '<pre>'.PHP_EOL;
    //print_r($q->createCommand()->sql);
    //echo PHP_EOL;
    //exit;

    $q->where(['like', 'status', 'MISMATCH']);
    $mismatch = $q->count();

    $q->where(['like', 'status', 'PRE_MATCH']);
    $pre_match = $q->count();

    $q->where(['like', 'status', 'OTHER']);
    $other = $q->count();

    //$q = Product::find()

    $q = $this->source_class::find()
      ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
      ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
      ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ');
    $q->where(['and',['p_all_compare.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]]);

    if (0 && $this->source_table_name === 'parser_trademarkia_com') {
      $q->andWhere(['like', 'info', 'add_info']);
      $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
      $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
    }

    $q->andWhere(['and',['hidden_items.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]]);

    $q->addGroupBy('`'.$this->source_table_name.'`.`id`');
    $nocompare = $q->count();


    $out["NOCOMPARE"] = $nocompare;
    $out["MISMATCH"] = $mismatch;
    $out["PRE_MATCH"] = $pre_match;
    $out["MATCH"] = $match;
    $out["OTHER"] = $other;

    return $out;
  }

  public function is_source_access($user_id = false,$source_id = false){
    if (!$user_id){
      $user_id = \Yii::$app->getUser()->id;
    }
    if (!$source_id){
      $source_id = Source::get_source()['source_id'];
    }

    $u = User::find()->where(['id' => $user_id])->limit(1)->one();
    $res = $u->user__source_access;

    // если нет записей... то можно все источники
    if (!$res) return true;
    foreach ($res as $r){
      if ((int)$r->source_id === (int)$source_id) return true;
    }
    return false;
  }


  public function actionDel_item(){
    $p_id = $this->request->get('id');
    $source_id = $this->request->get('source_id');

    $source_class = $this->source_class;

    /* @var $source_class yii\db\ActiveRecord */
    $res = $source_class::findOne(['id' => $p_id]);

    if (1 && $this->source_table_name !== 'parser_trademarkia_com') {
      $sql = 'DELETE FROM `checker`.`'.$this->source_table_name_2.'` WHERE  `checker`.`'.$this->source_table_name_2.'`.`asin`="'.$res->asin.'";';
      $connection = Yii::$app->getDb();
      $command = $connection->createCommand($sql);
      $res_ = $command->query();
    }

    $res->delete();

// json
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
      'res' => 'ok',
    ];

  }
}