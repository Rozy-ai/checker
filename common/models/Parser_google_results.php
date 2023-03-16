<?php

namespace common\models;


class Parser_google_results extends \yii\db\ActiveRecord
{
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
                'ROI' => 'Price',
                'Margin' => 'Margin'
            ]
        ],
        [
            'key' => 'Sort Order',
            'name' => 'f_right_sort_order',
            'label' => 'Sort Order',
            'type' => 'sort',
            'values' => [
                SORT_DESC => 'по убыванию ↓',
                SORT_ASC => 'по возрастанию ↑',
            ],
        ],
    ];
}
