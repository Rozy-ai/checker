<?php

/*
 * Список функций для получения списков чеголибо согласно фильтрам
 * Необходимы переменные:
 * 
 * getListCategoriesRoot()
 * getListUser()
 * getListComparisonStatuses()
 * getListProfiles()
 * getListProduct()
 * getCountProducts()
 * 
 */

namespace backend\presenters;

use common\models\Filters;
use common\models\Product;
use common\models\Source;
use common\models\User;
use common\models\Comparison;
use common\models\FiltersQuery;

/**
 * Description of TraitListOfFilters
 *
 * @author kosten
 */
trait TraitListFilters {
    private $source_table_name;
    private $source_table_class;
    private $source_table2_name;
    private $source_table2_class;
    
    public function loadTraitListFilters(Source $source){
        $this->source_table_class = $source->class_1;
        $this->source_table_name  = $source->table_1;
        $this->source_table2_name = $source->table_2;
        $this->source_table2_class= $source->class_2;
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
        if (!$this->source_table_name) {
            throw new \yii\base\InvalidParamException();
        }
        $list = \Yii::$app->db->createCommand(
            'SELECT info->\'$."Categories: Root"\' as cat, count(*) as count FROM '.$this->source_table_name.' GROUP BY cat')->queryAll(); 

        $new_list = [];
        if (is_array($list)){
            foreach ($list as $data){
                $new_list[preg_replace('~^"?(.*?)"?$~', '$1', $data['cat'])] = $data['count'];
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
        if (!$this->source_table_class || !$this->source_table_name) {
            throw new \yii\base\InvalidParamException();
        }
        
        $name_user = User::tableName();
        $name_comparisons  = Comparison::tableName();
        
        // select user.username, count(comparisons.status) as count_comparisons
        // from comparisons
        // left join user on user.id = comparisons.user_id
        // group by user.username;
        $all = Comparison::find()
           ->select([
               $name_user.'.id', 
               $name_user.'.username as name', 
               'count('.$name_comparisons.'.status) as count'])
           ->leftJoin($name_user, $name_user.'.id='.$name_comparisons.'.user_id')
           ->groupBy($name_user.'.username')
           ->asArray()
           ->indexBy('id')
           ->all();
        return $all;
        /*
        $all = User::find()
                ->select(['username'])
                ->where('status > 0')
                ->all();

        foreach ($all as $user) {
            $q = new FiltersQuery($this->source_table_class);
            $q->where(['and',
                $q->getSqlNoCompareItems($filters->f_no_compare, $filters->f_source),
                ['comparisons.user_id' => $user->id]
            ]);
            
            $q = $this->source_table_class::find()
                    ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $this->source_table_name . '.id ')
                    ->leftJoin('comparisons', 'comparisons.product_id = ' . $this->source_table_name . '.id ')
                    ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source_table_name . '.id ')
                    ->where($this->getSqlNoCompare())
                    ->andWhere(['comparisons.user_id' => $user->id]);

            $q->addGroupBy('`' . $this->source_table_name . '`.`id`');
            $c = $q->count();
            if (!$c) {
                $c = 0;
            }

            $cnt[$user->username] = ['id' => $user->id, 'cnt' => $c];
        }

        return $cnt;
        * 
        */
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
     *       NOCOMPARE => [
     *          'name' => 'abcdf',
     *          'count'=> '12345'
     *       ],
     *       MISMATCH  => [...]
     *       PRE_MATCH => [...]
     *       MATCH     => [...]
     *       OTHER     => [...]
     *    ]
     * @throws \yii\base\InvalidArgumentException
     */
    public function getListComparisonStatuses(bool $is_admin, $f_profile){
        //return Comparison::getFilterStatuses();
        
        if (!$this->source_table_class || !$this->source_table_name || !$this->source_table2_name) {
            throw new \yii\base\InvalidParamException();
        }                
                
        $q = new FiltersQuery($this->source_table_class);
        
        $q->select(['comparisons.status', 'COUNT(*) as count_statuses'])
          ->andWhere($q->getSqlProfile($is_admin, $this->source_table_name, $f_profile))
          ->groupBy('comparisons.status')
          ->asArray();
        
        $q->addTable('comparisons');
        $q->addJoins($this->source_table_name);            
        $q->indexBy('status');
                     
        $data = $q->all();
        $data['NOCOMPARE']=$data[null];
        
        // Приведем к нужному формату(Важен порядок):
        $list_comparisons = Comparison::getFilterStatuses();
        $out = [];
        foreach ($list_comparisons as $key => $val){            
            if ($data[$key]){
                $out[$key] = [
                        'name' => $val['name'],
                        'count' => $data[$key]['count_statuses'],                      
                    ];                
                $count[$key] = $data[$key]['count_statuses'];
                $name[$key] = $val['name'];
                
            }
        }                
        /** Выставляем значение по умолчанию согласно Checker_back5 п.3
         *  найбольшее кол-во товаров справа первым */
        array_multisort($count_result,SORT_DESC,$name,SORT_ASC,$out);
        
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
        if (!$this->source_table_class) {
            throw new \yii\base\InvalidParamException();
        }
       /* //Получить уникальные значения столбца profile
        $q0 = $this->source_table_class::find()->distinct(true)->select(['profile'])->asArray();
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

        $q2 = $this->source_table_class::find()
                ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source_table_name . '.id ')
                ->innerJoin($this->source_table2_name, $this->source_table2_name . '.`asin` = ' . $this->source_table_name . '.asin')
                ->where(['and', ['hidden_items.p_id' => null], ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source->id]]]);

        $q2->asArray();

        $q2->select($this->source_table_name . '.id, ' . $this->source_table_name . '.profile')
                ->groupBy($this->source_table_name . '.id ');

        $list_out = [];
        //$q2_load = $q2->all();
        $q2_load_cnt = $q2->count();
        foreach ($profiles_uniq as $p_name) {
            $q2_tmp = clone $q2;
            $q2_tmp->andWhere(['like', $this->source_table_name . '.`profile`', $p_name]);
            $list_out[$p_name] = $p_name . ' (' . $q2_tmp->count() . ')';
        }

        // !!!! что считать если один товар содержит 3 значения то товар же один ... дак выводить цифру 1 или 3
        $a['{{all}}'] = 'Все (' . $q2_load_cnt . ')';*/
        $user = \Yii::$app->user->identity;
        $is_admin = $user && $user->isAdmin();
        $filters = new Filters();
        $filters->loadFromSession();

        $filters->f_profile = 'General';
        $count_general = Product::getCountProducts($this->source, $filters, $is_admin);
        if ($count_general) {
            $profiles['General'] = "General ($count_general)";
        }

        $filters->f_profile = 'Free';
        $count_free = Product::getCountProducts($this->source, $filters, $is_admin);
        if ($count_free) {
            $profiles['Free'] = "Free ($count_free)";
        }

        $filters->f_profile = $user->username;
        $count_login = Product::getCountProducts($this->source, $filters, $is_admin);
        if($count_login > 0)
            $profiles[$user->username] = "{$user->username} ($count_login)";
        return $profiles;
    }
}
