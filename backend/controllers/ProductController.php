<?php

namespace backend\controllers;

use common\models\UserIdentity;
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
use backend\services\IndexService;
use common\models\Source;
use common\models\User;
use backend\services\Session;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller {

    /** @var IndexService */
    public $indexService;
    
    /** @var MySession */
    //public $session;
    
    public $prev = null;
    public $next = null;
    
    /** @var Source Модель источника товаров. Устанавливается в beforeAction */
    private $source = null;

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
            IndexService $indexService,
            array $config = [])
    {
        parent::__construct($id, $module, $config);
        //$this->session = $session;
        $this->indexService = $indexService;
    }
    
    /**
     * До того как вызывать какое либо действие, нужно выбрать источник
     * @param type $action
     * @return type
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
            \Yii::$app->session[Session::id_source] = $this->source->id;
        }
        
        /** @var User */
        $user = \Yii::$app->user->identity;
        if ($user) {
            $this->source = $user->getSource($source_id);
        } else {
            $this->source = Source::getById(1);
        }
        
        // Если пользователь зарегистрирован, но просто нет доступа к этому источнику, 
        // то даем ему шанс и пробуем найти для него другой доступный платный источник
        if ($user && !$this->source){
            $sources = $user->getSources();
            if (is_array($sources)) {
                $this->source = $sources[0];
            }
        }

        if (!$this->source){
            // Если пользователь зарегистрирован, но просто нет доступа к этому источнику, 
            // то даем ему шанс и пробуем найти для него другой доступный источник
            if ($user) {
                $sources = $user->getSources();
                //Если нашелся доступный:
                if (is_array($sources)) {
                    $this->source = $sources[0];
                }
            }
            
            // Если опять нету, пробуем ему бесплатный сточник
            if (!$this->source) {
                $sources = Source::getSourcesFree();
                if (is_array($sources)){
                    $this->source = $sources[0];
                }
                // Если и тут нету то тут уж ничего не поделаешь
                if (!$this->source){
                    throw new \yii\web\ForbiddenHttpException('Нет доступных источников');
                }
            }
            
            $url[] = 'product/index';
            $get_ = $this->request->get();
            $get_['filter-items__source'] = $this->source->id;
            $get_['source_id'] = $this->source->id;
            return $this->redirect(array_merge($url, $get_));
        }

        return parent::beforeAction($action);
    }

    public function actionIndex() {
        ini_set("memory_limit", "3024M");

        $this->indexService->loadParams(\Yii::$app->request->getQueryParams());
        $page_n = (int) $this->request->get('page', 0);
        
        if (isAdmin() && !$this->indexService->getFilterItemsComparisons()) {
            $no_compare = false;
            $f_items__comparisons = 'YES_NO_OTHER';
            $get_array = Yii::$app->request->get();
            $_url = ['product/index'];
            if ($page_n === 0) {
                $_url['page'] = 1;
            }
            $_url['filter-items__comparisons'] = "YES_NO_OTHER";
            return $this->redirect(array_merge($get_array, $_url));
        }

        if (!User::isAdmin() && !$this->indexService->getFilterItemsComparisons()) {
            $no_compare = true;
            $f_items__comparisons = 'NOCOMPARE';
            $get_array = Yii::$app->request->get();
            $_url = ['product/index'];
            if ($page_n === 0)
                $get_array['page'] = 1;
            $get_array['filter-items__comparisons'] = "NOCOMPARE";
            if ($page_n === 0)
                $_url['page'] = 1;
            $_url['filter-items__comparisons'] = "NOCOMPARE";
            return $this->redirect(array_merge($get_array, $_url));
        }

        // Если $page_n(берет значение из Get запроса 'page') то переходим на страницу 1
        $get_array = Yii::$app->request->get();
        $_url[] = 'product/index';
        $get_array['page'] = 1;
        $url_construct = array_merge($_url, $get_array);
        $res_url = Url::toRoute($url_construct);
        if ($page_n === 0)
            return $this->redirect($res_url);

        $on_page_str = null;

        $where_3_list = $this->indexService->getWhere_3_list();
        $where_4_list = $this->indexService->getWhere_4_list();
        $where_6_list = $this->indexService->getWhere_6_list();

        //$this->start_import(); // ???    
        $cnt_all = $this->indexService->getCountProducts();

        $list = $this->indexService->getProducts();

        $pager = $this->indexService->getPager($cnt_all);

        $this->layout = 'products_list';

        //$searchModel = new ProductSearch();
        //$params = $this->request->queryParams;
        //$data = &$params[$searchModel->formName()];
        //if (\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) === null && empty($data['user'])) {
        //  $params ['user'] = \Yii::$app->user->identity->username;
        //  $params ['unprocessed'] = true;
        //}
        //$dataProvider = $searchModel->search($params);

        $profiles_list = $this->indexService->profiles_list_cnt_2();
        //$profiles_list = $this->indexService->profiles_list_cnt(); Не понятно

        $this->getView()->params['filter_statuses'] = $this->indexService->cnt_filter_statuses($this->request->get('filter-items__profile'));
        if ($this->indexService->getFilterItemsComparisons() === 'NOCOMPARE') {
            $no_compare = true;
        }

        $last_update = $this->indexService->get_last_local_import();

        return $this->render('index', [
                    'get_' => $this->request->get(),
                    'searchModel' => null, //$searchModel,
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
                    'is_admin' => User::isAdmin(),
                    'no_compare' => $no_compare,
                    'pager' => $pager,
                    'sort' => $sort,
                    'right_item_show' => $this->indexService->getItemsRightItemShow() ? 1 : 0,
                    'last_update' => $last_update,
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

    public function actionView($id) {

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

    public function actionCompare($id) {
        // http://checker.loc/product/compare?id=6377&node=2&status=MATCH
        //$id = Yii::$app->request->get('id');
        $node = Yii::$app->request->get('node') - 1;
        $is_list_page = Yii::$app->request->get('list', 0);

        $model = $this->findModel($id);
        if (!$model) {
            //echo '<pre>'.PHP_EOL;
            print_r('не нашел такого товара');
            //echo PHP_EOL;
            exit;
        }
        $model->initAddInfo();

        $comparisonModel = Comparison::findOne(['product_id' => $id, 'node' => $node, 'source_id' => $this->source_id]);

        $nodes = array_values($model->addInfo);
        if ($comparisonModel === null && isset($nodes[$node])) {
            $comparisonModel = new Comparison(['product_id' => $model->id, 'source_id' => $this->source_id, 'user_id' => Yii::$app->user->id, 'node' => $node]);
        }
        if ($comparisonModel === null) {
            throw new NotFoundHttpException(Yii::t('yii', 'Не найдена модель сравнения'));
        }

        //$m_id = Yii::$app->request->get('msgid') ?: -1 ;
        $m_id = Yii::$app->request->get('msgid');

        $comparisonModel->setStatus(
                Yii::$app->request->get('status'),
                $m_id,
                array_keys($model->addInfo)[$node], $id
        );

        $comparisonModel->save();

        $a_url = parse_url($this->request->referrer);
        $part_1 = $a_url['path']; // /product/view
        //print_r($part_1);

        $get_array = Yii::$app->request->get();
        $_url[] = $part_1;
        $get_array['node'] = $node + 1;
        unset($get_array['status']);
        $url_construct = array_merge($_url, $get_array);
        $res_url = Url::toRoute($url_construct);

        if ($this->request->get('return', 0) && Yii::$app->request->get('status') === 'MISMATCH') {
            return $this->redirect($this->request->referrer);
        }

        //if (!$this->request->get('load_next',0) && Yii::$app->request->get('status') === 'MISMATCH') {
        if (Yii::$app->request->get('status') === 'MISMATCH') {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            return ['status' => 'ok'];
        }

        if ((int) Yii::$app->request->get('list') === 1 && Yii::$app->request->get('status') === 'MATCH') {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['status' => 'ok'];
        }

        if ((int) Yii::$app->request->get('list') === 1 && Yii::$app->request->get('status') === 'PRE_MATCH') {
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

            if ($this->request->get('ignore-right-hidden')) {
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
                        ->where(['product_id' => $id, 'status' => 'MISMATCH', 'source_id' => $this->source_id])
                        ->all();

                $out_1 = [];
                if ($res_1) {
                    foreach ($res_1 as $r_1) {
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
                $res_2 = array_diff($right_ids, $out_1);

                $res_3 = null;
                $cnt = 0;
                $first = null;
                foreach ($res_2 as $r2) {
                    if ($cnt === 0)
                        $first = $r2;
                    $cnt++;
                    if ($r2 > $node) {
                        $res_3 = $r2;
                        break;
                    }
                }

                if (empty($res_3))
                    $res_3 = $first - 1;
                if (!empty($res_3))
                    return $this->redirect(['view', 'id' => $id, 'node' => $res_3 + 1, 'source_id' => $this->source_id]);
            }

            return $this->redirect(['view', 'id' => $id, 'node' => $node + 1, 'source_id' => $this->source_id]);
        } else {

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['status' => 'OK'];
        }
    }

    public function actionMissall() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = $this->request->post('id');
        if (!$id) {
            $id = $this->request->get('id');
            if (!$id) {
                echo '<pre>' . PHP_EOL;
                print_r('не указан id');
                echo PHP_EOL;
                exit;
            }
        }

        $model = $this->findModel($id);

        $nodes = array_values($model->addInfo);
        $urls = array_keys($model->addInfo);

        if ($nodes) {
            foreach ($nodes as $node => $addInfo) {
                $comparisonModel = Comparison::findOne(['product_id' => $id, 'node' => $node, 'source_id' => $this->source_id]);

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
        } else {
            $comparisonModel = Comparison::findOne(['product_id' => $id, 'node' => -1, 'source_id' => $this->source_id]);
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

            $find = HiddenItems::find()->where(['p_id' => $id, 'source_id' => $this->source_id])->one();
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
        if (isset($_SERVER['HTTP_REFERER'])) {
            $parse_url = parse_url($_SERVER['HTTP_REFERER']);
            $url = array_merge($url, $parse_url);
            if (isset($parse_url['query'])) {
                $query = $parse_url['query'];
                parse_str($query, $get_);
                $url['get'] = $get_;
            }
        }

        if (!$this->request->isPost) {
            if ($url['path'] === '/product/view') {
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
     * @return array|\yii\db\ActiveRecord
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

    //public function actionUser_visible_fields(){
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
