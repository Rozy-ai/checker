<?php

namespace common;

use yii\base\BootstrapInterface;
use yii\di\Instance;
use common\models\Filters;
use backend\presenters\IndexPresenter;
use backend\presenters\ProductPresenter;

/**
 * Автозагрузка сервисов
 *
 * @author kosten
 */
class SetupChecker implements BootstrapInterface {
    public function bootstrap($app) {
        $container = \Yii::$container;
        
        //Подключаем класс Filters
        $container->setSingleton(Filters::class, function() {
            return new Filters();
        });
        
        //Сервис IndexPresenter
        //$container->setSingleton(IndexPresenter::class, [], [Instance::of(IndexPresenter::class)]);
        
        //Сервис ProductPresenter
        //$container->setSingleton(ProductPresenter::class, [], [Instance::of(ProductPresenter::class)]);
    }
}
