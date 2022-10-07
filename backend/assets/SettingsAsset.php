<?php

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class SettingsAsset extends AssetBundle{

    public $css = [
      'css/settings.css',
    ];
    
    public $js = [
      'js/settings.js',
    ];

    public $depends = [
        JqueryAsset::class
    ];
}
