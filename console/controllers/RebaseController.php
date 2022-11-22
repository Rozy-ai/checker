<?php

namespace console\controllers;

use yii\console\Controller;
use common\models\Filters;
use common\models\Product;
use common\models\Comparison;
use common\models\Source;


class RebaseController extends Controller {

public function actionStart() {
        $source = Source::getById(1);
        if (!($source instanceof Source)) {
            echo "Не удалось найти источник";
            return 1;
        }

        $filters = new Filters();
        $filters->setToDefault();
        $filters->f_source = $source->id;

        $products = Product::getListProducts($source, $filters, true);
        if (!is_array($products) || !count($products)) {
            echo "Не удалось получить список продуктов";
            return 1;
        }

        $all = count($products);
        $k = 100 / $all;
        if ($k == 0) {
            echo "Нет товаров для преобразования";
            return 1;
        }

        $count_rebased = 0;
        foreach ($products as $i => $product) {                     
            $items = $product->addInfo;
            foreach ($items as $index => $item) {               
                $item->source = $source;
                print_r($item->id);
                exit;                    
            }
        }
        echo "count rebased = " . $count_rebased;
        return 0;
    }

}
