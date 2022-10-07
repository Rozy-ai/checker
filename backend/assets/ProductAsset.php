<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class ProductAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/framework.css',
        'css/slick.css',
        'css/style.css',
        'css/product.css',
        'https://unpkg.com/swiper@8/swiper-bundle.min.css',
        'js/zoom/jquery.ez-plus.css',
    ];

    public $js = [
        'js/zoom/jquery.ez-plus.js',
        'js/clipboard/clipboard.js',
        'js/product.js',
        'js/common.js',
        'https://unpkg.com/swiper@8/swiper-bundle.min.js',
    ];
    public $depends = [
        SlickAsset::class,
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
}

