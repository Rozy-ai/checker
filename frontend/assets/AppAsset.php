<?php

namespace frontend\assets;

use yii\bootstrap4\BootstrapAsset;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://use.fontawesome.com/releases/v5.3.1/css/all.css',
        'css/site.css',
    ];
    public $js = [
        'js/app.js'
    ];
    public $depends = [
        YiiAsset::class,
        BootstrapPluginAsset::class,
        BootstrapAsset::class
    ];
}
