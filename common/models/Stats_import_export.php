<?php

namespace common\models;

use Yii;

class Stats_import_export extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%stats__import_export}}';
    }

    public static function getLastLocalImport($source_id = 1){
        return self::find()
            ->where(['type' => 'IMPORT', 'source_id' => $source_id])
            ->orderBy(['created' => SORT_DESC])
            ->limit(1)
            ->one();       
    }
}
