<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class ProductIndexAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/product.css',
    ];
    public $js = [
        'js/index/index.js'
        //'js/product-index.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
    
    public $jsOptions = [
        'type' => 'module',
    ];
    
    public $publishOptions = [
        'forceCopy' => true,
    ];
    
    public function __construct($config = [])
    {
        $this->publishOptions['forceCopy'] = (YII_ENV_DEV || YII_ENV_DEV);
        parent::__construct($config);
    }
}
