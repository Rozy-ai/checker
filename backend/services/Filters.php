<?php

/**
 * Сервис для работы с фильтрами условиями выборки а так же для предоставления вариантов возможных условий
 */

namespace backend\services;

use backend\models\User;

/**
 * Кдасс для составления запроса на получение списка продуктов, отсеянных фильтрами
 *
 * @author kosten
 */
class Filters {
    // Названия дибильные. Но оставлено пока так для совместимости с оригиналом по поиску
    public $filter_items__profile;
    public $f_items__right_item_show;       // Не используется
    public $f_items__show_n_on_page;        // Не используется
    public $f_items__no_compare;            // (where_1) Убрать товары из таблицы hidden_itesm
    public $f_items__id;                    // (where_2) id товара из поля формы для ввода id
    public $f_items__target_image;          // (where_3) categiries_root (select)
    public $f_items__user;                  // (where_4) username пользователя
    public $f_items__comparing_images;      // (where_5) Title
    public $f_items__comparisons;           // (where_6) Фильтр выбора товара из поля выбора из Comparisons
                                            // (where_7) Не используется
                                            // (where_8) Выносим в User
      
    public $source_id;
    public $source_class;
    public $source_table_name;              //Исходная таблица товаров ( parser_trademarkia_com )
    
    public function loadFromParams($params){
        $this->filter_items__profile = $params['filter-items__profile'] ?? null;
        $this->f_items__right_item_show = $params['filter-items__right-item-show'] ?? null;
        $this->f_items__show_n_on_page = $params['filter-items__show_n_on_page'] ?? 10;
        $this->f_items__id = $params['filter-items__id'] ?? null;
        $this->f_items__comparing_images = $params['filter-items__comparing-images'] ?? null;
        $this->f_items__target_image = $params['filter-items__target-image'] ?? null;
        $this->f_items__user = $params['filter-items__user'] ?? null;
        $this->f_items__comparisons = $params['filter-items__comparisons'] ?? null;
        $this->f_items__no_compare = $params['filter-items__no-compare'] ?? null;
    }
    
    /*
     * Фильтр проверки на отсутствие товара в таблице hidden_items
     *
     * @param string | null $f_items__no_compare
     * @return array
     * @throws \InvalidArgumentException
     */
    
    public function where_1($f_items__no_compare = null){
        if ($f_items__no_compare) {
            $this->f_items__no_compare;
        }
        if (!$this->source_id){
            throw new \InvalidArgumentException('Отсутствует обязательный аргумент');
        }
        return (!$this->f_items__no_compare)?
            ['and',['hidden_items.p_id' => null],
                ['OR',['hidden_items.source_id' => null],
                    ['<>','hidden_items.source_id', $this->source_id]
                ]
            ]:[];
        //return "`hidden_items`.`p_id` IS NULL) AND ((`hidden_items`.`source_id` IS NULL) OR (`hidden_items`.`source_id` <> $source_id"
    }
    
    /**
     * Фильтр поиска товара по id или asin. Используется в поле поиска товара на странице product/index
     * 
     * @param string $f_items__id id или asin товара из поля формы для ввода id
     * @param string $source_table_name Имя таблицы источника
     * @return array
     * @throws \InvalidArgumentException
     */
    public function where_2($f_items__id = null, $source_table_name = null){
        if ($f_items__id){
            $this->f_items__id = $f_items__id;
        }
        if ($source_table_name){
            $this->source_table_name = $source_table_name;
        }
        if (!$this->source_table_name){
            throw new \InvalidArgumentException('Отсутствует обязательный аргумент');
        }
        return ($this->f_items__id && $this->source_table_name)?
            ['or',[$this->source_table_name.'.id' => $this->items__id],
                  [$this->source_table_name.'.asin' => $this->f_items__id]
            ]:[];
    }
    
    /**
     * Фильтр поиска товара по Categories: Root. Используется в product/index
     * 
     * Ищет в поле info значение  '"Categories: Root": "'.$this->f_items__target_image.'"'
     * Поле info имеется в таблицах parser_trademarkia_com | parser_google | parser_china
     * @param string $f_items__target_image
     * @return array
     */
    public function where_3( $f_items__target_image = null ){
        if ($f_items__target_image){
            $this->f_items__target_image = $f_items__target_image;
        }
        return ($this->f_items__target_image)?
            ['like', 'info', '"Categories: Root": "'.$this->f_items__target_image.'"'] :[];
    }
    
    /*
     * Фильтр username пользователя. В поле появляются имена пользователей, которые делали выбор на правых товарах.
     * Поле появляется если пользователь admin. И там выбирается 1 из вариантов. Пока только user отображается почему-то.
     * @param string $f_items_user Выбранный username пользователя
     * @return array
     */
    public function where_4($f_items_user = null){
        if ($f_items_user){
            $this->f_items__user = $f_items_user;
        }
        return ($this->f_items__user)?['like', 'users', $this->f_items__user]:[];
    }
    
    /**
     * Фильтр Title товара
     * Отображается на странице product/index
     * @param string $f_items__comparing_images
     * @return array
     */
    public function where_5($f_items__comparing_images = null){
        if ($f_items__comparing_images){
            $this->f_items__comparing_images = $f_items__comparing_images;
        }
        return ($this->f_items__comparing_images)?['like', 'info', str_replace('/','\/',$this->f_items__comparing_images)]:[];
    }
    
    
    /**
     * Фильтр статуса товара 
     * Список статусов находится в common/models/Comparisons и является константой
     * 
     * @param type $f_items__comparisons
     * @param type $source_id - id источника товара (sourse->id)
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function where_6($f_items__comparisons = null, $source_id = null){
        if ($f_items__comparisons)
        {
            $this->f_items__comparisons = $f_items__comparisons;
        }
        
        if ($source_id) {
            $this->source_id = $source_id;
        }
        
        if ($this->f_items__comparisons === 'NOCOMPARE' && !$this->source_id) {
            throw new \InvalidArgumentException("Отсутствует обязательный аргумент для значения $this->f_items__comparisons");
        }
        
        switch ($this->f_items__comparisons){
            case 'MATCH':       return ['or like', 'comparisons_aggregated.statuses', ['MATCH','%,MATCH,%','MATCH,%','%,MATCH'], false];
            case 'MISMATCH':    return ['or like', 'comparisons_aggregated.statuses', 'MISMATCH'];
            case 'PRE_MATCH':   return ['or like', 'comparisons_aggregated.statuses', 'PRE_MATCH'];
            case 'OTHER':       return ['or like', 'comparisons_aggregated.statuses', 'OTHER'];
            case 'YES_NO_OTHER':return ['and', "`comparisons`.`status` IS NOT NULL AND comparisons.`status` <> 'MISMATCH'"];
            case 'NOCOMPARE':   return ['and',['p_all_compare.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]];
            case 'ALL_WITH_NOT_FOUND':  return [];
            default:                    return [];
        }
    }
    
    /**
     * Проверка источника на наличие поля add_info
     * 
     * Если исходная таблица из EBay (parser_trademarkia_com) 
     * @return array
     */
    public function where_7(){
        return ($this->source_table_name === 'parser_trademarkia_com')?
            ['and',
                ['like', 'info', 'add_info'],
                "info NOT LIKE '%\"add_info\":\"[]\"%'",
                "info NOT LIKE '%\"add_info\": \"[]\"%'"] : [];
    }
    
    /**
     * Если пользователь не Admin то включить записи правленые этим пользователем или никем
     * 
     * @return array
     * @throws \BadMethodCallException
     */
    public function where_8(){
        if (!User::isAdmin()) {
            $userId = \Yii::$app->getUser()->id;
            if (!$userId) {
                throw new \BadMethodCallException();
            }
            return ["IN", 'comparisons.user_id', [$userId, null]];
        } else {
            return [];
        }
    }
    
    /**
     * Включить в выборку только товары, с пометкой "Не удалось установить точное соответствие"
     * 
     * Список пометок находится в таблице checker.message
     * Устанавливается на странице сравнения товаров (product/view) на нажатии на правом товаре на синююю кнопку из трех
     * @return array
     */
    public function where_9(){
        return ['messages.settings__visible_all' => '1'];
    }
    
    /**
     * Фильтр профиля. Отображается только для администратора, значит и работает только для администратора
     * Отображается список профилей товара (Prepod, General, ...)
     * Указано в таблице parser_trademarkia_com в поле profile. И список выбирвется из него
     * Вопрос:
     *     Чтобы вывести список профилей, нужно сформировать список согласно фильтрам иди вообще всех товаров?
     * @return array
     */
    public function where_10($source_table_name = null, $filter_items__profile = null){
        if ($source_table_name){
            $this->source_table_name = $source_table_name;
        }
        if ($filter_items__profile){
            $this->filter_items__profile = $filter_items__profile;
        }
        if (!$source_table_name){
            throw new InvalidArgumentException('Не установлено значение source_table_name');
        }
        return (User::isAdmin() && $this->filter_items__profile !== '{{all}}' && $this->filter_items__profile !== 'Все')?
            ['like', $this->source_table_name.'.`profile`', $this->filter_items__profile] : [];
    }
    
    public function getAllWheres(){
        return ['and', $this->where_1(), $this->where_2(), $this->where_3(), $this->where_4(), $this->where_5(), 
                       $this->where_6(), $this->where_7(), $this->where_8(), $this->where_9(), $this->where_10()];
    }
}
