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
            'label' => "<div class='d-flex " . ($item->country ?  'justify-content-around' : '') . "'><div class='p-1'>" . $item->name . "</div>" . "<div class='p-1'>" . ($item->country ? Html::img('@web/img/flags-normal/'.$item->country.'.png', ['alt' => '', 'style'=>['height' => 'auto', 'width'=> '30px']]) : '') . "</div>" . "</div>",
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