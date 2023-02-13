'use strict';

import {
    Ajax
} from './Ajax.js';

export const EVENT_CHANGE_DATA_RIGHT = 'evenChangeDataRight';

export const ACTION_DATA_CREATE = 'create';
export const ACTION_DATA_CHANGE = 'change';
export const ACTION_DATA_DELETE = 'delete';
    
export class ListDataForServer{
    datas_products_right = [];
    
    findBy(id_product, status){
        return this.datas_products_right.filter((data)=>data.id_product===id_product&&data.status===status);
    }
    
    findAllBy(status){
        return this.datas_products_right.filter((data)=>data.status===status);
    }
    
    isExistsDataRightBy(id_source, id_item){
        return this.datas_products_right.some((data) => data.id_source === id_source && data.id_item === id_item);
    }
    
    /**
     * Удалить выборы правых товаров соответствующие параметрам
     * 
     * @param {type} id_source
     * @param {type} id_product
     * @param {type} id_item
     * @returns {undefined}
     */
    deleteIfExistsRightBy(id_source, id_product = null, id_item = null){
        this.datas_products_right = this.datas_products_right.filter(function(data){
            if ( data.id_source !== id_source ||
                 (id_product && data.id_product !== id_product) ||
                 (id_item && data.id_item !== id_item)) return true;

            document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_RIGHT, { detail: {
                data: data,
                action: ACTION_DATA_DELETE
            }}));
        });
    }
    
    /**
     * Удалить все выборы товаров
     * 
     * @returns {undefined}
     */
    deleteRightAll(){
        this.datas_products_right.forEach((data) => 
            document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_RIGHT, { detail: {
                data: data,
                action: ACTION_DATA_DELETE
            }})));
        this.reset();
    }
    
    /**
     * Добавить правый товар в массив сравнений
     * 
     * @param {type} data_item
     * @returns {undefined}
     */
    addDataRight(data_item){
        // Есть ли этот товар в массиве правых товаров. Если есть - меняем
        let index = this.datas_products_right.findIndex((data)=>data.id_source === data_item.id_source && data.id_item === data_item.id_item);           
        if ( index >=0 ){
            // Перезаписываем статус
            if (this.datas_products_right[index].status !== data_item.status){
                let status_last = this.datas_products_right[index].status;
                
                this.datas_products_right[index] = Object.assign({},data_item); // Перезаписываем
                // Генерируем событие изменения
                document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_RIGHT, { detail: {
                    status_last: status_last,
                    data: data_item,
                    action: ACTION_DATA_CHANGE
                }}));
            }
        } else {
            // Если товара в массиве нет то запоминаем data в массив правых товаров
            this.datas_products_right.push( Object.assign({},data_item) );

            let action = ACTION_DATA_CREATE;
            let status_last = null;

            // Если изменяются списки со статусом, то вызываем событие изменения
            if ($('#id_f_comparison_status').val() === "MISMATCH" || $('#id_f_comparison_status').val() === "PRE_MATCH") {
                status_last = $('#id_f_comparison_status').val();
                action = ACTION_DATA_CHANGE;
            }

            // Генерируем событие добавления/изменения
            document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_RIGHT, { detail: {
                status_last: status_last,
                data: data_item,
                action: action
            }}));
        }
    }
    
    /**
     * 
     * @param {type} datas_items Массив data атрибутов
     * @returns {undefined}
     */
    addDatasRightList(datas_items){
        datas_items.forEach((data) => this.addDataRight(data));
    }
    
    /**
     * Сбрасывает все массивы без генерации событий
     * @returns {undefined}
     */
    reset(){
        this.datas_products_right.length = 0;
    }
    
    /**
     * Содержатся ли данные в массивах
     * 
     * @returns {undefined}
     */
    isHasData(){
        return (this.datas_products_right.length);
    }
};