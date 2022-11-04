<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace backend\services;

/**
 * Класс будет отыечать за передачу данных между запросами
 *
 * @author kosten
 */
class Session extends \yii\web\Session{   
    /** @const int id источника */
    const id_source = 'id_source';
    
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
}
