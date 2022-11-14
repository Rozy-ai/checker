<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace backend\presenters;

use common\models\Filters;
use common\models\Session;
use common\helpers\AppHelper;
use common\models\Comparison;
/**
 * Description of ProductPresenter
 *
 * @author kosten
 */
class ProductPresenter {
    public Filters $filters;
    public Session $session;
    
    public $filter_items_source;
    public $direction;
    public $filter_no_compare;
    public $item_2__show_all;
    public $comparisons;
    public $number_node;
    
    public function __construct(Session $session, Filters $filters) {
        $this->session = $session;
        $this->filters = $filters;
    }
    
    //Загрузка параметров из сесиии. Можно легко переделать на get или post
    private function loadFromSession(){
        $session = \Yii::$app->session;
        
        $this->filter_items_source  = $session->getWithDefault(Session::id_source);
        $this->direction            = $session->getWithDefault(Session::direction_next_product);
        $this->filter_no_compare    = $session->getWithDefault(Session::filter_no_compare);
        $this->item_2__show_all     = $session->getWithDefault(Session::item_2_show_all);
        $this->comparisons          = $session->getWithDefault(Session::filter_comparisons);
    }
    
    public function loadFromParams(array $params){
        $session = \Yii::$app->session;
        $session->loadFromParams($params);
        $this->loadFromSession();
    }
    
    public function setSource($source){
        $this->filters->setSource($source);
    }
    
    public function getProduct(){
        $product = $this->filters->getProduct();
        
        
        return $product;
        //    $arrows['left']['ignore_checked'] = $this->get_arrows($id, $_model, 'prev', 1);
        //    $arrows['left']['ignore_dont_checked'] = $this->get_arrows($id, $_model, 'prev', 0);

        //    $arrows['right']['ignore_checked'] = $this->get_arrows($id, $_model, 'next', 1);
        //    $arrows['right']['ignore_dont_checked'] = $this->get_arrows($id, $_model, 'next', 0);
    }
    
    /*
     * Получить следущий сравниванемый продукт
     * todo: AppHelper::get_item_by_number_key заменить на выбор следущего правого продукта согласно фильтрам
     */
    public function getItemCompare($addInfo){
        $this->item_2__show_all = 1; // Написано что в скрытии красных нет необходимости. Но это не точно
        
        if ($this->item_2__show_all){
            return AppHelper::get_item_by_number_key($addInfo, $this->number_node);
        } else {
            return null;
        }
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
   
    /**
     * Список статусов и количество для статистики продуктов
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
    public function getListComparisonStatusesStatistic(){
        /*
        $out['YES_NO_OTHER'] = [
          'hex_color' => '',
          'name' => 'Result',
          'name_2' => 'Все отмеченные',
        ];
        */
        
        $statuses = Comparison::get_filter_statuses();

        foreach ($statuses as $name => $data){
          $statuses[$name]['count'] = 1;
        }

        return $statuses;        
    }
}
