<?php

namespace backend\models;

use yii\db\ActiveRecord;
use common\models\Stats_import_export;
class Stats__import_export extends ActiveRecord {

    public function rules() {
        return [
            [
                ['id', 'type', 'file_name', 'comparison', 'cnt', 'raw', 'source_id', 'profile', 'created'], 'safe'
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(Stats_import_export::className(), ['id' => 'source_id']);
    }
} 
