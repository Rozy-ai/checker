<?php

namespace backend\models;

use yii\web\Session;

/**
 * @property int $id
 * @property string $table_1
 * @property string $table_2
 * @property string $import_local__max_product_date
 * @property string $import_local__db_import_name
 * @property string $import__default_q_1
 * @property string $import__sql_file_path
 * 
 * @property string $class_1
 * @property string $class_2
 * @property string $sources
 */
class Source extends \yii\db\ActiveRecord {

    public static function tableName() {
        return 'source';
    }

    public function rules() {
        return [
            [['id', 'name', 'table_1', 'table_2', 'import_local__max_product_date', 'import_local__db_import_name', 'import__default_q_1', 'import__sql_file_path'], 'trim'],
            [['name', 'table_1'], 'required'],
        ];
    }
    
    public function getClass_1() {
        return 'common\models\\' . ucfirst($this->table_1);
    }    

    public function getClass_2() {
        return 'common\models\\' . ucfirst($this->table_2);
    }
    
    public static function getSources(){
        $user_id = \Yii::$app->getUser()->id;
        $u = User::find()->where(['id' => $user_id])->limit(1)->one();
        $res = $u->user__source_access;

        if (!$res) {
            return self::find()->all();
        } else {
            return self::find()
                ->leftJoin('user__source_access', 'user__source_access.source_id = source.id')
                ->where(['user__source_access.user_id' => $user_id])
                ->all();
        }
    }
    
    public static function getSource(int $source_id = 0) {
        if (!$source_id){
            return null;
        }
        
        return self::findOne(['id' => $source_id]);
    }

    //Дальше шлак на удаление
    public function get_class_2() {
        return 'common\models\\' . ucfirst($this->table_2);
    }

    public function get_class_1() {
        return 'common\models\\' . ucfirst($this->table_1);
    }

    public static function get_sources_for_form() {
        $out = [];
        $s = self::get_sources();
        if ($s) {
            foreach ($s as $item) {
                $out[$item->id] = $item->name;
            }
        }


        return $out;
    }

    public static function get_sources() {
        $user_id = \Yii::$app->getUser()->id;

        $u = User::find()->where(['id' => $user_id])->limit(1)->one();
        $res = $u->user__source_access;

        if (!$res)
            return Source::find()->all();
        else
            return Source::find()
                            ->leftJoin('user__source_access', 'user__source_access.source_id = source.id')
                            ->where(['user__source_access.user_id' => $user_id])
                            ->all();
    }

    public static function get_source($source_id = false) {
        if (!$source_id) {
            $source_id = \Yii::$app->request->get('filter-items__source', false);
            if (!$source_id)
                $source_id = \Yii::$app->request->get('source_id', false);
        }
//    //echo '<pre>'.PHP_EOL;
//    print_r((new Session())->get('source'));
//    //echo PHP_EOL;
//    exit;
        if ($source_id === false) {
            /*
              if ($s_source = (new Session())->get('source')){
              $source_id = $s_source['id'];
              }else{
              $source_id = 1;
              }
             */
            $source_id = 1;
        }

        $source = Source::findOne(['id' => (int) $source_id]);

        $out['source_id'] = $source_id;
        $out['source_name'] = $source->name;
        $out['source_table_name'] = $source->table_1;
        $out['source_table_name_2'] = $source->table_2;
        $out['source_class'] = 'common\models\\' . ucfirst($source->table_1);

        $out['import_local__max_product_date'] = $source->import_local__max_product_date;
        $out['import_local__db_import_name'] = $source->import_local__db_import_name;
        $out['import__default_q_1'] = $source->import__default_q_1;
        $out['import__sql_file_path'] = $source->import__sql_file_path;

        return $out;
    }

}
