<?php

namespace common\models;

use yii\db\ActiveRecord;

class HiddenItems extends ActiveRecord{

    const STATUS_NO_CHECK = 0;
    const STATUS_NOT_FOUND = 1;
    const STATUS_CHECK = 2;
    const STATUS_ACCEPT = 3;
    const STATUS_NO_ACCEPT = 4;



    public static function tableName(){
        return 'hidden_items';
    }

    public static function getTitleStatuses($k){
        $statusTitles = [
            'Не выбран',
            'Не найден',
            'Проверен',
            'Принят',
            'Не принят'
        ];
        return $statusTitles[$k];
    }




}