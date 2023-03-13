<?php

namespace common\models;


class Parser_google_results extends \yii\db\ActiveRecord{
    public static $filters = [
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