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

    /**
     * Get last Import
     * @param type $source_id
     * @param type $file_name
     * @return type
     */
    public static function getLastImport($source_id = 1, $file_name = null){
        $query = self::find();
        $query->where(['type' => 'IMPORT', 'source_id' => $source_id]);
        
        if ( $file_name <> 'LOCAL_IMPORT') {    
            $query->andWhere(['AND',['<>','file_name', 'LOCAL_IMPORT']]);    
        } else {
            $query->andWhere(['AND',['file_name'=>'LOCAL_IMPORT']]);  
        }       
        
        $result = $query->orderBy(['created' => SORT_DESC])
                        ->limit(1)
                        ->one();       
        return $result;
    }
    
    public static function getLastOtherImport($source_id=1){
        return self::getLastImport($source_id);
    }
    
        
    public static function getLastLocalImport($source_id=1){
        return self::getLastImport($source_id,'LOCAL_IMPORT');
    }
}
