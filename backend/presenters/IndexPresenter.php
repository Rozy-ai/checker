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

/**
 * Превставитель для страницы product/index, содержащий логику
 *
 * @author kosten
 */
class IndexPresenter {
    public Filters $filters;
    public Session $session;
    
    public function __construct(Session $session, Filters $filters) {
        $this->session = $session;
        $this->filters = $filters;
    }
    
    //Загрузка параметров из сесиии. Можно легко переделать на get или post
    public function loadFromSession(){
        $session = \Yii::$app->session;
        
        $this->filters->load([
            'f_count_products_on_page'  => $session[Session::filter_count_products_on_page],
            'f_number_page_current'     => $session[Session::filter_number_page_current],
            'f_profile'                 => $session[Session::filter_items_profile],
            'f_no_compare'              => $session[Session::filter_no_compare],
            'f_id'                      => $session[Session::filter_id],
            'f_target_image'            => $session[Session::filter_target_image],
            'f_user'                    => $session[Session::filter_username],
            'f_comparing_images'        => $session[Session::filter_title],
            'f_comparisons'             => $session[Session::filter_comparisons],
            'f_sort'                    => $session[Session::filter_sort]
        ]);
    }
    
    public function setSource($source){
        $this->filters->setSource($source);
    }
    
    // ***************************************************************************
    // *** Comparison_status
    // ***************************************************************************
    
    /**
     * Получиить текущий фильтр Comparisons
     * @return string | null
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
        return $this->filters->f_items__show_n_on_page;
    }
    
    public function getCountProductsOnPageRight(&$list){
        $cnt_all_right = 0;
        foreach ($list as $k => $product) {
            $items = $product->addInfo;
            $cnt_all_right += count($items);
        }
        return $cnt_all_right;
    }
    
    // ***************************************************************************
    // *** Другое
    // ***************************************************************************
    
    public function getLastLocalImport() {
        $s_import = Stats_import_export::find();
        $s_import->where(['type' => 'IMPORT']);
        $s_import->orderBy(['created' => SORT_DESC]);
        $s_import->limit(1);
        return $s_import->one();
    }
}
