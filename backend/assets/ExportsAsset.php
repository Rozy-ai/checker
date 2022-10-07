<?php

namespace backend\assets;

use yii\jui\JuiAsset;
use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class ExportsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
    ];
    
    public $js = [
        'js/exports.js',
    ];
    public $depends = [
        JuiAsset::class,
    ];
}
