<?php

namespace common\models;

class Parser_shopping extends Product
{
    protected $_baseInfo = [];
    protected $_addInfo = [];
    protected $_source_id = 4;

    public static $filters = [
        'Reviews: Rating' => [
            'name' => 'f_reviews',
            'label' => 'Reviews Rating',
            'type' => 'integer',
            'range' => false,
            'field' => 'info',
            'json' => true,
        ]
    ];

    public static function tableName()
    {
        return 'parser_shopping';
    }
}
