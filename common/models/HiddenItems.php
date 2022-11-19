<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * Это таблица для левых товаров
 */

class HiddenItems extends ActiveRecord{

    const STATUS_NOT_FOUND = 1;
    const STATUS_CHECK = 2;
    const STATUS_ACCEPT = 3;
    const STATUS_NO_ACCEPT = 4;



    public static function tableName(){
        return 'hidden_items';
    }

    public static function getTitleStatuses($k){
         $statusTitles = [
             1 => 'Не найден',
             2 => 'Проверен',
             3 => 'Принят',
             4 => 'Не принят'
        ];
        return $statusTitles[$k];
    }
    
}