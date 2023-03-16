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
            'key' => 'E_ratingS',
            'name' => 'f_rating_sort',
            'label' => 'Rating Sort',
            'type' => 'sort',
            'values' => [
                SORT_DESC => 'по убыванию ↓',
                SORT_ASC => 'по возрастанию ↑',
            ],
        ],
        [
            'key' => 'eBay_stock',
            'name' => 'f_stock_sort',
            'label' => 'Stock Sort',
            'type' => 'sort',
            'values' => [
                SORT_DESC => 'по убыванию ↓',
                SORT_ASC => 'по возрастанию ↑',
            ],
        ],
        [
            'key' => 'ROI',
            'name' => 'f_price_sort',
            'label' => 'Price Sort',
            'type' => 'sort',
            'values' => [
                SORT_DESC => 'по убыванию ↓',
                SORT_ASC => 'по возрастанию ↑',
            ],
        ],
        [
            'key' => 'Margin',
            'name' => 'f_margin_sort',
            'label' => 'Margin',
            'type' => 'sort',
            'values' => [
                SORT_DESC => 'по убыванию ↓',
                SORT_ASC => 'по возрастанию ↑',
            ],
        ],
    ];
}
