<?php

namespace backend\components;

use yii\base\Widget;

class AdditionalFilter extends Widget
{
    public $f_asin_multiple;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public function run()
    {
        return $this->render('additional-filter', [
            'f_asin_multiple' => $this->f_asin_multiple,
        ]);
    }
}
