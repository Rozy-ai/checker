<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace backend\services;
use backend\models\User;

/**
 * Description of FiltersIndex
 *
 * @author koste
 */
class FiltersIndex {
    /**
     * $f_items__no_compare
     * $source_id  string       берется из контроллера get параметра
     * $f_items__comparisons
     * @return array
     */
    public function where_1($f_items__no_compare, $source_id, $f_items__comparisons = null){
        if ($f_items__comparisons === 'ALL_WITH_NOT_FOUND') {
            return [];
        }
        return (!$f_items__no_compare)? 
            ['and',['hidden_items.p_id' => null],
                ['OR',['hidden_items.source_id' => null],
                    ['<>','hidden_items.source_id', $source_id]
                ]
            ]:[];
    }
    
    /**
     * @param string $source_table_name
     * @param string $f_items__id
     * @return array
     */
    public function where_2($source_table_name, $f_items__id){
        return ($f_items__id)? 
            ['or',[$source_table_name.'.id' => $f_items__id],
                  [$source_table_name.'.asin' => $f_items__id]
            ]:[];
    }
    
    /**
     * @param string $f_items__target_image берется из контроллера get параметра
     * @return array
     */
    public function where_3($f_items__target_image){
        return ($f_items__target_image)?
            ['like', 'info', '"Categories: Root": "'.$f_items__target_image.'"'] :[];
    }
    
    /**
     * @param string $f_items__user берется из контроллера get параметра
     * @return array
     */
    public function where_4($f_items__user){
        return ($f_items__user)?['like', 'users', $f_items__user]:[];
    }
    
    /**
     * @param string $f_items__comparing_images берется из контроллера get параметра
     * @return array
     */
    public function where_5($f_items__comparing_images){
        return ($f_items__comparing_images)?['like', 'info', str_replace('/','\/',$f_items__comparing_images)]:[];
    }
    
    /**
     * 
     * @param type $f_items__comparisons
     * @param type $source_id
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function where_6($f_items__comparisons, $source_id = null){
        if ($f_items__comparisons === 'NOCOMPARE' && !$source_id) {
            throw new \yii\base\InvalidArgumentException();
        }
        switch ($f_items__comparisons){
            case 'MATCH':       return ['or like', 'comparisons_aggregated.statuses', ['MATCH','%,MATCH,%','MATCH,%','%,MATCH'], false];
            case 'MISMATCH':    return ['or like', 'comparisons_aggregated.statuses', 'MISMATCH'];
            case 'PRE_MATCH':   return ['or like', 'comparisons_aggregated.statuses', 'PRE_MATCH'];
            case 'OTHER':       return ['or like', 'comparisons_aggregated.statuses', 'OTHER'];
            case 'YES_NO_OTHER':return ['and', "`comparisons`.`status` IS NOT NULL AND comparisons.`status` <> 'MISMATCH'"];
            case 'NOCOMPARE':   return ['and',['p_all_compare.p_id' => null],['OR',['hidden_items.source_id' => null],['<>','hidden_items.source_id', $source_id]]];
            case 'ALL_WITH_NOT_FOUND':  return [];
            default:                    return [];
        }
    }
    
    /**
     * 
     * @return type
     */
    public function where_7($source_table_name){
        return ($source_table_name === 'parser_trademarkia_com')?
            ['and',
                ['like', 'info', 'add_info'],
                "info NOT LIKE '%\"add_info\":\"[]\"%'",
                "info NOT LIKE '%\"add_info\": \"[]\"%'"] : [];
    }
    
    /**
     * 
     * @return array
     * @throws BadMethodCallException
     */
    public function where_8(){
        if (!User::isAdmin()) {
            $userId = \Yii::$app->getUser()->id;
            if (!$userId) {
                throw new BadMethodCallException();
            }
            return ["IN", 'comparisons.user_id', [$userId, null]];
        } else {
            return [];
        }
    }
    
    /**
     * @return array
     */
    public function where_9(){
        return ['messages.settings__visible_all' => '1'];
    }
    
    /**
     * 
     * @param type $filter_items__profile
     * @param type $source_table_name
     * @return array
     */
    public function where_10($filter_items__profile, $source_table_name){
        return ($filter_items__profile && $filter_items__profile !== '{{all}}' && $filter_items__profile !== 'Все')?
            ['like', $source_table_name.'.`profile`', $filter_items__profile] : [];
    }
}
