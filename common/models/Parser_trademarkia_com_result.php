<?php

namespace common\models;

class Parser_trademarkia_com_result extends \yii\db\ActiveRecord{
    public static $filters = [
        'eBay_stock' => [
            'name' => 'f_stock',
            'label' => 'Stock',
            'type' => 'integer',
            'range' => false,
        ],
        'E_ratingS' => [
            'name' => 'f_rating',
            'label' => 'Rating',
            'type' => 'integer',
            'range' => false,
        ],
        'E_feedb' => [
            'name' => 'f_feedback',
            'label' => 'Feedback (%)',
            'type' => 'integer',
            'range' => false,
        ],
        'ROI' => [
            'name' => 'f_roi',
            'label' => 'ROI',
            'type' => 'integer',
            'range' => true,
        ],
        'Margin' => [
            'name' => 'f_margin',
            'label' => 'Margin',
            'type' => 'integer',
            'range' => true,
        ]
    ];
}