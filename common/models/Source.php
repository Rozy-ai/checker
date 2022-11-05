<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace common\models;

/**
 * Класс для работы с таблицей Source
 *
 * @author kosten
 */
class Source extends \yii\db\ActiveRecord {
    const ids_source_free = [1, 2, 3];
    
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
    public static function getById(int $id){
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
    public static function getForUser($id_user = 0, $id_source = 0){
        if ($id_source && self::isSourceFree($id_source)){
            return self::getById ($id_source);
        }
        
        $source = null;
        if ($id_user){
            // Проверяем на доступность источника
            if ($id_source) {
                $source = User__source_access::getBy($id_source, $id_user);
                if ($source) return $source;
            }
            
            // Если пользователь зарегистрирован, но просто нет доступа к этому источнику, 
            // то даем ему шанс и пробуем найти для него другой доступный платный источник
            $sources = User__source_access::findByIdUser($id_user);
            if (isset($sources[0])) return $sources[0];
        }
        
        return self::getById(self::ids_source_free[0]);
    }
    
    /**
     * Явдяется ли источник с данным id бесплатным
     * 
     * @param type $id_source - id источника
     * @return bool
     */
    public static function isSourceFree($id_source): bool{
        return in_array($id_source, self::ids_source_free);
    }
    
    
    public static function getSourcesFree(){
        $res = [];
        foreach(self::ids_source_free as $id){
            $res[] = Source::getById($id);
        }
        return $res;
    }
    
    public static function findAllSources(int $id_source, $id_user = 0){
        $sources = self::getSourcesFree();
        if ($id_user && !self::isSourceFree($id_source)){
            $sources = array_merge($sources, User__source_access::findByIdUser($id_user));
        }
        return $sources;
    }
}
