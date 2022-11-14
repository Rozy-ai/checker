<?php

namespace backend\controllers;

use backend\models\P_all_compare;
use common\helpers\AppHelper;
use common\models\HiddenItems;
use common\models\P_user_visible;
use Yii;
use common\models\Comparison;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\db\Expression;
use common\models\Comparison\Aggregated;
use yii\data\ActiveDataProvider;
use backend\presenters\IndexPresenter;
use backend\presenters\ProductPresenter;
use common\models\Source;
use common\models\User;
use common\models\Session;
use backend\models\Settings__fields_extend_price;
use common\models\Product;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller {

    /** @var IndexPresenter */
    public $indexPresenter;
    
    /** @var ProductPresenter */
    public $productPresenter;

    public $prev = null;
    public $next = null;

    /** @var Source Модель источника товаров. Устанавливается в beforeAction */
    private Source $source;

    /*

      public function behaviors(){

      $rules_3 = [];


      $rules_1 = ['allow' => true, 'actions' => ['index','get_products_by_params'], 'roles' => ['@'],];

      $rules_2 = [
      'allow' => true,
      'actions' => ['compare', 'view', 'result', 'missall', 'user_visible_fields','reset_compare','del_item','test1','get_products_by_params'],
      //'roles' => ['compare-products'],
      'roles' => ['@'],
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
      //      comparisons.messages_id = messages.id ?? settings__visible_all = 1
      //      where product_id = $id AND messages.settings__visible_all = 1

      $res = Comparison::can_compare($id,$this->indexService->getSource()->id);   //getSource() возможно null

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
     */

    /**
      public function behaviors()
      {
      return [
      'access' => [
      'class' => AccessControl::className(),
      'only' => ['login', 'logout', 'signup'],
      'rules' => [
      [
      'allow' => true,
      'actions' => ['login', 'signup'],
      'roles' => ['?'],
      ],
      [
      'allow' => true,
      'actions' => ['logout'],
      'roles' => ['@'],
      ],
      ],
      ],
      ];
      }
     *
     */

    /**
     * @inheritdoc
     * @param IndexService $indexService
     */
    public function __construct($id, $module,
            IndexPresenter $indexPresenter,
            ProductPresenter $productPresenter,
            array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->indexPresenter = $indexPresenter;
        $this->productPresenter = $productPresenter;
    }

    /**
     * До того как вызывать какое либо действие, нужно выбрать источник
     *
     * {@inheritdoc}
     * @throws \yii\web\ForbiddenHttpException
     */

    public function beforeAction($action) {

        $id_source = \Yii::$app->session[Session::id_source];
        $id_user   = \Yii::$app->user->id;

        $this->source = Source::getForUser($id_user, $id_source);

        if (!$this->source){
            throw new \yii\web\ForbiddenHttpException('Не удалось найти доступный источник');
        }

        if ($id_source !== $this->source->id){
            \Yii::$app->session->set(Session::id_source, $this->source->id);
        }

        return parent::beforeAction($action);
    }

    public function actionIndex() {

        $this->indexPresenter->setSource($this->source);
        $this->indexPresenter->loadFromParams(\Yii::$app->request->get());

        $list = $this->indexPresenter->getListProduct();
        $this->layout = 'products_list';
        $user = \Yii::$app->user->identity;
        $count_products_all = $this->indexPresenter->getCountProducts();
        $count_products_on_page = $this->indexPresenter->getCountProductsOnPage();
        $count_pages = $this->indexPresenter->getCountPages($count_products_all, $count_products_on_page);
        $current_page  = 1;
                
        $is_admin = $user && $user->isAdmin();
        
        return $this->render('index', [
            'source'           => $this->source,
            'list_source'      => Source::findAllSources($this->source->id, $user->id),

            'where_3_list' => $this->indexPresenter->getListCategoriesRoot(),
            'where_4_list' => $this->indexPresenter->getListUser(),

            'active_profiles' => $this->indexPresenter->getCurrentProfile(),
            'list_profiles'=> $this->indexPresenter->getListProfiles(),

            'active_comparison_status' => $this->indexPresenter->getCurrentComparisonStatus(),
            'list_comparison_statuses' => $this->indexPresenter->getListComparisonStatuses(),

            'last_update' => $this->indexPresenter->getLastLocalImport(),

            'list' =>$list,
            'count_products_all'      => $count_products_all,
            'count_products_on_page'  => min($count_products_on_page, $count_products_all),
            'count_products_right'    => $this->indexPresenter->getCountProductsOnPageRight($list),
            'pager'                   => $this->indexPresenter->getPager($count_pages, $current_page),

            'is_admin'                => $is_admin,
            'filter_is_detail_view'   => $this->indexPresenter->isDetailView(),

            'default_price_name'      => Settings__fields_extend_price::get_default_price($this->source->id)->name?: 'Price Amazon',
            'no_compare'              => false
        ]);
    }

    /**
     * Get next or prev product
     * @param $currentId int id product
     * @param bool $prev bool prev or next pfroduct
     * @param int $item_1__ignore_red
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getNextModel($currentId, $prev = false, $item_1__ignore_red = 0) {

        $model = $this->source_class::find()
                ->where([$prev ? '<' : '>', 'id', $currentId])
                ->andWhere(['like', 'info', 'add_info'])
                ->orderBy(['id' => $prev ? SORT_DESC : SORT_ASC]);
        if ((int) $item_1__ignore_red === 1) {

            $model->leftJoin('comparisons_aggregated', 'comparisons_aggregated.product_id = parser_trademarkia_com.id')
                    ->leftJoin('hidden_items', 'hidden_items.p_id = parser_trademarkia_com.id ')
                    ->andWhere(['and', ['`hidden_items`.p_id' => null], ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source_id]]]);
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

    private function do_it_need_to_update($source, $p_date_in_parser) {
        // [если дата в source]  меньше  [даты последнего товара(сортировка по дате) в базе парсера] → запускаем импорт

        if (!$source)
            return false;
        if (!$source['import_local__max_product_date'])
            $source['import_local__max_product_date'] = '0000-00-00 00:00:00';
        if (!$source['import_local__db_import_name'])
            return false;
        if (!$p_date_in_parser)
            return false;

        return $source['import_local__max_product_date'] < $p_date_in_parser;
    }

    private function start_import() {
        set_time_limit(60 * 5);
        $source_id = $this->source->id;
        $source = Source::get_source((int) $source_id);
        $p_date_in_parser = ImportController::get_max_product_date_in_parser($source);
        // [если дата в source]  меньше  [даты последнего товара(сортировка по дате) в базе парсера] → запускаем импорт
        if ($p_date_in_parser && $this->do_it_need_to_update($source, $p_date_in_parser)) {
            \Yii::$app->runAction('import/local_import', ['source_id' => (int) $source_id, 'p_date_in_parser' => $p_date_in_parser]);
            // статистика в $this->getView()->params['local_import_stat']
        }
    }
    
    public function actionView(){
        $this->layout = 'product';
        $this->productPresenter->setSource($this->source);
        $this->productPresenter->loadFromParams(\Yii::$app->request->get());
        
        $model = $this->productPresenter->getProduct();

        $prev = null;
        $next = null;
        
        //$compare_item = $this->productPresenter->getItemCompare($product->addInfo);
        $node = 1; //$this->productPresenter->number_node;
        $compare_item = AppHelper::get_item_by_number_key($model->addInfo, $node);
        $identity = \Yii::$app->user->identity;
        
        $active_comparison_status = $this->productPresenter->getCurrentComparisonStatus();
        $list_comparison_statuses = $this->productPresenter->getListComparisonStatuses();
        
        //Передаем параметры в шаблон
        $this->getView()->params = [
            'comparison_statuses_statistic' => $this->productPresenter->getListComparisonStatusesStatistic(),
            'active_comparison_status' => $active_comparison_status,
            'product' => $model,
            'source' => $this->source
        ];
        
        return $this->render('view', [
            'model' => $model,
            'compare_item' => $compare_item,
            'compare_items' => $model->addInfo,
            'source' => $this->source,
            'filter_comparisons' => $this->productPresenter->filters->f_comparisons,
            'filter-items__profile' => $this->productPresenter->filters->f_profile,
            'number_node' => $node,
            'is_admin' => $identity && $identity->isAdmin(),
            
            'active_comparison_status' => $active_comparison_status,
            'list_comparison_statuses' => $list_comparison_statuses,
        ]);
//        return $this->render('view', [
//            'prev' => $prev,
//            'next' => $next,
//            //'arrows' => $arrows,
//            'product' => $product,
//            'source' => $this->source,
//            'compare_item' => $compare_item,
//            'is_admin' => $identity && $identity->isAdmin()
//        ]);
    }

    public function actionView1($id) {

        // http://checker.loc/product/view?id=6369&direction=next&item_1__ignore_red=1
        // http://checker.loc/product/view?id=6369&direction=next
        // http://checker.loc/product/view?id=6369&item_1__ignore_red=1
        // todo /product/view?id=6369&item_1__ignore_red=0&direction=prev

        $this->layout = 'product';

        $f_items__source = $this->request->get('filter-items__source', 1);

        $direction = Yii::$app->request->get('direction', false);
        $item_1__ignore_red = Yii::$app->request->get('item_1__ignore_red', 0);
        $item_2__show_all = Yii::$app->request->get('item_2__show_all');

        $comparisons = $this->request->get('comparisons');
        // todo менять урл если пришли без comparisons

        if (!$comparisons) {
            $default = 'PRE_MATCH';
            // http://checker.loc/product/view?comparisons=PRE_MATCH&id=2351&source_id=2

            $get_array = Yii::$app->request->get();
            $_url = ['product/view'];
            $_url['comparisons'] = $default;

            return $this->redirect(array_merge($_url, $get_array));
        }



        if ($comparisons) {
            if ($comparisons === 'MATCH')
                $where_6 = ['or like', 'comparisons_aggregated.statuses', ['MATCH', '%,MATCH,%', 'MATCH,%', '%,MATCH'], false];
            if ($comparisons === 'MISMATCH')
                $where_6 = ['or like', 'comparisons_aggregated.statuses', 'MISMATCH'];
            if ($comparisons === 'PRE_MATCH')
                $where_6 = ['or like', 'comparisons_aggregated.statuses', 'PRE_MATCH'];
            if ($comparisons === 'OTHER')
                $where_6 = ['or like', 'comparisons_aggregated.statuses', 'OTHER'];

            // это когда
            if ($comparisons === 'YES_NO_OTHER') {
                $where_6 = ['and', "`comparisons`.`status` IS NOT NULL AND comparisons.`status` <> 'MISMATCH'"];
            }
            if ($comparisons === 'NOCOMPARE') {
                $no_compare = true;
                $where_6 = ['and', ['p_all_compare.p_id' => null], ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source_id]]];
            }
        }

        $_model = $this->source_class::find()
                ->select('*')
                ->leftJoin('comparisons_aggregated', 'comparisons_aggregated.product_id = ' . $this->source_table_name . '.id')
                ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source_table_name . '.id ')
                ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $this->source_table_name . '.id ')
                ->leftJoin('comparisons', 'comparisons.product_id = ' . $this->source_table_name . '.id ')

                //->where(['<=>','hidden_items.source_id', $this->source_id])
                ->where('comparisons_aggregated.source_id = ' . (int) $this->source_id)
                ->limit(1);

        $where_0 = [];
        if (0 && $this->source_table_name === 'parser_trademarkia_com') {
            $where_0 = ['like', 'info', 'add_info'];
        }

        $where_2 = [];
        if ($item_1__ignore_red) {
            // показывать только не скрытые
            $where_2 = ['and', ['hidden_items.p_id' => null], ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source_id]]];
        }

        if (0 && $direction) {

            $where = [];
            if ($direction === 'prev') {
                $where_1 = ['<', 'id', $id];
                $order = 'DESC';
                $_model->where($where);
            } else {
                $where_1 = ['>', 'id', $id];
                $order = 'ASC';
            }

            $where = ['and', $where_0, $where_1, $where_2];

            $_model->where($where)
                    ->orderBy('id ' . $order);

            $model = $_model->one();

            $url[] = 'product/view';
            $get_ = $this->request->get();
            $get_['id'] = $model->id;
            unset($get_['direction']);
            return $this->redirect(array_merge($url, $get_));
        } else {

            $where_1 = [];
            $where_3 = ['id' => $id];

            $where = ['and', $where_0, $where_1, $where_2, $where_3];

            $_model->where($where)
                    ->orderBy('id ASC');

            $model = $_model->one();

            /**/

            $arrows['left']['ignore_checked'] = $this->get_arrows($id, $_model, 'prev', 1);
            $arrows['left']['ignore_dont_checked'] = $this->get_arrows($id, $_model, 'prev', 0);

            $arrows['right']['ignore_checked'] = $this->get_arrows($id, $_model, 'next', 1);
            $arrows['right']['ignore_dont_checked'] = $this->get_arrows($id, $_model, 'next', 0);

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

            if (!$model) {
                $where_1 = ['>', 'id', $id];

                $where = ['and', $where_0, $where_1, $where_2];
                $_model->where($where)
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

        // В модель добавдяем дополнительную информацию
        $model->source_id = $this->indexService->getSource()->id;
        $model->baseInfo = $model->info;

        //$model = $this->findModel($id,$item_1__ignore_red,$direction);
        //$prev = $this->getNextModel($id, true,$item_1__ignore_red);
        //$next = $this->getNextModel($id,null, $item_1__ignore_red);
        // http://checker.loc/product/view?id=6369&direction=next&item_1__ignore_red=1

        $prev = null;
        $next = null;

        $node = Yii::$app->request->get('node', 1);

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
        if ($item_2__show_all) {
            $compare_item = AppHelper::get_item_by_number_key($add_info, $node);
            //$compare_items = $_add_info;
        } else {

            // убираем MISMATCH
            $a_without_mismatch = $compare_items = $this->remove_mismatch($add_info, $id);

            // выбираем ближайший node
            $compare_item = AppHelper::get_next_item_by_key($a_without_mismatch, $node);

            if (!isset($a_without_mismatch[$node - 1]) && $compare_item) {

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



        $_for_breadcrumbs = function ($list) {
            $out['label'] = 'profile_';
            $select[] = '<div class="product_page__filter-profile-wrapper">';
            $select[] = '<select name="" id="product_page__filter-profile" class="form-control">';
            foreach ($list as $k => $item) {
                $selected = $k === $this->request->get('filter-items__profile') ? 'selected' : '';
                $select[] = '<option value="' . $k . '" ' . $selected . ' >' . $item . '</option>';
            }
            $select[] = '</select>';
            $select[] = '</div>';

            $out['template'] = implode('', $select);
            return $out;
        };

        $profile = $this->request->get('filter-items__profile');
        $this->getView()->params['filter_statuses'] = $this->indexService->cnt_filter_statuses($profile);

        $source = Source::get_source($this->source_id);
        $this->getView()->params['breadcrumbs'][] = ['label' => Yii::t('site', 'Products'), 'url' => ['index']];
        $this->getView()->params['breadcrumbs'][] = ['label' => Yii::t($source['source_name'], $source['source_name']), 'url' => ['index', 'filter-items__source' => $source_id]];
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

    public function actionResult($id) {
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
    public function isStatusSet($id) {
        $model = $this->findModel($id);
        foreach (array_values($model->addinfo) as $index => $node) {
            if (Comparison::findOne(['product_id' => $id, 'node' => $index]) === null) {
                return $index;
            }
        }
        return true;
    }

    public function actionReset_compare($id) {
        $p_id = Yii::$app->request->get('id');
        $source_id = Yii::$app->request->get('source_id');

        Comparison::deleteAll(['product_id' => $p_id, 'source_id' => $source_id]);
        HiddenItems::deleteAll(['p_id' => $p_id, 'source_id' => $source_id]);
        P_all_compare::deleteAll(['p_id' => $p_id, 'source_id' => $source_id]);

        // json
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'res' => 'ok',
        ];
    }

    /**
     * Сюда приходит после нажатия крестика на правом товаре
     */
    public function actionCompare() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (\Yii::$app->request->isGet){
            $params = \Yii::$app->request->get();
        } elseif (\Yii::$app->request->isPost){
            $params = \Yii::$app->request->post();
        }
                
        $id_product = (int)$params['id_product'];
        $id_item    = (int)$params['id_item'];
        $id_source  = (int)$params['id_source'];

        // На входе подается переменная source_id.
        // И в сесии у нас есть source_id
        // По идее эти данные должны совпадать. Если будет работать то на вход source_id можно не передавать c jquery        
        if ($this->source->id !== $id_source){
            throw new InvalidArgumentException('id источника не совпадает');
        }
        
        return $this->indexPresenter->setStatusProductRight($id_product, $id_item, $id_source, Comparison::STATUS_MISMATCH);
    }

    /**
     * Сюда приходит, если пользователь нажал крестик на левом товаре
     * Запрос get/post
     * @return type
     */
    public function actionMissall() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (\Yii::$app->request->isGet){
            $params = \Yii::$app->request->get();
        } elseif (\Yii::$app->request->isPost){
            $params = \Yii::$app->request->post();
        }
  
        if (!$params['id_product'] || !$params['id_source'] || !$params['url']){
            throw new InvalidArgumentException();
        }
        
        $url        = $params['url'];
        $id_product = (int)$params['id_product'];
        $id_source  = (int)$params['id_source'];
        $confirm_to_action = (bool)$params['confirm'];
        
        // На входе подается переменная source_id.
        // И в сесии у нас есть source_id
        // По идее эти данные должны совпадать. Если будет работать то на вход source_id можно не передавать c jquery
        if ($this->source->id !== $id_source){
            throw new InvalidArgumentException('id источника не совпадает');
        }
        
        $result = $this->indexPresenter->missmatchToAll($this->source->class_1, $url, $id_product, $id_source, $confirm_to_action);
        return $result;
    }

    private function get_model_for_next($id) {
        $f_items__source = $this->request->get('filter-items__source', 1);
        $direction = Yii::$app->request->get('direction', false);
        $item_1__ignore_red = Yii::$app->request->get('item_1__ignore_red', 0);
        $item_2__show_all = Yii::$app->request->get('item_2__show_all');

        $_model = $this->source_class::find()
                ->select('*')
                ->leftJoin('comparisons_aggregated', 'comparisons_aggregated.product_id = ' . $this->source_table_name . '.id')
                ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source_table_name . '.id ')
                //->where(['<=>','hidden_items.source_id', $this->source_id])
                ->where('comparisons_aggregated.source_id = ' . (int) $this->source_id)
                ->limit(1);

        $where_0 = [];
        if (0 && $this->source_table_name === 'parser_trademarkia_com') {
            $where_0 = ['like', 'info', 'add_info'];
        }
        $where_2 = [];
        if ($item_1__ignore_red) {
            // показывать только не скрытые
            $where_2 = ['and', ['hidden_items.p_id' => null], ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source_id]]];
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

        $where = ['and', $where_0, $where_1, $where_2];

        $_model->where($where)
                ->orderBy('id ' . $order);

        $model = $_model->one();

        $url[] = 'product/view';
        $get_ = $this->request->get();
        $get_['id'] = $model->id;
        unset($get_['direction']);

        return $this->redirect(array_merge($url, $get_));
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @param $item_1__ignore_red
     * @param $direction
     * @return array|\yii\db\Act iveRecord
     */
    protected function findModel($id, $item_1__ignore_red = 0, $direction = 'next') {
        $model = null;
        if ((int) $item_1__ignore_red === 1) {
            if (HiddenItems::find()->where(['p_id' => $id, 'source_id' => $this->source_id])->limit(1)->one()) {
                if ($direction === 'prev')
                    $symbol = '<=';
                else
                    $symbol = '>';

                $model = $this->source_class::find()
                        ->select('*')
                        ->leftJoin('comparisons_aggregated', 'comparisons_aggregated.product_id = ' . $this->source_table_name . '.id')
                        ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source_table_name . '.id ')
                        ->where(['and', ['`hidden_items`.p_id' => null], [$symbol, '`' . $this->source_table_name . '`.`id` ', $id]])
                        ->andWhere(['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source_id]]);

                if (0 && $this->source_table_name === 'parser_trademarkia_com') {
                    $model->andWhere(['like', 'info', 'add_info']);
                }

                $model = $model->limit(1)->one();
            }
        } else {
            $model = $this->source_class::find()
                    ->with('comparisons')
                    ->where(['id' => $id]);
            $model = $model->one();
        }

        return $model;
        //throw new NotFoundHttpException(Yii::t('site', 'The requested page does not exist.'));
    }

    public function actionUser_visible_fields() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $pid = $this->request->post('pid');

        if (!User::isAdmin())
            return false;

        $puv = P_user_visible::findOne(['p_id' => $pid]);

        if ($puv) {
            $puv->delete();
            return [
                'res' => 'ok',
                'text' => 'Показать поля (*) пользователю'
            ];
        } else {
            $puv = new P_user_visible();
            $puv->p_id = $pid;
            $puv->save();
            return [
                'res' => 'ok',
                'text' => 'Скрыть поля (*) для пользователя'
            ];
        }
    }

    public function actionGet_products_by_params() {
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

        if (!$p_id || !$comparison || !$source_id)
            return ['res' => 'fuck'];

        /* start */
        $q = $this->prepare_record_1($comparison, $profile);

//    if ($direction === 'prev' || $direction === 'next'){  }

        $prev = $this->prepare_2(clone $q, ['<', $this->source_table_name . '.id', $p_id], 'DESC', $comparison, 'prev_items', 5);
        $next = $this->prepare_2(clone $q, ['>', $this->source_table_name . '.id', $p_id], 'ASC', $comparison, 'next_items', 5);
        $this_item = $this->prepare_2($this->prepare_get_joined($this->source_class), [$this->source_table_name . '.id' => $p_id], 'ASC', $comparison, 'this_item', 1);

        /* RIGHT ITEMS */
        $q_this_item = $this->prepare_get_joined($this->source_class);
        $q_this_item->andWhere([$this->source_table_name . '.id' => $p_id]);
        $res = $q_this_item->one();

        $ignore = [];
        if ($comparison === 'NOCOMPARE')
            $ignore = ['PRE_MATCH', 'MATCH', 'OTHER', 'MISMATCH']; // nocompare
        if ($comparison === 'YES_NO_OTHER')
            $ignore = ['NOCOMPARE']; // result
        if ($comparison === 'MATCH')
            $ignore = ['PRE_MATCH', 'OTHER', 'NOCOMPARE', 'MISMATCH']; // match
        if ($comparison === 'MISMATCH')
            $ignore = ['PRE_MATCH', 'MATCH', 'OTHER', 'NOCOMPARE']; // mismatch
        if ($comparison === 'PRE_MATCH')
            $ignore = ['MATCH', 'OTHER', 'MISMATCH', 'NOCOMPARE']; // pre_match
        if ($comparison === 'OTHER')
            $ignore = ['PRE_MATCH', 'MATCH', 'MISMATCH', 'NOCOMPARE']; // other

        $right_items = $res->get_right_items($ignore);

        $fill_right_item = function ($item, $node_id, $parent_id) {

            $r_title = \backend\models\Settings__source_fields::name_for_source('r_Title');
            if ($r_title)
                $_item['text_brief'] = Html::encode($item->r_Title);

            $_item['id'] = $item->id;
            $_item['asin'] = $item->asin;
            $_item['img_main'] = $item->get_first_image();
            $_item['status'] = $item->get_status();
            //   /product/view?id=8009&node=1&source_id=1&comparisons=PRE_MATCH
            $_item['link'] = '/product/view?id=' . $parent_id . '&source_id=' . (int) $this->source_id . '&comparisons=' . $this->request->post('comparison') . '&profile=' . $this->request->post('profile');

            return $_item;
        };

        foreach ($right_items as $node_id => $right_item) {
            $out_right_items[$node_id] = $fill_right_item($right_item, $node_id, $p_id);
        }

        $this_item['items']['this_item_right_items'] = $out_right_items;
        /* .RIGHT ITEMS */

        $out = array_merge_recursive($this_item, $prev, $next);
        $out['comparison_cnt'] = $this->cnt_filter_statuses($profile);
        ;

        return $out;

        if ($all) {

        } else
            return ['res' => 'fuck',];

        if ($model) {
            return [
                'res' => 'ok',
                'link' => '/product/view?id=' . $model->id . '&source_id=' . (int) $this->source_id . '&comparisons=' . $comparison,
            ];
        } else
            return ['res' => 'fuck',];
    }

    private function prepare_2($q, $where, $order, $comparison, $direction, $limit = false) {
        if ($where)
            $q->andWhere($where);
        if ($order)
            $q->orderBy($this->source_table_name . '.id ' . $order);

        if ($limit)
            $q->limit($limit);
        //$model = $q->one();
        $sql = $q->createCommand()->getRawSql();
        $all = $q->all();

        $fill_item = function ($item, $source_id, $comparison) {
            $_item['id'] = $item->id;
            $_item['asin'] = $item->asin;
            $_item['img_main'] = $item->get_img_main();
            $_item['link'] = '/product/view?id=' . $item->id . '&source_id=' . (int) $source_id . '&comparisons=' . $comparison;

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

    private function get_arrows($id, $_model, $direction, $item_1__ignore_red) {
        $where_0 = [];
        if (0 && $this->source_table_name === 'parser_trademarkia_com') {
            $where_0 = ['like', 'info', 'add_info'];
        }

        $where_1 = [];
        $where_2 = [];
        if ($item_1__ignore_red)
            $where_2 = ['and',
                ['hidden_items.p_id' => null],
                ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source_id]],
            ];  // $item_1__ignore_red = 1

        if ($direction === 'prev') {
            $where_1 = ['<', 'id', $id];
            $order = 'DESC';
        } else {
            $where_1 = ['>', 'id', $id];
            $order = 'ASC';
        }

        $where = ['and', $where_0, $where_1, $where_2];
        $_model->where($where)
                ->orderBy('id ' . $order);

        $model = $_model->one();

        if ($model)
            return '/product/view?id=' . $model->id . '&source_id=' . (int) $this->source_id . '&item_1__ignore_red=' . $item_1__ignore_red . '&direction=' . $direction;
        else
            return null;
    }

    private function remove_mismatch($add_info, $id) {
        $out = [];
        foreach ($add_info as $k => $a_info) {
            $res = Comparison::findOne(['product_id' => $id, 'node' => $k, 'source_id' => $this->source_id]);
            if (!$res)
                $out[$k] = $a_info;
            else {
                if ($res->status !== 'MISMATCH')
                    $out[$k] = $a_info;
            }
        }
        return $out;
    }

    private function simple_pager($pages_cnt, $page_n, $left_right_n = 3) {
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
        $from = $page_n - $left_right_n; //1
        $add_to_v2 = 0;
        if ($from <= 0) {
            $add_to_v2 = abs($from) + 1; //2
            $from = 1;
        }
        $to = $page_n + $left_right_n + $add_to_v2;

        if ($pages_cnt < $to) {
            //$to = $pages_cnt;
            $add_to_from = $pages_cnt - $to;
            $from = $page_n - $left_right_n + $add_to_from;
            if ($from < 1)
                $from = 1;
        }

        if ($from > $pages_cnt && $from > $to)
            $from = 1;
        if ($to > $pages_cnt)
            $to = $pages_cnt;

        $pager['from'] = $from;
        $pager['to'] = $to;
        $pager['this_n'] = $page_n;
        $pager['in'] = ['pages_cnt' => $pages_cnt, 'left_right_n' => $left_right_n];

        return $pager;
    }

    public function actionDel_item() {
        $p_id = $this->request->get('id');
        $source_id = $this->request->get('source_id');

        $source_class = $this->source_class;

        /* @var $source_class yii\db\ActiveRecord */
        $res = $source_class::findOne(['id' => $p_id]);

        if (1 && $this->source_table_name !== 'parser_trademarkia_com') {
            $sql = 'DELETE FROM `checker`.`' . $this->source_table_name_2 . '` WHERE  `checker`.`' . $this->source_table_name_2 . '`.`asin`="' . $res->asin . '";';
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
