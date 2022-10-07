<?php

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Main backend application asset bundle.
 */
class SlickAsset extends AssetBundle
{
    public $sourcePath = '@bower/slick';

    public $css = [
        'slick.css',
        'slick-theme.css'
    ];
    
    public $js = [
        'slick.js',
    ];

    public $depends = [
        JqueryAsset::class
    ];
}
