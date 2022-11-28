<?php

namespace backend\models;

use yii\db\ActiveRecord;
use common\models\Source;
use backend\models\Settings__common_fields;

//  public $id;
//  public $title;
//  public $item_1_key;
//  public $item_2_key;
//  public $visible_for_user;

class Settings__source_fields extends ActiveRecord {

    public function rules() {
        return [
            [['id', 'settings__common_fields_id', 'source_id', 'type', 'name', 'field_action'], 'trim'],
            [['settings__common_fields_id', 'source_id', 'type', 'name', 'field_action'], 'required'],
        ];
    }

    public static function name_for_source($name, int $source_id, $type = false) {
        $q = self::find()
                ->select([
                    'settings__source_fields.id as id',
                    'settings__common_fields.name as c_name',
                    'settings__common_fields.description as c_description',
                    'settings__source_fields.name as s_name'
                ])
                ->innerJoin('settings__common_fields', '`settings__common_fields`.`id` = `settings__source_fields`.`settings__common_fields_id`')
                ->where(['source_id' => $source_id]);
        if ($type) {
            $q->andWhere(['type' => $type]);
        }
        $q->andWhere(['settings__common_fields.name' => $name])
        //->where('source')
        ;

        $res = $q->asArray()->one();

        if ($res) {
            return $res['s_name'];
        }

        return false;
    }

    public static function getSchemeFields($id_source) {
        $name_source = Settings__source_fields::tableName();
        $name_common = Settings__common_fields::tableName();
        $fields = Settings__source_fields::find()
            ->select([
                $name_source . '.name as name_source',
                $name_common . '.name as name_common'
            ])
            ->where([$name_source . '.source_id' => $id_source])
            ->innerJoin($name_common, $name_common . '.id = ' . $name_source . '.settings__common_fields_id')
            ->asArray()
            ->all();

        $data = [];
        foreach ($fields as $row) {
            $data[$row['name_source']] = $row['name_common'];
        }
        return $data;
    }

    public static function data_for_source($name, int $source_id, $type = false) {
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
                ->innerJoin('settings__common_fields', '`settings__common_fields`.`id` = `settings__source_fields`.`settings__common_fields_id`')
                ->where(['source_id' => $source_id]);
        if ($type) {
            $q->andWhere(['type' => $type]);
        }
        $q->andWhere(['settings__common_fields.name' => $name])
        //->where('source')
        ;

        $res = $q->asArray()->one();

        if ($res) {
            return $res;
        }

        return false;
    }

}
