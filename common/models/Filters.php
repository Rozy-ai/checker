<?php
namespace common\models;

/**
 * Класс содержит набор возможных фильтров 
 * и для получения списка этого фильтра в формате [ name => count ]
 * 
 * @property $f_profile                 Только все товары которые менял пользователь с данной ролью
 * @property $f_no_compare              f_items__no_compare      (where_1) Товары не должны быть в таблице скрытых товаров
 * @property $f_categories_root         f_items__target_image;   (where_3) categiries_root (select)
 * @property $f_username                f_items__user            (where_4) username пользователя
 * @property $f_id                      f_items__id              (where_2) Товар с данным id
 * @property $f_asin                    asin товара из поля формы для ввода asin
 * @property $f_title                   f_items__comparing_images(where_5) Title
 * @property $t_status
 * @property $f_comparison_status       f_items__comparisons     (where_6) Фильтр выбора товара из поля выбора из Comparisons
 * @property $f_count_products_on_page  f_items__show_n_on_page            Количество товаров на странице
 * @property $f_number_page_current                                        Номер страницы текущий;
 * @property $f_sort                    created_ASC | created_DESC | updated_ASC | updated_DESC
 * 
 * @author kosten
 */
class Filters {
    /** @var string f_items__profile */
    public $f_profile;    
    
    /** @var string f_items__show_n_on_page Количество товаров на странице */
    public $f_count_products_on_page;

    /** @var string Номер страницы текущий*/
    public $f_number_page_current;
    
    /** @var string product => id*/
    public $f_id;

    /** @var string  product => asin товара из поля формы для ввода asin*/
    public $f_asin;
    
    /** @var string product => info["Categories: Root"] JSON*/
    public $f_categories_root;
    
    /** @var string product => info["Tittle"] JSON*/
    public $f_title;
    
    /** $var string HiddenItems => status Статус по левым товарам. Список в модели HiddenItems*/
    public $f_status;
    
    /** @var string f_items__user           (where_4) username пользователя*/
    public $f_username;
    
    /** @var string f_items__comparisons    (where_6) Фильтр выбора товара из поля выбора из Comparisons*/
    public $f_comparison_status;    
    
    /** @var string f_items__no_compare     (where_1) Убрать товары из таблицы hidden_itesm */
    public $f_no_compare;
    
    //                                      (where_7) Не используется
    //                                      (where_8) Выносим в User
    
    // Другие фильтры:   
    public $f_source;
    public $f_sort;
    public $f_detail_view;
    
    /** @const array Значения по умолчанию для некоторых фильтров*/  
    const defaults = [
        'f_count_products_on_page' => 10,
        'f_number_page_current' => 1,
        //'f_comparison_status' => 'PRE_MATCH',
        //'f_no_compare' => 'NOCOMPARE',
    ];
    
    /**
     * Загружаем фильтра сразу все
     * @param array $params
     */
    public function loadByParams(array $params, $with_default = true){
        $attr = get_class_vars($this);
        foreach ($attr as $key => $val){
            $this->$key = (isset($params[$key]) && $params[$key])? $params[$key]: null;
        }       
        /*
        $this->f_source                  = $params['f_source'];
        $this->f_sort                    = $params['f_sort'];
        $this->f_detail_view             = $params['f_detail_view'];
 
        $this->f_profile                 = $params['f_profile'] ?? null;        
        $this->f_count_products_on_page  = $params['f_count_products_on_page'] ?? null;
        $this->f_number_page_current     = $params['f_number_page_current'] ?? null;
        $this->f_id                      = $params['f_id'];
        $this->f_asin                    = $params['f_asin'] ?? null;
        $this->f_categories_root         = $params['f_categories_root'] ?? null;
        $this->f_title                   = $params['f_title'] ?? null;
        $this->f_status                  = $params['f_status'] ?? null;
        $this->f_username                = $params['f_user'] ?? null;
        $this->f_comparison_status       = $params['f_comparison_status'] ?? null;
        $this->f_no_compare              = $params['f_no_compare'] ?? null;
        */
        
        if ($with_default){
            $this->setToDefault();
        }
    }
    
    public function setToDefault(){
        foreach (self::defaults as $key => $val){
            $this->$key = $val;
        }        
    }
    
    public function loadFromValue($key, $value, $with_default = true){
        $this->$key = $value ?:($with_default?self::defaults[$key] : null);
    }
    
    public function toArray(){
        return get_object_vars($this);
        
        /*        
        return [
            'f_source' => $this->f_source,
            'f_sort' => $this->f_sort,
            'f_detail_fiew' => $this->f_detail_view,
            
            'f_profile' => $this->f_profile,
            'f_count_products_on_page' => $this->f_count_products_on_page,
            'f_number_page_current' => $this->f_number_page_current,            
            'f_id' => $this->f_id,
            'f_asin' => $this->f_asin,
            'f_categories_root' => $this->f_categories_root,
            'f_title' => $this->f_title,
            'f_status' => $this->f_status,
            'f_username' => $this->f_username,
            'f_comparison_status' => $this->f_comparison_status,
            'f_no_compare' => $this->f_no_compare,          
        ];
        * 
        */
    }
    
    public function saveToSession(Session $session = null){
        if (!$session){
            $session = \Yii::$app->session;
        }
        $params = get_object_vars($this);
        $session->saveFromParams($params);
    }
    
    public function loadFromSession(Session $session = null){
        if (!$session){
            $session = \Yii::$app->session;
        }
        $params = $session->loadToArray();
        foreach ($params as $key => $val){
            if (isset($val)){
                $this->$key = $val;
            }
        }
    }
    
    /**
     * Проверка, имеются ли в сесии обязательные фильтра
     * 
     * @return boolean
     */
    public function isExistsDefaultParams(){
        $default_params = self::defaults;
        $session_params = $this->toArray();
        
        foreach ($default_params as $key => $val){
            if (!$session_params[$key]){
                return false;
            }
        }
        return true;
    }
    
    public function setVsSession($key, $value, Session $session = null){
        $this->$key = $value;
        if (!$session){
            $session = \Yii::$app->session;
        }
        $session->set($key, $value);
    }
}
