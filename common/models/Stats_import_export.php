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


}
