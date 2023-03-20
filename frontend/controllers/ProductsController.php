<?php

declare(strict_types=1);

namespace frontend\controllers;

use backend\models\Settings__fields_extend_price;
use backend\presenters\IndexPresenter;
use backend\presenters\ProductPresenter;
use common\models\ExternalUser;
use common\models\Filters;
use common\models\Product;
use common\models\Source;
use common\models\Stats_import_export;
use frontend\components\User;
use frontend\models\search\BillingSearch;
use InvalidArgumentException;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Request;

/**
 *  __construct($id,$module,IndexPresenter $indexPresenter,ProductPresenter $productPresenter,array $config = [])
 *  
 */
class ProductsController extends Controller
{
    public IndexPresenter $indexPresenter;

    public ProductPresenter $productPresenter;

    public function __construct(
        $id,
        $module,
        IndexPresenter $indexPresenter,
        ProductPresenter $productPresenter,
        array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->indexPresenter = $indexPresenter;
        $this->productPresenter = $productPresenter;
    }

    public function actionIndex(User $user, Request $request): string
    {
        $params = Yii::$app->request->get();

        $filters = new Filters();
        $filters->loadFromSession();
        // Если страница загружвется в первый раз, то будут отсутствовать обязательные параметры
        if ($filters->isExistsDefaultParams()) {
            $source = Source::getById($filters->f_source);

            //  Если в запросе указан номер страницы, то установим его:
            if (isset($params['page'])) {
                $number_page_current = (int)$params['page'];
                if ($number_page_current <= 0) {
                    throw new InvalidArgumentException('Указан не верный номер страницы');
                }
                $filters->setVsSession('f_number_page_current', $number_page_current);
                $this->redirect('/product/index');
            }
        } else {
            // Если страница загружается в первый раз то номер страницы нафиг не нужен, ибо по умолчанию установится в 1
            $id_user = Yii::$app->user->id;
            $source = Source::getForUser($id_user);

            if (!$source) {
                throw new ForbiddenHttpException('Не удалось найти доступный источник');
            }

            $filters->setToDefault();
            $filters->f_source = $source->id;
            $filters->saveToSession();
        }

        if (!$user->isGuest) {
            $filters->f_profile = $user->name;
        }

        $this->indexPresenter->setSource($source);

        $this->layout = 'products_list';
        $is_admin = false;
        [$list, $count_products_all] = Product::getListProductsFront($source, $filters);
        $count_pages = $this->indexPresenter->getCountPages($count_products_all, $filters->f_count_products_on_page);

        return $this->render('index', [
            'f_source' => $filters->f_source,
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
            'f_no_compare' => false,
            'f_profile_type' => '',

            'user_login' => Yii::$app->user->name,

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
            'default_price_name' => Settings__fields_extend_price::get_default_price($source->id)->name ?: 'Price Amazon',

            'source' => $source,
            'last_update' => Stats_import_export::getLastLocalImport()
        ]);
    }

    /**
     * Изменение фильтра и отображение нового списка продуктов
     */
    public function actionChangeFilter() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = \Yii::$app->request->post();
        if (isset($request)) {
            $name = $request['name'];
            $value = $request['value'];
        }
        if (!isset($name)) {
            return [
                'status' => 'error',
                'message' => 'Не удаось получить изменяемый фильтр',
            ];
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
        if ($filters->$name == $value) {
            return [
                'status' => 'info',
                'message' => 'Новое значение фильтра совпадает с предыдущим'
            ];
        }

        $filters->setVsSession($name, $value);

        $source = Source::getById($filters->f_source);
        $is_admin = false;

        return $this->getRequestWithUpdateList($source, $filters, $is_admin, true);
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

        $count_products_all = null;
        if ($is_update_list){
            [$list, $count_products_all] = Product::getListProductsFront($source, $filters);
        } else {
            $list = null;
        }

        $f_count_products_on_page = $filters->f_count_products_on_page;
        if ($count_products_all === null) {
            $count_products_all = (int)Product::getCountProducts($source, $filters, $is_admin);
        }
        $count_products_right = $this->indexPresenter->getCountProductsOnPageRight($list);
        $count_pages = $this->indexPresenter->getCountPages($count_products_all, $filters->f_count_products_on_page);
        if ($filters->f_number_page_current > $count_pages){
            $filters->setVsSession('f_number_page_current', 1);
        }
        $source_name = $source->name;
        $profile_path = ($filters->f_profile || $filters->f_profile === '{{all}}') ? $filters->f_profile : 'Все';

        $count_products = count($list);
        $html_block_count = "Показаны записи $count_products из $count_products_all ($count_products_right) Источник $source_name / $profile_path";
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
                'default_price_name' => Settings__fields_extend_price::get_default_price($source->id)->name ?: 'Price Amazon',
            ]):null,
            'other' => [
                'id_block_count' => $html_block_count,
                'id_paginator' => $html_paginator,
                'is_compare_all' => $is_compare_all
            ]
        ];
    }
}