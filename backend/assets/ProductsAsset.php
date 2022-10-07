<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class ProductsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/framework.css',
        'css/slick.css',
        'css/style.css',
        'css/product.css',
    ];
    
    public $js = [
      'js/clipboard/clipboard.js',
      'js/products.js',
      'js/common.js',

    ];
    public $depends = [
        SlickAsset::class,
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
}
