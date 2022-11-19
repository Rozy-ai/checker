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

    public static function getLastLocalImport(){
        return self::find()
            ->where(['type' => 'IMPORT'])
            ->orderBy(['created' => SORT_DESC])
            ->limit(1)
            ->one();       
    }
}
