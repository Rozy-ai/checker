<?php

namespace common\models;


class Parser_shopping_results extends \yii\db\ActiveRecord{
    public static $filters = [
        [
            'key' => 'ROI',
            'name' => 'f_roi',
            'label' => 'ROI',
            'type' => 'number',
            'range' => true,
        ],
        [
            'key' => 'Margin',
            'name' => 'f_margin',
            'label' => 'Margin',
            'type' => 'number',
            'range' => true,
        ],
        [
            'key' => 'Sort Field',
            'name' => 'f_right_sort_field',
            'label' => 'Sort Field',
            'type' => 'sort',
            'values' => [
                'Price' => [
                    'label' => 'Price',
                    'order' => SORT_ASC,
                ],
            ]
        ],
    ];
}