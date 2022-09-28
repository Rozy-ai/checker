<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class IconsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/twbs/bootstrap-icons/font';
    public $baseUrl = '@web';
    public $css = [
        'bootstrap-icons.css',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
}
