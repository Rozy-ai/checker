<?php

namespace common\models;


use yii\db\ActiveRecord;

class User__source_access extends ActiveRecord{
    public static function getById($id_source, $id_user){
        return self::findOne(['source_id' => $id_source, 'user_id' => $id_user]);
    }
    
    public static function isExists($id_source, $id_user){
        return self::find(['source_id' => $id_source, 'user_id' => $id_user])->exists();
    }
    
    public static function findIdSources($id_user){
        $usa = self::find(['user_id' => $id_user])->indexBy('source_id')->all();
        return array_keys($usa);
    }
    
    public static function findByIdUser($id_user){
        return self::findAll(['user_id' => $id_user]);
    }
}