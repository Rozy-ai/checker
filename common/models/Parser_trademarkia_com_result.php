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
            'key' => 'E_Feedb',
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
            'key' => 'Sort Order',
            'name' => 'f_right_sort_order',
            'label' => 'Sort Order',
            'type' => 'sort',
            'values' => [
                'E_ratingS' => [
                    'label' => 'Rating',
                    'order' => SORT_DESC,
                ],
                'eBay_stock' => [
                    'label' => 'Stock',
                    'order' => SORT_DESC,
                ],
                'eBay_price' => [
                    'label' => 'Price',
                    'order' => SORT_ASC,
                ],
            ]
        ],
    ];
}
