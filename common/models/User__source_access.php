<?php

namespace common\models;


use yii\db\ActiveRecord;

class User__source_access extends ActiveRecord{
    public static function getById($id_source, $id_user){
        return self::findOne(['source_id' => $id_source, 'user_id' => $id_user]);
    }
    
    public static function findByIdUser($id_user){
        return self::findOne(['user_id' => $id_user]);
    }
}