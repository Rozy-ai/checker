<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace common\models;

use common\models\User__source_access;
use common\models\Source;
use backend\models\Settings__source_fields;

/**
 * Класс для работы с таблицей Source
 * @property int $id
 * @property string $name
 * @property string $table_1
 * @property string $table_2
 * @property string $import_local__max_product_name
 * @property string $import_local__db_import_name
 * @property string $import__default_q_1
 * @property string $import__sql_file_path
 */
class Source extends \yii\db\ActiveRecord {

    const ids_source_free = [1, 2];
    
    private $dataFields;

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

    /**
     * Получить модель источника по заданному id
     * 
     * @param int $id
     * @return Source|null
     */
    public static function getById(int $id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * Возвратить обьект источник для пользователя
     *  - Если источник указан и он бесплатный то получам его
     *  - Если источник не указан и он бесплатный то берем петвый доступный
     *  - Чтобы получить платный источник, нужно иметь id_user такой чтобы был в базе
     *  - Если для зарегистрированного пользователя указан не верный источник, то ищем первый доступный
     *  - Если платного доступного нет то берем первый бесплатный
     * 
     * @param int $id_user id пользователя
     * @param int $id_source - предполагаемый источник
     */
    public static function getForUser($id_user = 0, $id_source = 0) {
        if ($id_source && self::isSourceFree($id_source)) {
            return self::getById($id_source);
        }

        $source = null;
        if ($id_user) {
            // Проверяем на доступность источника
            if ($id_source) {
                if (User__source_access::isExists($id_source, $id_user)) {
                    return self::getById($id_source);
                }
            }

            // Если пользователь зарегистрирован, но просто нет доступа к этому источнику, 
            // то даем ему шанс и пробуем найти для него другой доступный платный источник
            $sources = User__source_access::findByIdUser($id_user);
            if (isset($sources[0])) {
                return self::getById($sources[0]['source_id']);
            }
        }

        return self::getById(self::ids_source_free[0]);
    }

    /**
     * Явдяется ли источник с данным id бесплатным
     * 
     * @param type $id_source - id источника
     * @return bool
     */
    public static function isSourceFree($id_source): bool {
        return in_array($id_source, self::ids_source_free);
    }

    /**
     * Список бесплатных источников
     * 
     * @return Source[]
     */
    public static function getSourcesFree() {
        $res = [];
        foreach (self::ids_source_free as $id) {
            $res[] = Source::getById($id);
        }
        return $res;
    }

    /**
     * Получить спсок доступных платных источников
     * @param int id_user
     * @return Sources[]
     */
    public static function getSourcesPaidByIdUser($id_user = 0) {
        if (!$id_user) {
            $sources = self::find()->all();
        } else {
            $sources = Source::find()
                    ->leftJoin('user__source_access', 'user__source_access.source_id = source.id')
                    ->where(['user__source_access.user_id' => $id_user])
                    ->asArray()
                    ->all();
        }
        return $sources;
    }

    /**
     * Выбрать для пользователя доступные источники:
     * 
     * Только нюанс. Поступные платные не должны по id совпадать с бесплатными
     * @param int $id_source
     * @param type $id_user
     */
    public static function findAllSources(int $id_source, $id_user = 0) {
        $id_sources_free = self::ids_source_free;
        $id_sources_paid = ($id_user) ? User__source_access::findIdSources($id_user) : [];
        $id_sources = array_unique(array_merge($id_sources_free, $id_sources_paid));
        $sources = [];
        foreach ($id_sources as $id_source) {
            $sources[] = Source::getById($id_source);
        }
        return $sources;
    }

    /**
     * Эта х**ня оставлена для совместимости, ибо весь проект использует эту функцию
     * и я в душе не представляю, есть ли данные в  \Yii::$app->request->get
     * @param type $source_id
     * @return type
     */
    public static function get_source($source_id = false) {
        if (!$source_id) {
            $source_id = \Yii::$app->request->get('filter-items__source', false);
            if (!$source_id)
                $source_id = \Yii::$app->request->get('source_id', false);
        }

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
    
    function getDataFields($name){
        if (!$this->dataFields){
            $this->dataFields = Settings__source_fields::data_for_source_all($this->id);
        }
        return $this->dataFields[$name];
    }
}
