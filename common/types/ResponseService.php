<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Тип данных ответа от сервиса
 *
 * @author kosten
 */

class ResponseService {
    private $status;
    private $text;
    private $data;
    
    public function __construct(StatusResponse $status, string $text = '', array $data = null) {
        $this->status = status;
        $this->text = $text;
        $this->data = $data;
    }
}
