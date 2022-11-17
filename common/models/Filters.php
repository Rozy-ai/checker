<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace common\models;

use common\models\User;

/**
 * Класс содержит набор возможных фильтров 
 * и для получения списка этого фильтра в формате [ name => count ]
 * 
 * @property f_profile                 Только все товары которые менял пользователь с данной ролью
 * @property f_no_compare              f_items__no_compare      (where_1) Товары не должны быть в таблице скрытых товаров
 * @property f_target_image            f_items__target_image;   (where_3) categiries_root (select)
 * 
 * @property f_user                    f_items__user            (where_4) username пользователя
 * @property f_id                      f_items__id              (where_2) Товар с данным id
 * @property f_comparing_images        f_items__comparing_images(where_5) Title
 * @property f_comparisons             f_items__comparisons     (where_6) Фильтр выбора товара из поля выбора из Comparisons
 * @property f_count_products_on_page  f_items__show_n_on_page            Количество товаров на странице
 * @property f_number_page_current                                        Номер страницы текущий;
 * @property f_sort                    created_ASC | created_DESC | updated_ASC | updated_DESC
 * 
 * @author kosten
 */
class Filters {
    /** @var string f_items__show_n_on_page Количество товаров на странице */
    public $f_count_products_on_page;

    /** @val string Номер страницы текущий*/
    public $f_number_page_current;

    /** @var string f_items__profile */
    public $f_profile;

    /** @var string f_items__no_compare     (where_1) Убрать товары из таблицы hidden_itesm */
    public $f_no_compare;

    /** @var string f_items__id             (where_2) id товара из поля формы для ввода id*/
    public $f_id;

    /** @var string f_items__target_image;  (where_3) categiries_root (select)*/
    public $f_target_image;

    /** @var string f_items__user           (where_4) username пользователя*/
    public $f_user;

    /** @var string f_items__comparing_images(where_5) Title */
    public $f_comparing_images;

    /** @var string f_items__comparisons    (where_6) Фильтр выбора товара из поля выбора из Comparisons*/
    public $f_comparisons;
    //                                      (where_7) Не используется
    //                                      (where_8) Выносим в User
    
    /** @var string filter_items__sort */
    public $f_sort;

    /** @var array Массив данных, наполненный именами таблиц для присоединеиня в зависимости от условий */
    private $tables = [];
    public Source $source;

    // Сделал так для наглядности нужных параметров
    public function load(array $params){
        //foreach($properties as $key => $value){
        //  $this->{$key} = $value;
        //}
        
        $this->f_count_products_on_page  = $params['f_count_products_on_page'];
        $this->f_number_page_current     = $params['f_number_page_current'];
        $this->f_profile                 = $params['f_profile'];
        $this->f_no_compare              = $params['f_no_compare'];
        $this->f_id                      = $params['f_id'];
        $this->f_asin                    = $params['f_asin'];
        $this->f_target_image            = $params['f_target_image'];
        $this->f_user                    = $params['f_user'];
        $this->f_comparing_images        = $params['f_comparing_images'];
        $this->f_comparisons             = $params['f_comparisons'];
        $this->f_sort                    = $params['f_sort'];
    }

    public function setSource($source){
        $this->source = $source;
    }

    public function loadFromSession(Source $source){
        $session = \Yii::$app->session;

        $this->f_profile         = $session[Session::filter_items_profile];
        $this->f_id              = $session[Session::filter_id];
        $this->f_comparing_images= $session[Session::filter_title];
        $this->f_target_image    = $session[Session::filter_target_image];
        $this->f_user            = $session[Session::filter_username];
        $this->f_no_compare      = $session[Session::filter_no_compare];
        $this->f_comparisons     = $session[Session::filter_comparisons];


        if (!$this->comparisons){
            // is admin
            $identity = \Yii::$app->user->identity;
            if ($identity && $identity->isAdmin()){
                $this->f_no_compare = $session[Session::filter_comparisons] = 'YES_NO_OTHER';
            } else {
                $this->f_no_compare = $session[Session::filter_comparisons] = 'NOCOMPARE';
            }
        }

    }
       
    /*
     * Фильтр проверки на отсутствие товара в таблице hidden_items
     * where_1
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getSqlNoCompare(){
        if (!isset($this->source->id)){
            throw new \InvalidArgumentException('Отсутствует обязательный аргумент');
        }

        if ($this->f_no_compare){
            $this->addTable('hidden_items');
            return  ['and',['hidden_items.p_id' => null],
                        ['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source->id]]
                    ];
        } else {
            return [];
        }
    }

    /**
     * Фильтр поиска товара по id или asin
     * where_2
     *
     * @param string $f_items__id id или asin товара из поля формы для ввода id
     * @param string $source_table_name Имя таблицы источника
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getSqlIdOrAsin(){
        if (!isset($this->source->table_1)){
            throw new \InvalidArgumentException('Отсутствует обязательный аргумент');
        }

        return ($this->f_id)?
            ['or',[$this->source->table_1.'.id' => $this->f_id],
                  [$this->source->table_1.'.asin' => $this->f_id]
            ] : [];
    }
    
    public function getSqlAsin(){
        if (!isset($this->source->table_1)){
            throw new \InvalidArgumentException('Отсутствует обязательный аргумент');
        }
        return ($this->f_asin)?
            ['like', $this->source->table_1.'ASIN', $this->f_asin.'____'] : [];
    }
    
    public function getSqlIdGreater(){
        return ($this->f_id)? [ '>=' , 'id', $this->f_id] : [];
    }

    /**
     * Фильтр поиска товара по Categories: Root
     * where_3
     *
     * Ищет в поле info значение  '"Categories: Root": "'.$this->f_items__target_image.'"'
     * Поле info имеется в таблицах parser_trademarkia_com | parser_google | parser_china
     * @param string $f_items__target_image
     * @return array
     */
    public function getSqlCategoriesRoot(){
        if (!isset($this->source->table_1)){
            throw new \InvalidArgumentException('Отсутствует обязательный аргумент');
        }

        return ($this->f_target_image)?
            ['like', $this->source->table_1.'info', '"Categories: Root": "'.$this->f_target_image.'"'] : [];
    }

    /*
     * Фильтр username пользователя. В поле появляются имена пользователей, которые делали выбор на правых товарах.
     * where_4
     *
     * Поле появляется если пользователь admin. И там выбирается 1 из вариантов. Пока только user отображается почему-то.
     * @param string $f_user Выбранный username пользователя
     * @return array
     */
    public function getSqlUser(){
        return ($this->f_user)?['like', 'users', $this->f_user]:[];
    }

    /**
     * Фильтр Title товара
     * (where_5)
     *
     * Отображается на странице product/index
     * @param string $f_items__comparing_images
     * @return array
     */
    public function getSqlComparingImages(){
        return ($this->f_comparing_images)?['like', 'info', str_replace('/','\/',$this->f_comparing_images)]:[];
    }

    /**
     * Фильтр отмеченных сравнений товара
     * (where_6)
     *
     * Список статусов находится в common/models/Comparisons и является константой
     *
     * @param type $f_items__comparisons
     * @param type $source_id - id источника товара (sourse->id)
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function getSqlComparisons(){
        if ($this->f_comparisons === 'NOCOMPARE' && !isset($this->source->id)) {
            throw new \InvalidArgumentException("Отсутствует обязательный аргумент для значения $this->f_comparisons");
        }

        switch ($this->f_comparisons){
            case 'MATCH': {
                $this->addTable('comparisons_aggregated');
                return ['or like', 'comparisons_aggregated.statuses', 'MATCH', false];
            }
            case 'MISMATCH': {
                $this->addTable('comparisons_aggregated');
                return ['or like', 'comparisons_aggregated.statuses', 'MISMATCH'];
            }
            case 'PRE_MATCH': {
                $this->addTable('comparisons_aggregated');
                return ['or like', 'comparisons_aggregated.statuses', 'PRE_MATCH'];
            }
            case 'OTHER': {
                $this->addTable('comparisons_aggregated');
                return ['or like', 'comparisons_aggregated.statuses', 'OTHER'];
            }
            case 'YES_NO_OTHER': {
                $this->addTable('comparisons');
                return ['and', "`comparisons`.`status` IS NOT NULL AND comparisons.`status` <> 'MISMATCH'"];
            }
            case 'NOCOMPARE': {
                $this->addTable('p_all_compare');
                $this->addTable('hidden_items');
                return ['and',['p_all_compare.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]];
            }
            //case 'ALL_WITH_NOT_FOUND':  return [];
            default:                    return [];
        }
    }

    /**
     * Проверка источника на наличие поля add_info
     * (where_7)
     *
     * Если исходная таблица из EBay (parser_trademarkia_com)
     * @return array
     */
    public function getSqlAddInfoExists(){
        return ($this->source->table_1 === 'parser_trademarkia_com')?
            ['and',
                ['like', 'info', 'add_info'],
                "info NOT LIKE '%\"add_info\":\"[]\"%'",
                "info NOT LIKE '%\"add_info\": \"[]\"%'"] : [];
    }

    /**
     * Если пользователь не Admin то включить записи правленые этим пользователем или никем
     * (where_8)
     *
     * @return array
     * @throws \BadMethodCallException
     */
    public function getSqlNoInComparisons(){
        $user = \Yii::$app->user->identity;
        $is_admin = ($user && $user->isAdmin());

        if (!$is_admin && $user->id) {
            $this->addTable('comparisons');
            return ["IN", 'comparisons.user_id', [$user->id, null]];
        } else {
            return [];
        }
    }

    /**
     * Включить в выборку только товары, с пометкой "Не удалось установить точное соответствие"
     * (where_9)
     *
     * Список пометок находится в таблице checker.message
     * Устанавливается на странице сравнения товаров (product/view) на нажатии на правом товаре на синююю кнопку из трех
     * @return array
     */
    public function getSqlSettingsMessage(){
        $this->addTable('messages');
        return ['messages.settings__visible_all' => '1'];
    }

    /**
     * Фильтр профиля. Отображается только для администратора, значит и работает только для администратора
     * (where_10)
     *
     * Отображается список профилей товара (Prepod, General, ...)
     * Указано в таблице parser_trademarkia_com в поле profile. И список выбирвется из него
     * Вопрос:
     *     Чтобы вывести список профилей, нужно сформировать список согласно фильтрам иди вообще всех товаров?
     * @return array
     */
    public function getSqlProfile(): array
    {
        if (!isset($this->source->table_1)){
            throw new InvalidArgumentException('Не установлено значение source->table_1');
        }
        if ((User::isAdmin() && $this->f_profile && $this->f_profile !== '{{all}}' && $this->f_profile !== 'Все')) {
            return ['like', $this->source->table_1.'.`profile`', $this->f_profile];
        }
        return [];
    }

    /**
     * Получить список всех категорий ( 'Categories: Root' ) и их количество
     * (todo: Нужно оптимизировать)
     * @return array
     *    [
     *       string => int,
     *       string => int,
     *       ...
     *       string => int
     *    ]
     */
    public function getListCategoriesRoot(){
        $list = \Yii::$app->db->createCommand(
            'SELECT info->\'$."Categories: Root"\' as cat, count(*) as count FROM parser_trademarkia_com GROUP BY cat')->queryAll(); 

        $new_list = [];
        if (is_array($list)){
            foreach ($list as $data){
                $new_list[$data['cat']] = $data['count'];
            }
        }
        return $new_list;
    }

    /**
     * Список пользователей с количеством сравнений для каждого
     * (todo: Нужно оптимизировать)
     * @return attay
     *    [
     *       username => [
     *          'id'        => int,
     *          'cnt'       => int
     *       ],
     *       ...
     *    ]
     */
    public function getListUser(){
        if (!$this->source) {
            throw new \yii\base\InvalidParamException();
        }

        $cnt = [];
        $all = User::find()
                ->where('status > 0')
                ->all();
        $source_table_name = $this->source->table_1;
        $source_class = $this->source->class_1;

        foreach ($all as $user) {
            $q = $source_class::find()
                    ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $source_table_name . '.id ')
                    ->leftJoin('comparisons', 'comparisons.product_id = ' . $source_table_name . '.id ')
                    ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $source_table_name . '.id ')
                    ->where($this->getSqlNoCompare())
                    ->andWhere(['comparisons.user_id' => $user->id]);

            $q->addGroupBy('`' . $this->source->table_1 . '`.`id`');
            $c = $q->count();
            if (!$c) {
                $c = 0;
            }

            $cnt[$user->username] = ['id' => $user->id, 'cnt' => $c];
        }

        return $cnt;
    }

    /**
     * Список статусов и количество товаров, которые сотвествуют этому фильтру
     * Список статусов находится в common/models/Comparisons и является константой
     * (todo: Нужно оптимизировать)
     *
     * @param type $f_items__comparisons
     * @param type $source_id - id источника товара (sourse->id)
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
        if (!$this->source) {
            throw new \yii\base\InvalidParamException();
        }

        $source_table_name = $this->source->table_1;
        $source_class = $this->source->class_1;

        $q = $source_class::find()
            ->select(['comparisons.status', 'COUNT(*) as count_statuses'])
            ->leftJoin('comparisons', 'comparisons.product_id = ' . $source_table_name . '.id ')
            ->asArray()
            ->groupBy('comparisons.status');

        $data = $q->all();

        $out = [];
        foreach ($data as $k => $val){
            if ($val['status'] == null){
                $val['status'] = 'NO_COMPARE';
            }
            $out[$val['status']] = $val['count_statuses'];
        }

        return $out;
    }

    /**
     * Получить список профилей. Отображается только для администратора, значит и работает только для администратора
     *      Нужно придумать вместо этой хрени 1 запрос
     * Нужно получить результат:
     *      array(7) (
                [Prepod] => (string) Prepod (16)
                [General] => (string) General (1160)
                [General_1] => (string) General_1 (0)
                [General_2] => (string) General_2 (0)
                [Alex] => (string) Alex (27)
                [FBA] => (string) FBA (1)
                [Prepod_var] => (string) Prepod_var (0)
            )
     *
     * @return array
     */
    public function getListProfiles() {
        $source_class = $this->source->class_1;

        //Получить уникальные значения столбца profile
        $q0 = $source_class::find()->distinct(true)->select(['profile'])->asArray();
        $res_1 = $q0->column();

        //Так как названий профилей может быть много через запятую и ищем уникальные еше раз
        $find_uniq = function ($data) {
            $out = [];
            foreach ($data as $k => $item) {
                $a = explode(',', $item);
                foreach ($a as $value) {
                    if ($value){
                        $out[$value] = $value;
                    }
                }
            }
            return $out;
        };

        // Тут уникальные значения столбца profile
        $profiles_uniq = $find_uniq($res_1);

        $q2 = $source_class::find()
                ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source->table_1 . '.id ')
                ->innerJoin($this->source->table_2, $this->source->table_2 . '.`asin` = ' . $this->source->table_1 . '.asin')
               ->where(['and', ['hidden_items.p_id' => null], ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source->id]]]);

        $q2->asArray();

        $q2->select($this->source->table_1 . '.id, ' . $this->source->table_1 . '.profile')
                ->groupBy($this->source->table_1 . '.id ');

        $list_out = [];
        //$q2_load = $q2->all();
        $q2_load_cnt = $q2->count();
        foreach ($profiles_uniq as $p_name) {
            $q2_tmp = clone $q2;
            $q2_tmp->andWhere(['like', $this->source->table_1 . '.`profile`', $p_name]);
            $list_out[$p_name] = $p_name . ' (' . $q2_tmp->count() . ')';
        }

        // !!!! что считать если один товар содержит 3 значения то товар же один ... дак выводить цифру 1 или 3
        $a['{{all}}'] = 'Все (' . $q2_load_cnt . ')';

        return array_merge($a, $list_out);
    }

    /**
     * Плучить список продуктов(слева) для вывода, согласно всех фильтров
     * Есть огнаричение количества на вывод
     */
    public function getListProduct(){
        if (!$this->source) {
            throw new \yii\base\InvalidArgumentException();
        }

        $source_table = $this->source->table_1;
        $this->tables = [];
        
        $q = $this->source->class_1::find();

        // Получаем все условия запроса:
        // !!! Если менять тут то нужно менять getCountProducts
        $q->where(['and',
            $this->getSqlNoCompare(),
            $this->getSqlAsin(),
            $this->getSqlCategoriesRoot(),
            $this->getSqlUser(),
            $this->getSqlComparingImages(),
            $this->getSqlComparisons(),
            //$this->getSqlAddInfoExists(),
            $this->getSqlNoInComparisons(),
            //$this->getSqlSettingsMessage(),
            //$this->getSqlProfile()
        ]);
        
        // Добавим сортировку:
        switch ($this->f_sort) {
            case 'created_ASC':
                $q->orderBy($source_table . '.date_add ASC');
                break;
            case 'created_DESC':
                $q->orderBy($source_table . '.date_add DESC');
                break;
            case 'updated_ASC' :
                $this->addTable('p_updated');
                $q->orderBy('p_updated.date ASC');
                break;
            case 'updated_DESC' :
                $this->addTable('p_updated');
                $q->orderBy('p_updated.date DESC');
                break;
            default:
                $q->orderBy($source_table . '.id');
        }

        // Получим все необходимые join
        $this->addJoins($q);

        // Отсечем не нужные записи
        if ($this->f_count_products_on_page !== 'ALL'){
            $count_products_on_page = (int)$this->f_count_products_on_page;

            $offset = ($this->f_number_page_current - 1) * $count_products_on_page;
            $q->limit($count_products_on_page);
            $q->offset($offset);
        }
        
        $q->addGroupBy('`'.$this->source->table_1.'`.`id`');

        $list = $q->all();
        
        foreach ($list as $k => $product) {
            $product->source = $this->source;
            $product->baseInfo = $product->info;
        }
        return $list;
    }

    public function getCountProducts(){
        if (!$this->source) {
            throw new \yii\base\InvalidArgumentException();
        }

        $this->tables = [];
        $q = $this->source->class_1::find();
        
        
        // !!! Если менять тут то нужно менять getListProducts
        $q->where(['and',
            $this->getSqlNoCompare(),
            $this->getSqlAsin(),
            $this->getSqlCategoriesRoot(),
            $this->getSqlUser(),
            $this->getSqlComparingImages(),
            $this->getSqlComparisons(),
            //$this->getSqlAddInfoExists(),
            $this->getSqlNoInComparisons(),
            //$this->getSqlSettingsMessage(),
            //$this->getSqlProfile()
        ]); 
        
        $this->addJoins($q);
        
        
        return $q->count();
    }
    
    // Добавить таблицу в $this->tables. Далее они пакетом присоединятся к запросу
    private function addTable($table_name){
        if (!in_array($table_name, $this->tables)){
            $this->tables[] = $table_name;
        }
    }

    private function addJoins(&$q){
        if (!$this->source) {
            throw new \yii\base\InvalidArgumentException();
        }

        $source_table = $this->source->table_1;

        // Получим все необходимые join
        foreach ($this->tables as $table){
            switch ($table) {
                case 'hidden_items':
                    $q->leftJoin('hidden_items', 'hidden_items.p_id = ' . $source_table . '.id ');
                    break;
                case 'comparisons_aggregated':
                    $q->leftJoin('comparisons_aggregated', 'comparisons_aggregated.product_id = ' . $source_table . '.id');
                    break;
                case 'p_all_compare':
                    $q->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $source_table . '.id ');
                    break;
                case '':
                    $q->leftJoin('p_updated', 'p_updated.p_id = ' . $source_table . '.id ');
                    break;
                case 'comparisons':
                    $q->leftJoin('comparisons', 'comparisons.product_id = ' . $source_table . '.id ');
                    break;
                case 'messages':
                    $q->leftJoin('messages', 'messages.id = comparisons.messages_id');
                    break;
            }
        };
        $q->innerJoin($this->source->table_2, $this->source->table_2.'.`asin` = `'.$this->source->table_1.'`.`asin`');
    }
    
    /**
     * Получить модель продуктов согласно всем фильтрам
     * 
     * @return Product
     * @throws \yii\base\InvalidArgumentException
     */
    public function getProduct(){
        if (!$this->source) {
            throw new \yii\base\InvalidArgumentException();
        }

        $source_table = $this->source->table_1;
        $this->tables = [];
        
        $q = $this->source->class_1::find();
        
        $q->where(['and',
            $this->getSqlComparisons(), // where_6
            $this->getSqlNoCompare(),   // $item_1__ignore_red
            $this->getSqlIdGreater()
        ]);
        
        $this->addJoins($q);
        
        $q->orderBy($source_table.'.id ASC');
        $q->limit(1);
        $product = $q->one();
        $product->source = $this->source;
        $product->baseInfo = $product->info;
        return $product;
    }
}
