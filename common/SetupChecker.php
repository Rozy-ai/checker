<?php

namespace common;

use yii\base\BootstrapInterface;
use yii\di\Instance;
use backend\services\Filters;
use backend\services\IndexService;

//use backend\services\MySession;

/**
 * Автозагрузка сервисов
 *
 * @author kosten
 */
class SetupChecker implements BootstrapInterface {
    public function bootstrap($app) {
        $container = \Yii::$container;

        //Подключаем класс сессии
        ///$container->setSingleton(MySession::class, function() {
        //    return new MySession();
        //});    
        
        //Подключаем сервис c помощью анонимной функции
        $container->setSingleton(FilterService::class, function() {
            return new FilterService();
        });
        
        //Сервис Service
        $container->setSingleton(IndexService::class, [], [Instance::of(Filters::class)]);
    }
}
