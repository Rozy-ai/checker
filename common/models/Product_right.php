<?php

namespace common\models;

use backend\models\Settings__source_fields;
use common\models\Source;

class Product_right {

    private $compare_status;
    private Source $source;
    
    public function __construct(Source $source, array $attributes = []) {
        $this->setSource($source);
        foreach ($attributes as $key => $val){
            $this->$key = $val;
        }
    }
    
    private function setSource($source){
        $this->source = $source;
        if (!isset($this->source->fields)) {
            $this->source->fields = Settings__source_fields::getSchemeFields($this->source->id);
        }
    }
    
    public function __get($name) {
        return $this->$name;
    }
    
    /**
     * $key - из таблицы name_source
     */
    public function __set($name, $value) {
        if (!isset($this->source->fields))
            throw new \InvalidArgumentException('Не указана cхема полей источника');
        
        $field_common = $this->source->fields[$name] ?? $name;
        $this->$field_common = $value;
    }

    public function getStatus() {
        return $this->compare_status;
    }

    public function setStatus($status) {
        $this->compare_status = $status;
        return $this;
    }
}
