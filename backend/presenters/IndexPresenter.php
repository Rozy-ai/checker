<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace backend\presenters;

use common\models\Session;
use common\models\Filters;
use common\models\Stats_import_export;
use common\models\Comparison;
use common\models\HiddenItems;
use common\models\P_user_visible;

/**
 * Превставитель для страницы product/index, содержащий логику
 *
 * @author kosten
 */
class IndexPresenter {
    public Filters $filters;
    public Session $session;
    
    /** @var bool кратко или подробно отображать список товаров */
    private bool $isDetailView = false;
    
    public function __construct(Session $session, Filters $filters) {
        $this->session = $session;
        $this->filters = $filters;
    }
    
    //Загрузка параметров из сесиии. Можно легко переделать на get или post
    private function loadFromSession(){
        $session = \Yii::$app->session;
        
        $this->filters->load([
            'f_count_products_on_page' => $session->getWithDefault(Session::filter_count_products_on_page),
            'f_number_page_current'    => $session->getWithDefault(Session::filter_number_page_current),
            'f_no_compare'             => $session->getWithDefault(Session::filter_no_compare),

            'f_profile'                => $session->get(Session::filter_items_profile),
            'f_id'                     => $session->get(Session::filter_id),
            'f_target_image'           => $session->get(Session::filter_target_image),
            'f_user'                   => $session->get(Session::filter_username),
            'f_comparing_images'       => $session->get(Session::filter_title),
            'f_comparisons'            => $session->get(Session::filter_comparisons),
            'f_sort'                   => $session->get(Session::filter_sort)
        ]);
        
        $this->isDetailView = ($session[Session::filter_is_detail_view])? true: false;
    }
    
    public function loadFromParams(array $params){
        $session = \Yii::$app->session;
        $session->loadFromParams($params);
        $this->loadFromSession();
    }
    
    public function setSource($source){
        $this->filters->setSource($source);
    }
    
    public function getNumberPageCurrent(){
        return $this->filters->f_number_page_current;
    }
    // ***************************************************************************
    // *** Comparison_status
    // ***************************************************************************
    
    /**
     * Получиить текущий фильтр Comparisons
     * @return array
     */
    public function getCurrentComparisonStatus(){
        return $this->filters->f_comparisons;
    }
    
    /**
     * Список статусов и количество товаров, которые сотвествуют этому фильтру
     * Список статусов находится в common/models/Comparisons и является константой
     * 
     * @return attay
     *    [
     *       NOCOMPARE => int,
     *       MISMATCH  => int,
     *       PRE_MATCH => int,
     *       MATCH     => int,
     *       OTHER     => int
     *    ]
     * @throws \yii\base\InvalidArgumentException
     */
    public function getListComparisonStatuses(){
        $names = Comparison::get_filter_statuses();
        $out = [];
        $data = $this->filters->getListComparisonStatuses();
        foreach ($data as $key => $count){
            $out[$key] = [
                'name'  => $names[$key]['name'],
                'count' => $count
            ];
        }
        return $out;
    }
    
    //public function getListComparisonStatus(){
    //    return Comparison::get_filter_statuses();
    //}
    
    // ***************************************************************************
    // *** Profile
    // ***************************************************************************
    
    public function getCurrentProfile(){
        return $this->filters->f_profile;
    }
    
    // ***************************************************************************
    // *** CategoriesRoot
    // ***************************************************************************
    
    public function getListCategoriesRoot(){
        return $this->filters->getListCategoriesRoot();
    }
    
    // ***************************************************************************
    // *** Users
    // ***************************************************************************    
    
    public function getListUser(){
        return $this->filters->getListUser();
    }
    
    // ***************************************************************************
    // *** Profiles
    // ***************************************************************************
    
    public function getListProfiles(){
        return $this->filters->getListProfiles();
    }
    
    // ***************************************************************************
    // *** Products
    // ***************************************************************************    

    public function getListProduct(){
        return $this->filters->getListProduct();
    }
    
    public function getCountProducts(){
        return $this->filters->getCountProducts();
    }
    
    public function getCountProductsOnPage(){
        return $this->filters->f_count_products_on_page;
    }
    
    public function getCountProductsOnPageRight($list){
        $cnt_all_right = 0;
        foreach ($list as $product) {
            $items = $product->addInfo;
            $cnt_all_right += count($items);
        }
        return $cnt_all_right;
    }
    
    // ***************************************************************************
    // *** Другое
    // ***************************************************************************
    
    public function isDetailView(){
        return $this->isDetailView;
    }
    
    public function getLastLocalImport() {
        $s_import = Stats_import_export::find();
        $s_import->where(['type' => 'IMPORT']);
        $s_import->orderBy(['created' => SORT_DESC]);
        $s_import->limit(1);
        return $s_import->one();
    }
    
    /**
     * Сколько товаров отображается на странице
     * 
     * @param int $countProducts Общее количество товаров
     * @param int $countProductsOnPage Количество отображаемых товаров на страние
     * @return int
     * @throws InvalidArgumentException
     */
    public function getCountPages(int $countProducts, int $countProductsOnPage): int{
        if ( !$countProductsOnPage ){
            throw new InvalidArgumentException('Количество отображаемых товаров на страние не может равняться нулю');
        }
        return ceil($countProducts/$countProductsOnPage);
    }
    
    /**
     * Функция для отображения пагинатора
     * Всю логику работы оставил старой. Позже вернусь
     * 
     * @param type $countPages Сколько всего страниц
     * @param type $numberPageCurrent Номер текущей страницы
     * @param type $left_right_n - настройка области просмотра
     */
    public function getPager(int $countPages, int $numberPageCurrent, $left_right_n = 3){
        $from = $numberPageCurrent-$left_right_n; //1
        $add_to_v2 = 0;
        if ($from <= 0){
          $add_to_v2 = abs($from)+1; //2
          $from = 1;
        }
        $to = $numberPageCurrent + $left_right_n + $add_to_v2;

        if ($countPages < $to){
          //$to = $countPages;
          $add_to_from = $countPages - $to;
          $from = $numberPageCurrent-$left_right_n+$add_to_from;
          if ($from < 1) {
                $from = 1;
            }
        }

        if ($from > $countPages && $from > $to) {
            $from = 1;
        }
        if ($to > $countPages) {
            $to = $countPages;
        }

        $pager['from'] = $from;
        $pager['to'] = $to;
        $pager['this_n'] = $numberPageCurrent;
        $pager['in'] = ['pages_cnt' => $countPages, 'left_right_n' => $left_right_n];

        return $pager;
    }
    
    /**
     * Функция для присвоения статуса STATUS_MISMATCH всем правым товарам 
     * и присвоения левому товару статуса STATUS_NOT_FOUND
     * 
     * @param string $classProduct
     * @param type $url
     * @param int $id_product
     * @param int $id_source
     * @param bool $confirmToAction
     * @return json array [
     *      'status' = ok | have_match | error
     *      'message' Сообщение для пользователя
     * ]
     */
    public function missmatchToAll(string $classProduct, $url, int $id_product, int $id_source, bool $confirmToAction = false){
        $product = $classProduct::getById($id_product);
        if (!$confirmToAction && $product->isExistsItemsWithMatch()){
            return [
                'status'    => 'have_match',
                'message'   => 'У даннного продукта имеются товары со статутом Match/Prematch которые будут изменены на Missmatch. Продолжить?'
            ];
        }
               
        $add_info = $product->addInfo;
       
        try{
            //Всем товарам справа присвоить статус STATUS_MISMATCH
            if (is_array($add_info)){
                foreach ($add_info as $number_node => $item){
                    Comparison::setStatus(Comparison::STATUS_MISMATCH, $id_source, $id_product, $item->id);
                }
            }
            
            // Добавить товар в список скрытых (Добавить в таблицу hidden_items)
            $find = HiddenItems::find()->where(['p_id' => $id_product, 'source_id' => $id_source])->one();
            if (!$find) {
                $h = new HiddenItems([
                    'p_id'      => $id_product,
                    'source_id' => $id_source,
                    'status'    => HiddenItems::STATUS_NOT_FOUND,
                ]);
                $h->save();
            }
        } catch (\Exception $ex) {
            \Yii::error($ex->getLine().':'.$ex->getMessage());
            return [
                'status' => 'error',
                'message' => 'При сохранении данных возникла ошибка'
            ];
        }

        return [
            'status' => 'ok'
        ];
    }
    
    /**
     * Установить статус правого товара
     * 
     * @param type $id_product Левый товар
     * @param type $id_item Правый товар
     * @param type $id_source id источника
     * @param type $status Статус, в который нужно установить товар
     * @return type
     */
    public function setStatusProductRight($id_product, $id_item, $id_source, $status, $message=''){
        
        try{
            Comparison::setStatus($status, $id_source, $id_product, $id_item, $message);
            
            if ($status == Comparison::STATUS_OTHER && $message && User::isAdmin()){
                if (!P_user_visible::findOne(['p_id' => $id_product])) {
                    
                    $puv = new P_user_visible();
                    $puv->p_id = $id_product;
                    $puv->save();
                }
            }

        } catch (\Exception $ex) {
            return [
                \Yii::error($ex->getLine().':'.$ex->getMessage()),
                'status' => 'error',
                'message' => 'При обновлении базы данных возникла ошибка'
            ];
        }
        
        return [
            'status' => 'ok'
        ];
    }
    
}
