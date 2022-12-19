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
use common\models\Filters;
use common\models\Stats_import_export;
use common\models\Product_right;

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
    //private Source $source;

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
            array $config = []) {
        parent::__construct($id, $module, $config);
        $this->indexPresenter = $indexPresenter;
        $this->productPresenter = $productPresenter;
    }

    public function actionIndex($src = null) {
        $params = $params = \Yii::$app->request->get();
        
        $filters = new Filters();
        $filters->loadFromSession();  
        $source = null;
        // Если страница загружвется в первый раз, то будут отсутствовать обязательные параметры
        if ($filters->isExistsDefaultParams()) {
            $source = Source::getById($src ? $src : $filters->f_source);
            
            //  Если в запросе указан номер страницы, то установим его:
            if (isset($params['page'])){
                $number_page_current = (int)$params['page'];
                if ($number_page_current <= 0 ){
                    throw new InvalidArgumentException('Указан не верный номер страницы');
                }
                $filters->setVsSession('f_number_page_current', $number_page_current);
                $this->redirect('/product/index');
            }
        } else {
            // Если страница загружается в первый раз то номер страницы нафиг не нужен, ибо по умолчанию установится в 1
            $id_user = \Yii::$app->user->id;
            $source = Source::getForUser($id_user, $src);

            if (!$source) {
                throw new \yii\web\ForbiddenHttpException('Не удалось найти доступный источник');
            }

            $filters->setToDefault();
            $filters->f_source = $source->id;
            $filters->saveToSession();
        }

        $this->indexPresenter->setSource($source);

        $this->layout = 'products_list';
        $user = \Yii::$app->user->identity;
        $is_admin = $user && $user->isAdmin();
        $list = Product::getListProducts($source, $filters, $is_admin);
        $count_products_all = Product::getCountProducts($source, $filters, $is_admin);
        $count_pages = $this->indexPresenter->getCountPages($count_products_all, $filters->f_count_products_on_page);

        return $this->render('index', [
            'f_source' => $src ? $src : $filters->f_source,
            'f_profile' => $filters->f_profile,
            'f_count_products_on_page' => $filters->f_count_products_on_page,
            'f_number_page_current' => $filters->f_number_page_current,
            'f_asin' => $filters->f_asin,
            'f_title' => $filters->f_title,
            'f_status' => $filters->f_status,
            'f_username' => $filters->f_username,
            'f_comparison_status' => $filters->f_comparison_status,
            'f_sort' => $filters->f_sort,
            'f_detail_view' => $filters->f_detail_view,
            'f_categories_root' => $filters->f_categories_root,
            'f_batch_mode' => $filters->f_batch_mode,
            'f_hide_mode' => $filters->f_hide_mode,
            'f_no_compare' => true,

            'list_source' => $this->indexPresenter->getListSource(),
            'list_profiles' => $this->indexPresenter->getListProfiles(),
            'list_count_products_on_page' => $this->indexPresenter->getListCountProductsOnPage(),
            'list_categories_root' => $this->indexPresenter->getListCategoriesRoot(),
            'list_username' => $this->indexPresenter->getListUser(),
            'list_comparison_statuses' => $this->indexPresenter->getListComparisonStatuses($is_admin, $filters->f_profile),
            'list' => $list,
            
            'count_products_all' => $count_products_all,
            'count_products_right' => $this->indexPresenter->getCountProductsOnPageRight($list),
            'count_pages' => $count_pages,
            'is_admin' => $is_admin,
            'default_price_name' => Settings__fields_extend_price::get_default_price($source->id)->name ?: 'Price Amazon',
            
            'source' => $source,
            'last_update' => Stats_import_export::getLastLocalImport()
        ]);
    }

    /*
     * Временная экспериментальная функция для замены node на id_product (Не используется)
     */
    public function actionStart() {
        $source = Source::getById(1);
        if (!($source instanceof Source)) {
            echo "Не удалось найти источник";
            return 1;
        }

        $filters = new Filters();
        $filters->setToDefault();
        $filters->f_source = $source->id;

        $products = Product::getListProducts($source, $filters, true);
        if (!is_array($products) || !count($products)) {
            echo "Не удалось получить список продуктов";
            return 1;
        }

        $all = count($products);
        $k = 100 / $all;
        if ($k == 0) {
            echo "Нет товаров для преобразования";
            return 1;
        }

        $count_rebased = 0;
        foreach ($products as $i => $product) {
            $items = $product->addInfo;
            foreach ($items as $index => $item) {
                $item->source = $source;
                print_r($item->id);
                exit;
            }
        }
        echo "count rebased = " . $count_rebased;
        return 0;
    }

    /**
     * Изменение фильтра и отображение нового списка продуктов
     * @return type
     */
    public function actionChangeFilter() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = \Yii::$app->request->post();
        if (isset($request)) {
            $name = $request['name'];
            $value = $request['value'];
            $data_comparisons = $request['data_comparisons'];
        }
        if (!isset($name)) {
            return [
                'status' => 'error',
                'message' => 'Не удаось получить изменяемый фильтр',
            ];
        }
        
        // Если с запросом прищли данные сравнений, 
        if ($data_comparisons['datas_products_left'] || $data_comparisons['datas_products_right']){
            $is_comare_all = $this->indexPresenter->changeStatusProducts($data_comparisons['datas_products_left'], $data_comparisons['datas_products_right']);
        }

        $filters = new Filters();
        $filters->loadFromSession();
        if (!$filters->isExistsDefaultParams()) {
            throw new \InvalidArgumentException('В сессии не хватает данных');
        }

        if (!property_exists($filters, $name)) {
            return [
                'status' => 'error',
                'message' => 'Не верный изменяемый ключ'
            ];
        }
        $a = $name;
        if ($filters->$name == $value) {
            return [
                'status' => 'info',
                'message' => 'Новое значение фильтра совпадает с предыдущим'
            ];
        }

        $filters->setVsSession($name, $value);

        $source = Source::getById($filters->f_source);
        $user = \Yii::$app->user->identity;
        $is_admin = $user && $user->isAdmin();

        return $this->getRequestWithUpdateList($source, $filters, $is_admin, true, $is_comare_all);
    }

    /**
     *  Сюда приходит после нажатия на снопку сбросить для левого товара
     *  Требуется перерисовка списка продуктов
     */
    public function actionResetCompare() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (\Yii::$app->request->isGet) {
            $params = \Yii::$app->request->get();
        } elseif (\Yii::$app->request->isPost) {
            $params = \Yii::$app->request->post();
        }

        $id_product = (int) $params['id_product'];
        $id_source = (int) $params['id_source'];

        if (!$id_product || !$id_source) {
            return [
                'status' => 'error',
                'message' => 'Не хватает исходных данных',
            ];
        }

        try {
            $this->indexPresenter->ResetCompareProduct($id_source, $id_product);
        } catch (\Exception $ex) {
            return [
                'status' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        $filters = new Filters();
        $filters->loadFromSession();
        if (!$filters->isExistsDefaultParams()) {
            return [
                'status' => 'error',
                'message' => 'В сесии не хватает данных'
            ];
        }

        $source = Source::getById($filters->f_source);
        $user = \Yii::$app->user->identity;
        $is_admin = $user && $user->isAdmin();

        return $this->getRequestWithUpdateList($source, $filters, $is_admin);
    }

    /**
     * Сюда приходит после нажатия крестика на правом товаре
     */
    public function actionCompare() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (\Yii::$app->request->isGet) {
            $params = \Yii::$app->request->get();
        } elseif (\Yii::$app->request->isPost) {
            $params = \Yii::$app->request->post();
        }

        $status = (string) $params['status'];
        $id_product = (int) $params['id_product'];
        $id_item = (int) $params['id_item'];
        $id_source = (int) $params['id_source'];
        $message = (string) $params['message'];
        $is_last = (bool) $params['is_last'];

        if (!$status || !$id_product || !$id_item || !$id_source) {
            return [
                'status' => 'error',
                'message' => 'Не хватает исходных данных'
            ];
        }

        try {
            $this->indexPresenter->changeStatusProductRight($status, $id_source, $id_product, $id_item, $message, $is_last);
        } catch (\Exception $ex) {
            return [
                'status' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        $filters = new Filters();
        $filters->loadFromSession();
        if (!$filters->isExistsDefaultParams()) {
            return [
                'status' => 'error',
                'message' => 'В сесии не хватает данных'
            ];
        }

        $source = Source::getById($filters->f_source);
        $user = \Yii::$app->user->identity;
        $is_admin = $user && $user->isAdmin();

        return $this->getRequestWithUpdateList($source, $filters, $is_admin);
    }
    
    public function actionCompareBatch(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (\Yii::$app->request->isGet) {
            $params = \Yii::$app->request->get();
        } elseif (\Yii::$app->request->isPost) {
            $params = \Yii::$app->request->post();
        }
        
        $list_product_right = $params['list_products_right'];
        if (!is_array($list_product_right)){
            return [
                'status' => 'info',
                'message' => 'нет элементов для сравнения'
            ];            
        }
        
        try{
            $this->indexPresenter->changeStatusProductsRight($list_product_right);
        } catch (\Exception $ex) {
            return [
                'status' => 'error',
                'message' => $ex->getMessage()
            ];
        }
        
        return $this->getRequestWithUpdateList();
    }

    /**
     * Сюда приходит, если пользователь нажал крестик на левом товаре
     * Запрос get/post
     * @return type
     */
    public function actionMissall() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (\Yii::$app->request->isGet) {
            $params = \Yii::$app->request->get();
        } elseif (\Yii::$app->request->isPost) {
            $params = \Yii::$app->request->post();
        }

        if (!$params['id_product'] || !$params['id_source'] || !$params['url']) {
            //throw new InvalidArgumentException();
            return [
                'status' => 'error',
                'message' => 'Не верные входящие переменные'
            ];
        }

        $url = $params['url'];
        $id_product = (int) $params['id_product'];
        $id_source = (int) $params['id_source'];
        $confirm_to_action = (bool) $params['confirm'];

        try {
            if (!$this->indexPresenter->missmatchToAll($url, $id_product, $id_source, $confirm_to_action)) {
                return [
                    'status' => 'have_match',
                    'message' => 'У даннного продукта имеются товары со статутом Match/Prematch которые будут изменены на Missmatch. Продолжить?'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        $filters = new Filters();
        $filters->loadFromSession();
        if (!$filters->isExistsDefaultParams()) {
            return [
                'status' => 'error',
                'message' => 'В сесии не хватает данных'
            ];
        }
        $source = Source::getById($id_source);
        $user = \Yii::$app->user->identity;
        $is_admin = $user && $user->isAdmin();

        return $this->getRequestWithUpdateList($source, $filters, $is_admin);
    }

    public function actionDeleteProduct() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (\Yii::$app->request->isGet) {
            $params = \Yii::$app->request->get();
        } elseif (\Yii::$app->request->isPost) {
            $params = \Yii::$app->request->post();
        }

        $id_source = (int) $params['id_source'];
        $id_product = (int) $params['id_product'];

        try {
            $this->indexPresenter->deleteProduct($id_source, $id_product);
        } catch (\Exception $ex) {
            return [
                'status' => 'error',
                'message' => $ex->message
            ];
        }

        $filters = new Filters();
        $filters->loadFromSession();
        if (!$filters->isExistsDefaultParams()) {
            return [
                'status' => 'error',
                'message' => 'В сесии не хватает данных'
            ];
        }

        $source = Source::getById($filters->f_source);
        $user = \Yii::$app->user->identity;
        $is_admin = $user && $user->isAdmin();

        return $this->getRequestWithUpdateList($source, $filters, $is_admin);
    }
    
    private function getRequestWithUpdateList(Source $source = null, Filters $filters = null, bool $is_admin = null, $is_update_list = true, $is_compare_all=false) {
        if (!$filters){
            $filters = new Filters();
            $filters->loadFromSession();
            if (!$filters->isExistsDefaultParams()) {
                return [
                    'status' => 'error',
                    'message' => 'В сесии не хватает данных'
                ];
            }
        }
        
        if (!$source){
            $source = Source::getById($filters->f_source);
        }

        if (!isset($is_admin)){
            $user = \Yii::$app->user->identity;
            $is_admin = $user && $user->isAdmin();
        }
        
        if ($is_update_list){
            $list = Product::getListProducts($source, $filters, $is_admin);
        } else {
            $list = null;
        }
        
        $f_count_products_on_page = $filters->f_count_products_on_page;
        $count_products_all = Product::getCountProducts($source, $filters, $is_admin);
        $count_products_right = $this->indexPresenter->getCountProductsOnPageRight($list);
        $count_pages = $this->indexPresenter->getCountPages($count_products_all, $filters->f_count_products_on_page);
        if ($filters->f_number_page_current > $count_pages){
            $filters->setVsSession('f_number_page_current', 1);
        }
        $source_name = $source->name;
        $profile_path = ($filters->f_profile || $filters->f_profile === '{{all}}') ? $filters->f_profile : 'Все';
        
        $html_block_count = "Показаны записи $f_count_products_on_page из $count_products_all ($count_products_right) Источник $source_name / $profile_path";
        $html_paginator = $this->indexPresenter->getHTMLPaginator($filters->f_number_page_current, $count_pages);

        return [
            'status' => 'ok',
            'message' => '',
            'html_index_table' => ($list)?$this->renderPartial('index_table', [
                'list' => $list,
                'local_import_stat' => null,
                'is_admin' => $is_admin,
                'f_comparison_status' => $filters->f_comparison_status,
                'f_profile' => $filters->f_profile,
                'f_no_compare' => $filters->f_no_compare,
                'f_detail_view' => $filters->f_detail_view,
                'f_number_page_current' => $filters->f_number_page_current,
                'count_pages' => $count_pages,
                'source' => $source,
            ]):null,
            'other' => [
                'id_block_count' => $html_block_count,
                'id_paginator' => $html_paginator,
                'is_compare_all' => $is_compare_all
            ]
        ];
    }
    
    /*************************************************************************
     *** Старый шлак для страницы сравнения продуктов
     *************************************************************************/

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

    private function start_import($source) {
        set_time_limit(60 * 5);
        $p_date_in_parser = ImportController::get_max_product_date_in_parser($source);
        // [если дата в source]  меньше  [даты последнего товара(сортировка по дате) в базе парсера] → запускаем импорт
        if ($p_date_in_parser && $this->do_it_need_to_update($source, $p_date_in_parser)) {
            \Yii::$app->runAction('import/local_import', ['source_id' => (int) $source->id, 'p_date_in_parser' => $p_date_in_parser]);
            // статистика в $this->getView()->params['local_import_stat']
        }
    }

    //http://localhost/product/view?id=8012&source_id=1&comparisons=&filter-items__profile=%7B%7Ball%7D%7D
    public function actionView() {
        $this->layout = 'product';

        if (\Yii::$app->request->isGet) {
            $params = \Yii::$app->request->get();
        } elseif (\Yii::$app->request->isPost) {
            $params = \Yii::$app->request->post();
        }

        $id_product = (int) $params['id'];
        $id_source = (int) $params['source_id'];

        if (!$id_product || !$id_source) {
            return $this->redirect('/product/index');
        }

        $filters = new Filters();
        $filters->loadFromSession();
        $source = Source::getById($id_source);

        $model = Product::getProduct($source, $filters);

        $prev = null;
        $next = null;

        //$compare_item = $this->productPresenter->getItemCompare($product->addInfo);
        $node = 1; //$this->productPresenter->number_node;
        $compare_item = AppHelper::get_item_by_number_key($model->addInfo, $node);
        $identity = \Yii::$app->user->identity;

        //Передаем параметры в шаблон
        $this->getView()->params = [
            'comparison_statuses_statistic' => $this->productPresenter->getListComparisonStatusesStatistic(),
            'active_comparison_status' => $filters->f_comparison_status,
            'product' => $model,
            'source' => $source
        ];

        return $this->render('view', [
                    'model' => $model,
                    'compare_item' => $compare_item,
                    'compare_items' => $model->addInfo,
                    'source' => $source,
                    'filter_comparisons' => $this->productPresenter->filters->f_comparisons,
                    'filter-items__profile' => $this->productPresenter->filters->f_profile,
                    'number_node' => $node,
                    'is_admin' => $identity && $identity->isAdmin(),
                    'active_comparison_status' => $active_comparison_status,
                    'list_comparison_statuses' => Comparison::getStatuses()
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
        
        $post = $this->request->post();
        $p_id = $this->request->post('p_id');
        $comparison = $this->request->post('comparison');
        $source_id = $this->request->post('source_id');
        $profile = $this->request->post('profile');
        $node_id = 1;
        
        return [
            'res' => 'ok',
            'link' => '/product/view?id=' . $p_id . '&source_id=' . (int) $source_id . '&comparisons=' . $comparison,
        ];
    }

    public function actionGet_products_by_params1() {
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
        $node_id = 1;
//    $direction = $this->request->post('direction','next');

        if (!$p_id || !$comparison || !$source_id)
            return ['res' => 'fuck'];

        $source = Source::getById($source_id);

        /* start */
        $q = $this->prepare_record_1($source, $comparison, $profile);

    if ($direction === 'prev' || $direction === 'next'){  }

        $prepare_joned = $this->prepare_get_joined($source->class_1, $source->table_1);
        
        $prev = $this->prepare_2($source, clone $q, ['<', $source->table_1 . '.id', $p_id], 'DESC', $comparison, 'prev_items', 5);
        $next = $this->prepare_2($source, clone $q, ['>', $source->table_1 . '.id', $p_id], 'ASC', $comparison, 'next_items', 5);
        $this_item = $this->prepare_2($source, $prepare_joned, [$source->table_1 . '.id' => $p_id], 'ASC', $comparison, 'this_item', 1);

        /* RIGHT ITEMS */
        $q_this_item = $this->prepare_get_joined($source->class_1, $source->table_1);
        $q_this_item->andWhere([$source->table_1 . '.id' => $p_id]);
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
            $_item['status'] = $item->getStatus();
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
        $out['comparison_cnt'] = $this->cnt_filter_statuses($source, $profile);

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
            return ['res' => 'fuck'];
    }

    private function prepare_2($source, $q, $where, $order, $comparison, $direction, $limit = false) {
        if ($where)
            $q->andWhere($where);
        if ($order)
            $q->orderBy($source->table_1 . '.id ' . $order);

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
            $next_items[] = $fill_item($item, $source->id, $comparison);
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

    /**
     * @param $comparison
     * @return \yii\db\ActiveQuery
     */
    private function prepare_record_1($source, $comparison, $profile) {
        /* @var $source_class yii\db\ActiveRecord */
        $source_class = $source->class_1;
        $source_table = $source->table_1;
        $source_table_2 = $source->table_2;
        $source_id = $source->id;

        $q = $this->prepare_get_joined($source_class, $source_table);

        //->where(['<=>','hidden_items.source_id', $this->source_id])
        //->where('comparisons_aggregated.source_id = '.(int)$this->source_id );


        if (0 && $source_table === 'parser_trademarkia_com') {
            $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
            $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
        } else {
            $q->innerJoin($source_table_2, $source_table_2 . '.`asin` = ' . $source_table . '.asin');
        }

        $q->addGroupBy('`' . $source_table . '`.`id`');

        $where_0 = [];
        if (0 && $source_table === 'parser_trademarkia_com') {
            $where_0 = ['like', 'info', 'add_info'];
        }

        $where_2 = [];
        $where_3 = ['and',
            ['hidden_items.p_id' => null],
            ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $source_id]],
        ];  // $item_1__ignore_red = 1


        if ($comparison === 'ALL') {
            
        } elseif ($comparison === 'ALL_WITH_NOT_FOUND') {

            $where_3 = [];
        } elseif ($comparison === 'YES_NO_OTHER') {
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
        } elseif ($comparison === 'NOCOMPARE') {
            $where_2 = ['and', ['p_all_compare.p_id' => null], ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $source_id]]];
        } else {
            $where_2 = ['`comparisons`.`status`' => $comparison];
        }

        $where = ['and', $where_0, $where_2, $where_3];

        if ($profile && $profile !== '{{all}}' && $profile !== 'Все') {
            $q->andWhere(['like', $source_table . '.`profile`', $profile]);
        }

        $q->andWhere($where);

//    echo '<pre>'.PHP_EOL;
//    print_r($q->createCommand()->getRawSql());
//    echo PHP_EOL;
//    exit;

        return $q;
    }

    private function prepare_get_joined($source_class, $source_name) {
        return $q = $source_class::find()
                //->select('*')
                //->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
                ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $source_name . '.id ')
                ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $source_name . '.id ')
                ->leftJoin('comparisons', 'comparisons.product_id = ' . $source_name . '.id ');
    }

    private function cnt_filter_statuses($source, $profile) {
        /*
          $out['YES_NO_OTHER'] = [
          'hex_color' => '',
          'name' => 'Result',
          'name_2' => 'Все отмеченные',
          ];
         */
        $statuses = Comparison::get_filter_statuses();

        foreach ($statuses as $name => $data) {
            $q_cnt = $this->prepare_record_1($source, $name, $profile);
            $res_cnt = $q_cnt->count();
            $statuses[$name]['cnt'] = $res_cnt;
        }

        return $statuses;
    }

}
