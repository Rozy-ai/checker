<?php

namespace common;

use yii\base\BootstrapInterface;
use yii\di\Instance;
use common\models\Filters;

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
    }
}
