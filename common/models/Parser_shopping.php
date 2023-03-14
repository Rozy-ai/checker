<?php

namespace common\models;

class Parser_shopping extends Product
{
    protected $_baseInfo = [];
    protected $_addInfo = [];
    protected $_source_id = 4;


    public static function tableName()
    {
        return 'parser_shopping';
    }
}
