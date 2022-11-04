<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace common\models;

use backend\models\Source;
use common\models\UserEntity;
use common\models\User__source_access;

/**
 * Данный класс явдяется расширением стандартного UserIdentity (ActiveRecord)
 * Вынесен отдельно чтобы не смешивать код, относящийся к checker и для обращения $yii::$app->user->identity
 *
 * @author kosten
 */
class User extends UserEntity {

    public function isAdmin($id = 0):bool {
        if (!$id && !$id=$this->id) {
            return false;
        }
        return !empty(\Yii::$app->authManager->getAssignment('admin', $id));
    }
}
