<?php

namespace common\models;


class Parser_google_results extends \yii\db\ActiveRecord{
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