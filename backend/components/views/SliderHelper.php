<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of SliderHelper
 *
 * @author koste
 */
class SliderHelper {    
    function getStockLeft12($item){
        if ($grade_key = $item->gradeKey?:0) {
            return '<div class="slider-item__cnt-1">
                        <span class="cnt-1__stock-title __blue-title">Stock:</span>
                        <span class="grade cnt-1__stock-n">'.$item->gradeKey.'</span>
                    </div>';
        }
    }
    
    function getStockRight1($item){
        if ($grade_key = $item->gradeKey?:0) {
            return '<span class="_slider-item__cnt-1">
                        <span class="cnt-1__stock-title __blue-title">Stock:</span>
                        <span class="_grade cnt-1__stock-n">'.$grade_key.' </span>
                    </span>';
        }          
    }
    
    function getSales11($item){
        if ($sales_key = $item->salesKey){
            $sales_key_preg = preg_replace('|\D|','', $sales_key);
            return '<span class="_slider__sales sales"><span class="__blue-title">Sales:</span>'.$sales_key_preg.' </span>';
        } else {
            return '';
        }           
    }
}
