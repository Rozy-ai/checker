<?php

namespace common;

use yii\base\BootstrapInterface;
use yii\di\Instance;
use backend\services\FilterService;
use backend\services\IndexService;

/**
 * Автозагрузка сервисов
 *
 * @author kosten
 */
class SetupChecker implements BootstrapInterface {
    public function bootstrap($app) {
        $container = \Yii::$container;

        //Подключаем сервис c помощью анонимной функции
        $container->setSingleton(FilterService::class, function() {
            return new FilterService();
        });
        
        $container->setSingleton(IndexService::class, [], [Instance::of(FilterService::class)]);
    }
}
