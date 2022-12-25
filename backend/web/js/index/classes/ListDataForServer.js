'use strict';

import {
    Ajax
} from './Ajax.js';

export const EVENT_CHANGE_DATA_LEFT = 'eventChangeDataLeft';
export const EVENT_CHANGE_DATA_RIGHT = 'evenChangeDataRight';
export const EVENT_CHANGE_DATA_DELETE_LEFT = 'evenChangeDataDeleteLeft';
export const EVENT_CHANGE_DATA_DELETE_RIGHT = 'evenChangeDataDeleteRight';

export const ACTION_DATA_CREATE = 'create';
export const ACTION_DATA_CHANGE = 'change';
export const ACTION_DATA_DELETE = 'delete';

const MISMATCH = 'mismatch';
    
export class ListDataForServer{
    datas_products_left  = [];
    datas_products_right = [];
    datas_products_left_delete = [];
    datas_products_right_delete = [];
    
    /**************************************************************************
     *** isExistsData
     **************************************************************************/
    
    isExistsDataLeftBy(id_source, id_product){
        return this.datas_products_left.some((data) => data.id_source === id_source && data.id_product === id_product);
    }
    
    isExistsDataLeftDeleteBy(id_source, id_product){
        return this.datas_products_left_delete.some((data) => data.id_source === id_source && data.id_product === id_product);
    }
    
    isExistsDataRightBy(id_source, id_item){
        return this.datas_products_right.some((data) => data.id_source === id_source && data.id_item === id_item);
    }
    
    isExistsDataRightDeleteBy(id_source, id_item){
        return this.datas_products_right_delete.some((data) => data.id_source === id_source && data.id_item === id_item);
    } 
    
    /**************************************************************************
     *** deleteIfExists
     **************************************************************************/
    
    /**
     * Удалить товар из массива левых товаров на mismatch
     * Когда левый товар удаляетсся из списка на mismatch то все правые товары тоже должны удалиться из списка. Всегда!
     * 
     * @param {type} id_source
     * @param {type} id_product
     * @param {type} forced_products_right - правые товары проверять в любом случае
     * @returns {undefined}
     */
    deleteIfExistsLeftBy(id_source, id_product, forced_products_right = false){
        // Ищем и удаляем запись в массиве
        let index = this.datas_products_left.findIndex((data)=>data.id_source===id_source&&data.id_product===id_product);

        if ( index >= 0 ){
            document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_LEFT, { detail: {
                data: this.datas_products_left[index],
                action: ACTION_DATA_DELETE
            }}));
            this.datas_products_left.splice(index, 1);
        } else if (!forced_products_right) {
            return;
        }

        // Заодно снимаем все выборы из списка правых товаров тоторые будут все mismatch
        this.datas_products_right = this.datas_products_right.filter((data)=>{
            if (data.id_source !== id_source || data.id_product !== id_product){
                return true;
            } else {
                document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_RIGHT, { detail: {
                    data: data,
                    action: ACTION_DATA_DELETE
                }}));
            }
        });
    }
    
    /**
     * Удалить товар из массива левых товаров на удаление
     * 
     * @param {type} id_source
     * @param {type} id_product
     * @param {type} forced_products_right - принудительная проверка правых товаров
     * 
     * @returns {undefined}
     */
    deleteIfExistsLeftDeleteBy(id_source, id_product, forced_products_right = false){
        // Ищем и удаляем запись в массиве
        let index = this.datas_products_left_delete.findIndex((data)=>data.id_source === id_source && data.id_product === id_product);

        if (index >=0 ){
            document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_DELETE_LEFT, { detail: {
                data: this.datas_products_left_delete[index],
                action: ACTION_DATA_DELETE
            }}));
            this.datas_products_left_delete.splice(index, 1);
        } else if (!forced_products_right) {
            return;
        } 

        // Заодно убираем все правые товары на удаление
        this.datas_products_right_delete = this.datas_products_right_delete.filter((data)=>{
            if (data.id_source !== id_source || data.id_product !== id_product){
                return true;
            } else {
                document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_DELETE_RIGHT, { detail: {
                    data: data,
                    action: ACTION_DATA_DELETE
                }}));
            }
        });        
    }
    
    /**
     * Удалить товар из массива правых товаров на изменение статуса
     * ( Убрать статус правого товара )
     * 
     * @param {type} id_source
     * @param {type} id_item
     * @returns {undefined}
     */
    deleteIfExistsRightBy(id_source, id_item){
        let index = this.datas_products_right.findIndex((data)=>data.id_source === id_source && data.id_item === id_item);
        if ( index < 0 ) return;

        document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_RIGHT, { detail: {
            data: this.datas_products_right[index],
            action: ACTION_DATA_DELETE
        }}));            
        
        this.datas_products_right.splice(index, 1);
    }
    
    /**
     * Удалить товар из массива правых на удаление
     * ( Убрать удаление правого товара )
     * 
     * @param {type} id_source
     * @param {type} id_item
     * @returns {undefined}
     */
    deleteIfExistsRightDeleteBy(id_source, id_item){
        let index = this.datas_products_right_delete.findIndex((data)=>data.id_source === id_source && data.id_item === id_item);
        if ( index < 0 ) return; 
        
        document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_DELETE_RIGHT, { detail: {
            data: this.datas_products_right_delete[index],
            action: ACTION_DATA_DELETE
        }}));
    
        this.datas_products_right_delete.splice(index, 1);
    }
    
    /**************************************************************************
     *** addData
     **************************************************************************/
        
    /**
     * Добавить левый товар в массив сравнений
     * 
     * @param {object} data_product
     * param {object} list_data_item
     * @returns {Boolean} Является ли товар новым
     */
    addDataLeft(data_product){ //list_data_item = []){
        // Есть ли этот товар в массиве левых товаров. Если есть - уходим. Если нет - добавляем
        if (this.isExistsDataLeftBy(data_product.id_source, data_product.id_product )) return false;

        // Есть ли этот товар в массиве левых товаров на удаление. Если есть - убираем
        this.deleteIfExistsLeftDeleteBy(data_product.id_source, data_product.id_product);

        // Добавляем запись в массив левых товаров на mismatch
        this.datas_products_left.push(Object.assign({}, data_product));
        document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_LEFT, { detail: {
            data: data_product,
            action: ACTION_DATA_CREATE
        }}));
    
        // Добавляем в массив все правые товары на mismatch
        //list_data_item.each((data_item)=>this.addDataRight(data_item));
    }
    
    /**
     * Добавить левый товар в массив на удаление
     * 
     * @param {type} data_product
     * @returns {undefined}
     */
    addDataDeleteLeft(data_product){
        if (this.ifExistsDataDeleteLeftBy(data_product.id_source, data_product.id_product)) return;
        
        this.deleteIfExistsLeftBy(data_product.id_source, data_product.id_product);
        
        // Добавляем запись в массив левых товаров на удаление
        this.datas_products_left_delete.push( Object.assign({}, data_product ) );
        document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_DELETE_LEFT, { detail: {
            data: data_product,
            action: ACTION_DATA_CREATE
        }}));        
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
            return;
        } else {
            // Если товара в массиве нет то запоминаем data в массив правых товаров
            this.datas_products_right.push( Object.assign({},data_item) );
            
            // Генерируем событие добавления
            document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_RIGHT, { detail: {
                data: data_item,
                action: ACTION_DATA_CREATE
            }}));
        }

        // Есть ли этот товар в массиве правых товаров на удаление. Если есть - убираем
        this.deleteIfExistsRightDeleteBy(data_item.id_source, data_item.id_item);
        
        // Если соответственный левый товар есть в массиве левых на missmatch то удаляем его оттуда
        if (data_item.status !== MISMATCH){
            this.deleteIfExistsLeftBy(data_item.id_source, data_item.id_product); 
        }
        
        // Если соответственный левый товар есть в массиве левых на удаление то удаляем его оттуда
        this.deleteIfExistsLeftDeleteBy(data_item.id_source, data_item.id_product);
    }
    
    /**
     *  Добавить правый товар в массив сравнений
     *  
     * @param {type} data_item
     * @returns {undefined}
     */
    addDataDeleteRight(data_item){
        // Проверка на дубли
        if (this.isExistsDataDeleteRigth(data_item.id_source, data_item.id_item)) return;
        
        // Убираем из массива сравнения слева
        this.deleteIfExistsLeftBy(data_item.id_source, data_item.id_product);
        
        //Если этот товар в списке на статус - удаляем его оттуда
        this.deleteIfExistsRightBy(data_item.id_source, data_item.id_item);
        
        // Добавляем в список удаления правого товара
        this.datas_products_right_delete.push( Object.assign({},data_item) );
        document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_DELETE_RIGHT, { detail: {
            data: data_item,
            action: ACTION_DATA_CREATE
        }}));
    }
    
    /**
     * Сбрасывает все массивы без генерации событий
     * @returns {undefined}
     */
    reset(){
        this.datas_products_left.length = 0;
        this.datas_products_right.length = 0;
        this.datas_products_left_delete.length = 0;
    }
    
    /**
     * Содержатся ли данные в массивах
     * 
     * @returns {undefined}
     */
    isHasData(){
        return (
            this.datas_products_left.length ||
            this.datas_products_right.length ||
            this.datas_products_left_delete.length);
    }
};