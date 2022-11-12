<?php

/*
 * Тут собраны все переменные, которые используются в сесии.
 * Возможно добавятся функии
 */

namespace common\models;

/**
 * Класс будет отыечать за передачу данных между запросами
 * Префикс "filter_" означает что переменная имеет отношение к запросам выборки
 *
 * @author kosten
 */
class Session extends \yii\web\Session{   
    /** @const int id источника */
    const id_source = 'id_source';
    
    /** @const string Направленелие поиска следущего товара */
    const direction_next_product = 'direction_next_product';
    
    /** @const string */
    const item_2_show_all = 'item_2__show_all';
    
    /** @const string $f_items__no_compare    (where_1) Убрать товары из таблицы hidden_itesm */
    const filter_no_compare = 'filter_no_compare';
    
    /** @const string $f_items__id            (where_2) id товара из поля формы для ввода id */
    const filter_id = 'filter_id';
    
    /** @const string $f_items__target_image  (where_3) categiries_root (select) */
    const filter_target_image = 'filter_target_image';
    
    /** @const string $f_items__user          (where_4) username пользователя */
    const filter_username = 'filter_username';
   
    /** @const string $f_items__comparing_images (where_5) Title */
    const filter_title = 'filter_title';

    /** @const string f_items__comparisons    (where_6) Фильтр выбора товара из поля выбора из Comparisons */
    const filter_comparisons = 'filter_comparisons';
    
    /** @const string filter-items__sort      Нужно ли сотрировать продукты */
    const filter_sort = 'filter_sort';
    
    /** @const string filter-items__profile   Доступен только для администратора*/
    const filter_items_profile = 'filter_items_profile';
    
    /** @const int page Номер просматриваемой страницы */
    const filter_number_page_current = 'filter_number_page_current';
    
    /** @const string $items__show_n_on_page Сколько товаров отображать на странице */
    const filter_count_products_on_page = 'filter_count_products_on_page';
    
    /** @const string $right_item_show Как отображать список товаров, кратко или подробно */
    const filter_is_detail_view = 'filter_is_detail_view';
    
    /** @const array Значения по умолчанию */
    const defaults = [
        self::filter_count_products_on_page => 10,
        self::filter_number_page_current => 1,
        self::filter_comparisons => 'PRE_MATCH',
        self::filter_no_compare => true,
        self::id_source => 1,
       
    ];
    
    public function getWithDefault($key) {
        return parent::get($key, self::defaults[$key]);
    }
    /**
     * @inheritDoc
     * @return mixin Вернуть значение что установили
     */
    public function set($key, $value){
        parent::set($key, $value);
        return $value;
    }
    
    /**
     * Установить значение по умолчанию для переменной key
     * Если переменная не устанавливлена то установятся все
     * 
     * @param string|null $key
     * @return null|mixin Значение установленной переменной
     */
    public static function setToDefault($key = null){
        if ($key){
            if (in_array($key, self::defaults)) {
                return self::set(self::filter_no_compare, self::defaults[$key]);
            }
        } else {
            foreach (self::defaults as $key => $val){
                self::set($key, $val);
            }
        }
    }
    
    public static function loadFromParams(array $params){
        foreach ($params as $key => $value){
            if (!$value) {
                $value = self::defaults[$key];
            }
            self::set($key, $value);
        }
    }
}
