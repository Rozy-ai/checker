<?php

namespace backend\components;

use yii\base\Widget;

class AdditionalFilter extends Widget
{
    public $source;
    public $f_asin_multiple;
    public $f_new;
    public $f_favor;
    public $right_filters;
    public $left_filters;
    public $additional_filter_values;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public function run()
    {
        return $this->render('additional-filter', [
            'source' => $this->source,
            'f_asin_multiple' => $this->f_asin_multiple,
            'f_new' => $this->f_new,
            'f_favor' => $this->f_favor,
            'right_filters' => $this->right_filters,
            'left_filters' => $this->left_filters,
            'additional_filter_values' => $this->additional_filter_values,
        ]);
    }
}
