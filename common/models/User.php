<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace common\models;

use common\models\Source;
use common\models\UserEntity;
use common\models\User__source_access;

/**
 * Данный класс явдяется расширением стандартного UserIdentity (ActiveRecord)
 * Вынесен отдельно чтобы не смешивать код, относящийся к checker и для обращения $yii::$app->user->identity
 *
 * @author kosten
 */
class User extends UserEntity {

    public function isAdmin() {
        return !empty(\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id));
    }

    public static function isAdminStatic($id = 0): bool {
        if (!$id) {
            return false;
        }
        return !empty(\Yii::$app->authManager->getAssignment('admin', $id));
    }

    public function is_detail_view_for_items() {
        return $this->detail_view_for_items ? true : false;
    }

    /*
      public static function is_detail_view_for_items() {

      $user_id = \Yii::$app->getUser()->id;
      $res = self::findOne(['id' => $user_id]);
      if ($res) {
      return $res->detail_view_for_items ? true : false;
      }
      return false;
      }
     */

    /**
     * Gets query for [[User__source_access]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser__source_access() {
        return $this->hasMany(User__source_access::class, ['user_id' => 'id']);
    }

}
