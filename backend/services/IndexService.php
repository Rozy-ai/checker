<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace backend\services;

use backend\services\FilterService;
use common\models\Source;
use backend\models\User;
use common\models\Stats_import_export;
use common\models\Comparison;

/**
 * Сервис является помощником. Собран весь шлак, что был в контроллере
 * И служит для принятия и подготовки данных на странице index
 * Возможно переписать как presenter c интерфейсом для отображения
 *
 * @author kosten
 */
class IndexService {

    private Filters $filters;
    public  Source $source;
    
    private $filter_items__sort;
    private $numberPage;   
    private $f_items__show_n_on_page;
    /**
     * 
     * @param FilterService $filters - загружается автоматически при первом же вызове и один раз
     */
    public function __construct(Filters $filters) {
        $this->filters = $filters;
    }
    
    /**
     * Функция загружает входные параметры. 
     * @param type $params массив входных данных
     * @return string
     */
    public function loadParams() {
        $session = \Yii::$app->session;
        $this->filters->loadFromSession();
        $this->filters->source_id = $this->source->id;
        $this->filters->source_class = $this->source->class_1;
        $this->filters->source_table_name = $this->source->table_1;
        
        $this->filter_items__sort = $session[Session::filter_items_sort];
        $this->numberPage         = $session[Session::pager_page]?: $session[Session::pager_page]=1;
        $this->f_items__show_n_on_page = $session[Session::pager_on_page]?: $session[Session::pager_on_page] = 10;
    }
    
    // Временная функция
    public function test(){
        $q = $this->source->class_1::find()
            ->where( $this->filters->where_2() );
        
        print_r($q->createCommand()->getRawSql());
        exit;        
    }

    public function getFilterItemsComparisons() {
        return $this->filters->f_items__comparisons;
    }
    
    public function getItemsRightItemShow(){
        return $this->filters->f_items__right_item_show;
    }
    
    public function getItemsProfile(){
        return $this->filters->f_items__profile;
    }
    
    public function getCountProductsOnPage(){
        return $this->f_items__show_n_on_page;
    }

    /**
     * Получить список всех категорий ( 'Categories: Root' ) и их количество
     * @return attay
     *    [
     *       string => int,
     *       string => int,
     *       ...
     *       string => int
     *    ]
     */
    public function getWhere_3_list() {
        if (!$this->source) {
            throw new \yii\base\InvalidParamException();
        }

        $cnt = [];
        $source_table_name = $this->source->table_1;
        $source_class = $this->source->class_1;

        $q = $this->source->class_1::find()
                ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $source_table_name . '.id ')
                ->leftJoin('comparisons', 'comparisons.product_id = ' . $source_table_name . '.id ')
                ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $source_table_name . '.id ');

        // if (0 && $this->source->table_1 === 'parser_trademarkia_com'){
        //    $q->andWhere(['like','info','add_info']);
        //    $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
        //    $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
        // }

        $q->andWhere($this->filters->where_1());                  //Кроме скрытых элементов
        //$q->addGroupBy('`' . $source_table_name . '`.`id`');
        $all = $q->all();

        foreach ($all as $a_item) {
            $c_root = $a_item->info['Categories: Root']; //baseInfo - еще не инициальзированы
            if (isset($cnt[$c_root])) {
                $cnt[$c_root]++;
            } else {
                $cnt[$c_root] = 1;
            }
        }
        return $cnt;
    }

    /**
     * Список пользователей с количеством сравнений для каждого
     * @return attay
     *    [
     *       username => [
     *          'id'        => int,
     *          'username'  => string, //Зачем дубль - пока не знаю
     *          'cnt'       => int
     *       ],
     *       ...
     *    ]
     */
    public function getWhere_4_list() {
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
                    ->where($this->filters->where_1())
                    ->andWhere(['comparisons.user_id' => $user->id]);

            $q->addGroupBy('`' . $this->source->table_1 . '`.`id`');
            $c = $q->count();
            if (!$c) {
                $c = 0;
            }

            $cnt[$user->username] = ['id' => $user->id, 'username' => $user->username, 'cnt' => $c]; //Зачем дубль - пока не знаю
        }

        return $cnt;
    }

    /**
     * 
     * @return attay
     *    [
     *       NOCOMPARE => int,
     *       MISMATCH  => int,
     *       PRE_MATCH => int,
     *       MATCH     => int,
     *       OTHER     => int
     *    ]
     */
    public function getWhere_6_list() {
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
     * Тут не поятная ересь (нужно проанализироварть)
     * Не используется
     * @return type
     * @throws \yii\base\InvalidParamException 
     */
    public function profiles_list_cnt() {
        if (!$this->source) {
            throw new \yii\base\InvalidParamException();
        }
        $source_class = $this->source->class_1;
        //$q = $source_class::find()->distinct(true)->select(['profile'])->asArray();
        /* @var $source_class ActiveRecord */
        $q2 = $source_class::find()
                ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source->table_1 . '.id ')
                ->innerJoin($this->source->table_2, $this->source->table_2 . '.`asin` = ' . $this->source->table_1 . '.asin')
                ->where(['and', ['hidden_items.p_id' => null], ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source->id]]])
                ->asArray();

        $q2->select($this->source->table_1 . '.id, ' . $this->source->table_1 . '.profile')
                ->groupBy($this->source->table_1 . '.id ');
        
        print_r($q->createCommand()->getRawSql());
        exit;             

        $q2_load = $q2->all();
        $q2_load_cnt = $q2->count();

        $res_handler = function ($data) {
            // нужно отдать
            //
            // array
            //    [profile] => General
            //    [cnt] => 267
            // array
            //    [profile] => Prepod_
            //    [cnt] => 1

            $out_1 = [];

            foreach ($data as $item) {
                if (!isset($out_1[$item['profile']])) {
                    $out_1[$item['profile']] = 0;
                }
                $out_1[$item['profile']] += 1;
            }

            $out_2 = [];

            foreach ($out_1 as $k => $v) {
                $arr['profile'] = $k;
                $arr['cnt'] = $v;
                $out_2[] = $arr;
            }

            return $out_2;
        };

        $q2_res = $res_handler($q2_load);

        //$cnt_all = $q1->one()['cnt'] ?? 0;
        // $q2_res = $q2->all();
        /*
          [0] => Array
          (
          [profile] => General
          [cnt] => 267
          )

          [1] => Array
          (
          [profile] => Prepod_
          [cnt] => 1
          )

          [2] => Array
          (
          [profile] => General_2
          [cnt] => 23
          )

          [3] => Array
          (
          [profile] => Prepod
          [cnt] => 41
          )
         *
         * */

        $profile_list = [];

        if ($q2_res) {
            foreach ($q2_res as $item) {
                //$item = strtolower($item);
                $e_items = explode(',', $item['profile']);
                $profile_list['cnt'][$item['profile']] = $item['cnt'];

                foreach ($e_items as $e_item) {
                    $e_item = trim($e_item);
                    $profile_list[$e_item] = $e_item;

                    if (count($e_items) > 1) {
                        $profile_list['cnt'][$e_item] += $item['cnt'];
                    }
                }
            }
        }

        // это если считать каждое значение в товаре например и  General + Prepod_ + General_2 даже если они в одном товаре
        $all = function ($profile_list) {
            $out = 0;
            foreach ($profile_list as $k => $item) {
                if ($k === 'cnt') {
                    continue;
                }
                $out += $profile_list['cnt'][$item];
            }
            return $out;
        };

        $concat_cnt_to_list = function ($profile_list) {
            $out = [];
            foreach ($profile_list as $k => $item) {
                if ($k === 'cnt') {
                    continue;
                }
                $out[$k] = $item . ' (' . $profile_list['cnt'][$k] . ')';
            }
            return $out;
        };

        $list_out = $concat_cnt_to_list($profile_list);

        $all_cnt = $all($profile_list);
        // !!!! что считать если один товар содержит 3 значения то товар же один ... дак выводить цифру 1 или 3
        $a['{{all}}'] = 'Все (' . $q2_load_cnt . ')';

        return array_merge($a, $list_out);
    }
    
    /**
     * Тут не понятная ересь 2
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
     * @return type
     */
    public function profiles_list_cnt_2() {
        /* @var $source_class ActiveRecord */
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

    public function cnt_filter_statuses($profile) {
        if ($profile) {
            $this->filters->filter_items__profile = $profile;
        }
        /*
          $out['YES_NO_OTHER'] = [
          'hex_color' => '',
          'name' => 'Result',
          'name_2' => 'Все отмеченные',
          ];
         */
        $statuses = Comparison::get_filter_statuses();

        foreach ($statuses as $name => $data) {
            $q_cnt = $this->prepare_record_1($name, $this->filters->filter_items__profile);
            $res_cnt = $q_cnt->count();
            $statuses[$name]['cnt'] = $res_cnt;
        }

        return $statuses;
    }

    /**
     * Получить общее количество левыx продуктов, подходящих под действие всех фильтров
     * 
     * @return int
     */
    public function getCountProducts() {
        if (!$this->source) {
            throw new \yii\base\InvalidArgumentException();
        }
        $where = $this->filters->getAllWheres();
        $q = $this->source->class_1::find()
                ->leftJoin('comparisons_aggregated', 'comparisons_aggregated.product_id = ' . $this->source->table_1 . '.id')
                ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('p_updated', 'p_updated.p_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('comparisons', 'comparisons.product_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('messages', 'messages.id = comparisons.messages_id')
                ->where($where);
        return $q->count();
    }

    /**
     * Плучить список продуктов(слева) для вывода, согласно всех фильтров
     * Есть огнаричение количества на вывод 
     */
    public function getProducts() {
        if (!$this->source) {
            throw new \yii\base\InvalidArgumentException();
        }

        $where = $this->filters->getAllWheres();
        $q = $this->source->class_1::find()
                //->select('*') todo: Нужно перечислить только нужные поля для оптимизации
                ->leftJoin('comparisons_aggregated', 'comparisons_aggregated.product_id = ' . $this->source->table_1 . '.id')
                ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('p_updated', 'p_updated.p_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('comparisons', 'comparisons.product_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('messages', 'messages.id = comparisons.messages_id')
                ->where($where);
        //print_r($q->createCommand()->getRawSql());
        //exit;
        
        $q->innerJoin($this->source->table_2, $this->source->table_2 . '.`asin` = ' . $this->source->table_1 . '.asin');

        switch ($this->filter_items__sort) {
            case 'created_ASC': $q->orderBy($this->source->table_1 . '.date_add ASC');
                break;
            case 'created_DESC': $q->orderBy($this->source->table1 . '.date_add DESC');
                break;
            case 'updated_ASC' : $q->orderBy('p_updated.date ASC');
                break;
            case 'updated_DESC' :$q->orderBy('p_updated.date DESC');
                break;
            default: $q->orderBy($this->source->table_1 . '.id');
        }

        $q->addGroupBy('`' . $this->source->table_1 . '`.`id`');

        // Рассчитываем нужные для вывода продукты 
        if ($this->f_items__show_n_on_page !== 'ALL') {
            $f_items__show_n_on_page = (int) $this->f_items__show_n_on_page;
            $offset = ($this->numberPage - 1) * $f_items__show_n_on_page;
            $q->limit($f_items__show_n_on_page);
        } else {
            $offset = 0;
        }
        $q->offset($offset);

        //Получаем нужные продукты (список слева)
        $list = $q->all();

        // В каждый эленент добавляется дополнительна информация
        foreach ($list as $k => $product) {
            $product->source = $this->source;
            $product->baseInfo = $product->info; // Нужно для фицированного поля baseInfo
            $product->initAddInfo();
        }
        return $list;
    }
    
    public function getCountProductsRight($list){
        $cnt_all_right = 0;
        foreach ($list as $k => $product) {
            $items = $product->addInfo;
            $cnt_all_right += count($items);
        }
        return $cnt_all_right;
    }
    
    public function get_last_local_import() {
        $s_import = Stats_import_export::find();
        $s_import->where(['type' => 'IMPORT']);
        $s_import->orderBy(['created' => SORT_DESC]);
        $s_import->limit(1);
        return $s_import->one();
    }

    /**
     * @param $comparison
     * @return \yii\db\ActiveQuery
     */
    private function prepare_record_1($comparison, $profile) {
        /* @var $source_class yii\db\ActiveRecord */
        $source_class = $this->source->class_1;

        $q = $this->prepare_get_joined($source_class);

        //->where(['<=>','hidden_items.source_id', $this->source->id])
        //->where('comparisons_aggregated.source_id = '.(int)$this->source->id );


        if (0 && $this->source->table_1 === 'parser_trademarkia_com') {
            $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
            $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
        } else {
            $q->innerJoin($this->source->table_2, $this->source->table_2 . '.`asin` = ' . $this->source->table_1 . '.asin');
        }

        $q->addGroupBy('`' . $this->source->table_1 . '`.`id`');

        $where_0 = [];
        if (0 && $this->source->table_1 === 'parser_trademarkia_com') {
            $where_0 = ['like', 'info', 'add_info'];
        }

        $where_2 = [];
        $where_3 = ['and',
            ['hidden_items.p_id' => null],
            ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source->id]],
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
            $where_2 = ['and', ['p_all_compare.p_id' => null], ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source->id]]];
        } else {
            $where_2 = ['`comparisons`.`status`' => $comparison];
        }

        $where = ['and', $where_0, $where_2, $where_3];

        if ($profile && $profile !== '{{all}}' && $profile !== 'Все') {
            $q->andWhere(['like', $this->source->table_1 . '.`profile`', $profile]);
        }

        $q->andWhere($where);

//    echo '<pre>'.PHP_EOL;
//    print_r($q->createCommand()->getRawSql());
//    echo PHP_EOL;
//    exit;

        return $q;
    }

    public function getPager(int $countProducts) {
        $pages_cnt = ($this->f_items__show_n_on_page !== 'ALL') ?
                ceil($countProducts / (int) $this->f_items__show_n_on_page) : 1;

        return $this->simple_pager($pages_cnt, $this->numberPage);
    }

    public function simple_pager($pages_cnt, $page_n, $left_right_n = 3) {
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

    private function prepare_get_joined($source_class) {
        return $q = $source_class::find()
                //->select('*')
                //->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
                ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $this->source->table_1 . '.id ')
                ->leftJoin('comparisons', 'comparisons.product_id = ' . $this->source->table_1 . '.id ');
    }

}
