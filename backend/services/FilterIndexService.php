<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace backend\services;

use backend\services\FiltersIndex;
use backend\models\User;

/**
 * Description of Filter
 *
 * @author koste
 */
class FilterIndexService {
    public $filter_items__profile;
    public $f_items__right_item_show;
    public $f_items__show_n_on_page;
    public $f_items__id;
    public $f_items__comparing_images;
    public $f_items__target_image;
    public $f_items__user;
    public $f_items__comparisons;
    public $f_items__no_compare;
    
    public $source_id;
    public $source_class;
    public $source_table_name;
    
    private $filtersIndex;
    
    public function __construct() {
        $this->filtersIndex = new FiltersIndex();
    }
    
    public function where_1(){
        return $this->filtersIndex->where_1($this->f_items__no_compare, $this->source_id, $this->f_items__comparisons);
    }
    
    public function where_2(){
        return $this->filtersIndex->where_2($this->source_table_name, $this->f_items__id);
    }
    
    public function where_3(){
        return $this->filtersIndex->where_3($this->f_items__target_image);
    }
    
    public function where_4(){
        return $this->filtersIndex->where_4($this->f_items__user);
    }
    
    public function where_5(){
        return $this->filtersIndex->where_5($this->f_items__comparing_images);
    }
    
    public function where_6(){
        return $this->filtersIndex->where_6($this->f_items__comparisons, $this->source_id);
    }
    
    public function where_7(){
        return $this->filtersIndex->where_7($this->source_table_name);
    }
    
    public function where_8(){
        return $this->filtersIndex->where_8();
    }
    
    public function where_9(){
        return $this->filtersIndex->where_9();
    }
    
    public function where_10(){
        return $this->filtersIndex->where_10($this->filter_items__profile, $this->source_table_name);
    }
    
    public function getAllWheres(){
        $where__1 = ['and', $this->where_1(), $this->where_2(), $this->where_3(), $this->where_4(), $this->where_5(), $this->where_6(),$this->where_7(),$this->where_8(),$this->where_10()];
        $where__2 = ['and', $this->where_1(), $this->where_2(), $this->where_3(), $this->where_4(), $this->where_5(), $this->where_6(),$this->where_7(),$this->where_9(),$this->where_10()];

        if (!$this->where_1() && !$this->where_2() && !$this->where_3() && !$this->where_4() && !$this->where_5() && !$this->where_6() && !$this->where_7() && !$this->where_8() && !$this->where_10()){
          $where__1 = ['and', '1+1'];
        }
        if (!$this->where_1() && !$this->where_2() && !$this->where_3() && !$this->where_4() && !$this->where_5() && !$this->where_6() && !$this->where_7() && !$this->where_9() && !$this->where_10()) {
          $where__2 = ['and', '1+1'];
        }

        return ['or', $where__1, $where__2];
    }
    
    /*
     * Получить список всех категорий ( 'Categories: Root' ) и их количество
     */
    public function getWhere_3_list(){
        $cnt = [];
        //$all = Product::find()->all();

        //$q = Product::find()

        $q = $this->source_class::find()
          ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
          ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
          ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ');

        //$q->andWhere(['p_all_compare.p_id' => null]);
        if (0 && $this->source_table_name === 'parser_trademarkia_com'){
          $q->andWhere(['like','info','add_info']);
          $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
          $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
        }
        
        $q->andWhere( $this->where_1());
        //$q->andWhere(['and',['hidden_items.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]]);
        $q->addGroupBy('`'.$this->source_table_name.'`.`id`');
        $all = $q->all();

        foreach ($all as $a_item){
          $c_root = $a_item->info['Categories: Root']; //baseInfo - еще не инициальзированы 
          if (isset($cnt[$c_root])) $cnt[$c_root]++; else $cnt[$c_root] = 1;

        }
        return $cnt;
    }
    
    
    public function getWhere_4_list(){
        $out = [];
        $all = User::find()
          ->where('status > 0')
          ->all();

        foreach ($all as $user){
            $q = $this->source_class::find()
              ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
              ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
              ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
              ->where(['comparisons.user_id' => $user->id]);
            $q->andWhere(['and', $this->where_1(), $this->where_7()]);
            $q->addGroupBy('`'.$this->source_table_name.'`.`id`');
            $c = $q->count();
            if (!$c) $c = 0;

            $cnt[$user->username] = ['id' => $user->id, 'username' => $user->username, 'cnt' => $c];
            $out[$user->id] = $user->username;        
        }
        
        return $cnt;
    }
    
    public function getWhere_6_list(){
        $q = $this->source_class::find()
          ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
          ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
          ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ')
          ->where(['or like', 'status', ['MATCH','%,MATCH,%','MATCH,%','%,MATCH'], false]);

        $q->andWhere( $this->filtersIndex->where_6('NOCOMPARE', $this->source_id) );
        //$q->andWhere( $this->filtersIndex->where_7($this->source_table_name));
        $q->addGroupBy('`comparisons`.`product_id`');

        $match = $q->count();

        $q->where(['like', 'status', 'MISMATCH']);
        $mismatch = $q->count();

        $q->where(['like', 'status', 'PRE_MATCH']);
        $pre_match = $q->count();

        $q->where(['like', 'status', 'OTHER']);
        $other = $q->count();


        $q = $this->source_class::find()
          ->leftJoin('p_all_compare','p_all_compare.p_id = '.$this->source_table_name.'.id ')
          ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
          ->leftJoin('hidden_items','hidden_items.p_id = '.$this->source_table_name.'.id ');
        $q->where(['and',['p_all_compare.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]]);

        if (0 && $this->source_table_name === 'parser_trademarkia_com') {
          $q->andWhere(['like', 'info', 'add_info']);
          $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
          $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
        }

        $q->andWhere(['and',['hidden_items.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $this->source_id]]]);

        $q->addGroupBy('`'.$this->source_table_name.'`.`id`');
        $nocompare = $q->count();


        $out["NOCOMPARE"] = $nocompare;
        $out["MISMATCH"] = $mismatch;
        $out["PRE_MATCH"] = $pre_match;
        $out["MATCH"] = $match;
        $out["OTHER"] = $other;

        return $out;
    }
}
