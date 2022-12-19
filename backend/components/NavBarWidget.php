<?php

namespace backend\components;

use yii\base\Widget;
use common\models\Source;

class NavBarWidget extends Widget{

  public function run(){
    $sources = Source::find()->select(['id', 'name'])->all();
    $sourceItems = array_map(fn($item) => ['label' => $item->name, 'url' => ['product/index', 'src' => $item->id], 'visible' => $item->checkAccess()], $sources);
    return $this->render('nav-bar', [
        'sourceItems' => $sourceItems
    ]);
  }
}