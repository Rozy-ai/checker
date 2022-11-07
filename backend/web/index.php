<?php
/*
  $error = error_get_last();

  if( $error !== NULL) {
    $errno   = $error["type"];
    $errfile = $error["file"];
    $errline = $error["line"];
    $errstr  = $error["message"];

    file_put_contents(__DIR__.'/temp/500.log', date("Y-m-d H:i:s").' :: '.$errstr.' :: '.$errfile.' :: '.$errline.PHP_EOL, FILE_APPEND | LOCK_EX);
  }
}*/

if (!empty(getenv('IS_DEV')) || !empty($_ENV['IS_DEV'])) {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_ENV') or define('YII_ENV', 'dev');
}

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);


(new yii\web\Application($config))->run();
