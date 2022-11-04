<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of ParamsQuery
 *
 * @author koste
 */
class ParamsQuery {
    public $filter_items__profile;
    public $f_items__right_item_show;       // Не используется
    public $f_items__show_n_on_page;        // Не используется
    public $f_items__id;                    // (where_2) id товара из поля формы для ввода id
    public $f_items__target_image;          // (where_3) categiries_root (select)
    public $f_items__user;                  // (where_4) username пользователя
    public $f_items__comparing_images;      // (where_5) Title
    public $f_items__comparisons;           // (where_6) Фильтр выбора товара из поля выбора из Comparisons
                                            // (where_7) Не используется
                                            // (where_8) Выносим в User
    public $f_items__no_compare;            // Всегда null. Раньше было поле с возможностью отключения
}
