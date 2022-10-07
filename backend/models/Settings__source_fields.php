<?php

namespace backend\models;


use yii\db\ActiveRecord;
use yii\web\Session;

//  public $id;
//  public $title;
//  public $item_1_key;
//  public $item_2_key;
//  public $visible_for_user;

class Settings__source_fields extends ActiveRecord{
  public function rules(){
    return [
      [['id','settings__common_fields_id','source_id','type','name','field_action'],'trim'],
      [['settings__common_fields_id','source_id','type','name','field_action'],'required'],
    ];
  }

  public static function name_for_source($name,$source_id = false,$type = false){
    if (!$source_id) {
      if ( isset( \Yii::$app->view->params['source_id'] ) ){
        $source_id = \Yii::$app->view->params['source_id'];
      };

      if (!$source_id) {
        $s = Source::get_source();
        $source_id = $s['source_id'];
      }
    }

    $q = self::find()

      ->select([
        'settings__source_fields.id as id',
        'settings__common_fields.name as c_name',
        'settings__common_fields.description as c_description',
        'settings__source_fields.name as s_name'
                ])

      ->innerJoin('settings__common_fields','`settings__common_fields`.`id` = `settings__source_fields`.`settings__common_fields_id`')
      ->where(['source_id' => $source_id]);
      if ($type){
        $q->andWhere(['type' => $type]);
      }
      $q->andWhere(['settings__common_fields.name' => $name])
      //->where('source')
    ;

    $res = $q->asArray()->one();

    if ($res){
      return $res['s_name'];
    }

    return false;
  }

  public static function data_for_source($name,$source_id = false,$type = false){
    if (!$source_id) {
      if ( isset( \Yii::$app->view->params['source_id'] ) ){
        $source_id = \Yii::$app->view->params['source_id'];
      };


      if (!$source_id) {
        $s = Source::get_source();
        $source_id = $s['source_id'];
      }
    }



    $q = self::find()

      ->select([
        'settings__source_fields.id as settings__source_fields_id',
        'settings__source_fields.settings__common_fields_id as settings__source_fields_settings__common_fields_id',
        'settings__source_fields.source_id as settings__source_fields_source_id',
        'settings__source_fields.type as settings__source_fields_type',
        'settings__source_fields.name as settings__source_fields_name',
        'settings__source_fields.field_action as settings__source_fields_field_action',

        'settings__common_fields.id as settings__common_fields_id',
        'settings__common_fields.name as settings__common_fields_name',
        'settings__common_fields.description as settings__common_fields_description',
      ])

      ->innerJoin('settings__common_fields','`settings__common_fields`.`id` = `settings__source_fields`.`settings__common_fields_id`')
      ->where(['source_id' => $source_id]);
    if ($type){
      $q->andWhere(['type' => $type]);
    }
    $q->andWhere(['settings__common_fields.name' => $name])
      //->where('source')
    ;

    $res = $q->asArray()->one();


    if ($res){
      return $res;
    }

    return false;
  }





  public function getSource(){
    return $this->hasOne(Source::class, ['id' => 'source_id']);
  }


}