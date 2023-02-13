<?php

namespace backend\components;

use Yii;
use yii\base\Widget;
use common\models\Source;
use yii\helpers\Html;

class NavBarWidget extends Widget {

    public function run() {
        $sources = Source::find()->select(['id', 'name', 'country'])->all();

        $sourceItems = array_map(fn($item) => [
            'label' => $item->name . ($item->country ? Html::img('@web/img/flags-normal/'.$item->country.'.png', ['alt' => '', 'style'=>['height' => 'auto', 'width'=> '30px']]) : ''),
            'url' => ['product/index', 'src' => $item->id],
            'encode' => false,
            'visible' => $item->checkAccess()
        ], $sources);

        return $this->render('nav-bar', [
            'sourceItems' => $sourceItems,
            'encodeLabels' => false,
        ]);
    }

}