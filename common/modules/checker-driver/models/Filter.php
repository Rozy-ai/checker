<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

use yii\db\ActiveQuery;
use Behat\Gherkin\Filter\FilterInterface;
/**
 * Description of Filter
 *
 * @author kosten
 */
class Filter{
    private string   $name;
    private string   $description;
    private array    $rolesUser;
    
    private ActiveQuery $value;
    
    public function __construct($name, $value, $description, $rolesUser) {
        
    }
    
    public function toQuery(){
        return $this->value;
    }
}
