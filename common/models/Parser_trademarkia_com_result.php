<?php

namespace common\models;

class Parser_trademarkia_com_result extends \yii\db\ActiveRecord
{
    public static $filters = [
        [
            'key' => 'eBay_stock',
            'name' => 'f_stock',
            'label' => 'Stock',
            'type' => 'number',
            'range' => false,
        ],
        [
            'key' => 'E_ratingS',
            'name' => 'f_rating',
            'label' => 'Rating',
            'type' => 'number',
            'range' => false,
        ],
        [
            'key' => 'E_feedb',
            'name' => 'f_feedback',
            'label' => 'Feedback (%)',
            'type' => 'number',
            'range' => false,
        ],
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
                'E_ratingS' => 'Rating',
                'eBay_stock' => 'Stock',
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
