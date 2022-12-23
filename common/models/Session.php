<?php

namespace common\models;

/**
 * @author kosten
 */
class Session extends \yii\web\Session{      
    public function saveFromParams(array $params){
        foreach ($params as $key => $value){
            //if ($value) {
                $this->set($key, $value);
            //}
        }
    }
    
    public function loadToArray(){
        return [
            'f_profile'                 => $this->get('f_profile'),
            'f_count_products_on_page'  => $this->get('f_count_products_on_page'),
            'f_number_page_current'     => $this->get('f_number_page_current'),
            'f_id'                      => $this->get('f_id'),
            'f_asin'                    => $this->get('f_asin'),
            'f_categories_root'         => $this->get('f_categories_root'),
            'f_title'                   => $this->get('f_title'),
            'f_status'                  => $this->get('f_status'),
            'f_username'                => $this->get('f_username'),
            'f_comparison_status'       => $this->get('f_comparison_status'),
            'f_no_compare'              => $this->get('f_no_compare'),
            'f_source'                  => $this->get('f_source'),
            'f_sort'                    => $this->get('f_sort'),
            'f_detail_view'             => $this->get('f_detail_view'),
            'f_batch_mode'              => $this->get('f_batch_mode'),
            'f_hide_mode'               => $this->get('f_hide_mode')
        ];
    }
}
