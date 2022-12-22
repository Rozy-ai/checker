'use strict';

import {
    Ajax
} from './Ajax.js';

export const EVENT_CHANGE_DATA_LEFT = 'eventChangeDataLeft';
export const EVENT_CHANGE_DATA_RIGHT = 'evenChangeDataRight';
export const EVENT_CHANGE_DATA_DELETE = 'evenChangeDataDelete';

export const ACTION_DATA_CREATE = 'create';
export const ACTION_DATA_CHANGE = 'change';
export const ACTION_DATA_DELETE = 'delete';
    
export class ListDataForServer{
    datas_products_left  = [];
    datas_products_right = [];
    datas_products_left_delete = [];
    
    isExistsDataRightBy(id_source, id_item){
        return this.datas_products_right.some((data) => data.id_source === id_source && data.id_item === id_item);
    }
    
    isExistsDataDeleteBy(id_source, id_product){
        return this.datas_products_left_delete.some((data) => data.id_source === id_source && data.id_product === id_product);
    }
    
    /**
     * Запомнить выбор от правых товаров (Элементов)
     * 
     * @param {object} data_item
     * @returns {boolean} Является ли запись товара новой
     */
    addRight(data_item){
        // Смотрим, есть ли в этом блоке левый товар в массиве удаленных.
        // Если есть, то пользователь передумал удалять левый товар вместе со всем весь блоком
        for (let i = this.datas_products_left_delete.length - 1; i >= 0; --i) {
            let data = this.datas_products_left_delete[i];
            if (data.id_product === data_item.id_product){
                document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_DELETE, { detail: {
                    data: this.datas_products_left_delete[i],
                    action: ACTION_DATA_DELETE
                }}));
                this.datas_products_left_delete.splice(i, 1);
                break; // Дальше смотреть нет смысла, ибо значение уникальны
            }
        }
        
        // Если хоть один правый товар приобрел статус отличный от mismatch то 
        // не лоджно быть в масиве левых товаров соответствующего продукта
        if (data_item.status !== 'MISMATCH'){
            for (let i = this.datas_products_left.length - 1; i >= 0; --i) {         
                let data = this.datas_products_left[i];
                if (data.id_product === data_item.id_product){
                    document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_LEFT, { detail: {
                        data: data,
                        action: ACTION_DATA_DELETE
                    }}));
                    this.datas_products_left.splice(i, 1);
                }
            }
        }     
        
        // Сморим, есть ли уже этот элемент в массиве правых товаров, ожидающем отправку
        for (let i = this.datas_products_right.length - 1; i >= 0; --i) {
            let data = this.datas_products_right[i];
            if (data.id_item === data_item.id_item && data.id_source === data_item.id_source) {
                let statuslast = this.datas_products_right[i].status;
                this.datas_products_right[i] = data_item; //Перезаписать
                document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_RIGHT, { detail: {
                    data: this.datas_products_right[i],
                    action: ACTION_DATA_CHANGE,
                    statuslast: statuslast
                }}));
                return false;
            }
        }

        // Если эмемента в массиве нет то запоминаем в массив элемент
        this.datas_products_right.push(data_item);
        document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_RIGHT, { detail: {
            data: data_item,
            action: ACTION_DATA_CREATE
        }}));
        return true;
    }
    
    /**
     * Запомнить выбор левых
     * 
     * @param {object} data_product
     * @returns {boolean} Является ли товар новым
     */
    addLeft(data_product){
        // Сморим, есть ли уже этот товар в массиве левых товаров, ожидающем отправку
        for ( let data of this.datas_products_left){
            if (data.id_product === data_product.id_product && data.id_source === data_product.id_source) {         
                return false;
            }
        }
        
        // Смотрим, есть ли этот товар в массиве удаленных. Если есть, то пользователь передумал его удалять
        for (let i = this.datas_products_left.length - 1; i >= 0; --i) {
            let data = this.datas_products_left[i];
            if (data.id_product === data_product.id_product){
                document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_LEFT, { detail: {
                    data: data,
                    action: ACTION_DATA_DELETE
                }}));
                this.datas_products_left_delete.splice(i, 1);
                break; // Дальше смотреть нет смысла, ибо значение уникальны
            }
        }

        // Если товара в массиве нет то запоминаем в массив левый товар
        this.datas_products_left.push(data_product);
        document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_LEFT, { detail: {
            data: data_product,
            action: ACTION_DATA_CREATE
        }}));
        return true;
    }
    
    /**
     * Запомнить блок товаров на удаление
     * 
     * @param {type} data_product data-данные левых товаров на удаление
     * @returns {Boolean} Если запись в массив была новая, то true
     */
    addDelete(data_product){
        // Сморим, есть ли уже этот товар в массиве левых товаров на удадение, ожидающем отправку
        for (let i = this.datas_products_left_delete.length - 1; i >= 0; --i) {
            let data = this.datas_products_left_delete[i];
            if (data.id_product === data_product.id_product && data.id_source === data_product.id_source) {
                return false;
            }
        }
        
        // Смотрим, есть ли уже этот товар в массиве, ожидающем отправку на missmatch.
        // Если есть, то пользователь передумал его отмечать и хочет удалить
        for (let i = this.datas_products_left.length - 1; i >= 0; --i) {
            let data = this.datas_products_left[i];
            if (data.id_product === data_product.id_product && data.id_source === data_product.id_source) {
                //Убираем из массива datas_products_right
                for (let j = this.datas_products_right.length - 1; j >= 0; --j) {
                    let data_right = this.datas_products_right[j];
                    if (data_right.id_source === data.id_source && data_right.id_product === data.id_product){
                        this.deleteRightBy(data_right.id_source, data_right.id_item);
                    }
                }
                //Убираем из массива datas_products_left
                document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_LEFT, { detail: {
                    data: data,
                    action: ACTION_DATA_DELETE
                }}));
                this.datas_products_left.splice(i, 1);
                break; // Дальше смотреть нет смысла, ибо значение уникальны
            }
        }
        
        // Если товара в массиве на удаление нет то запоминаем его
        this.datas_products_left_delete.push(data_product);
        document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_DELETE, { detail: {
            data: data_product,
            action: ACTION_DATA_CREATE
        }}));
        return true;
    }
    
    /**
     * Отменить выбор правого товара по data атрбутам
     * 
     * @param {type} id_source
     * @param {type} id_item
     * @returns {undefined}
     */
    deleteRightBy(id_source, id_item){
        let index = this.datas_products_right.findIndex(item => item.id_source === id_source && item.id_item === id_item);
        if (index >= 0){
            document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_RIGHT, { detail: {
                data: this.datas_products_right[index],
                action: ACTION_DATA_DELETE
            }}));
            this.datas_products_right.splice(index, 1);
        }
    }
    
    /**
     * Отменить выбор правого товара по индексу
     * 
     * @param {type} index
     * @returns {undefined}
     */
    deleteRightByIndex(index){
        document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_RIGHT, { detail: {
            data: this.datas_products_right[index],
            action: ACTION_DATA_DELETE
        }}));
        this.datas_products_right.splice(index, 1);
    }
    
    /**
     * Отменить выбор левого товара
     * 
     * @param {type} index
     * @returns {undefined}
     */
    deleteLeftByIndex(index){
        document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_LEFT, { detail: {
            data: this.datas_products_left[index],
            action: ACTION_DATA_DELETE
        }}));
        this.datas_products_left.splice(index, 1);
    }
    
    /**
     * Отмена удаления блока товаров
     * Получилось вот такая тавтология ибо массив для удаления левых товаров называется datas_products_left_delete
     * 
     * @param {type} index индекс записи в datas_products_left_delete
     * @returns {array} Новый массив datas_products_left_delete
     */
    deleteDeleteByIndex(index){
        document.dispatchEvent(new CustomEvent(EVENT_CHANGE_DATA_DELETE, { detail: {
            data: this.datas_products_left_delete[index],
            action: ACTION_DATA_DELETE
        }}));
        this.datas_products_left_delete.splice(index, 1);
    }
    
    /**
     * Удаление блока товаров
     * 
     * @param {type} data_product data данные блока удаленных товаров
     * @returns {Boolean} 
     *      true: если эти данные оказались в массиве новыми
     *      false: если просто обновление записи в массив
     */
    addBlockDelete(data_product){
        // Сморим, есть ли уже этот элемент в массиве левых товаров на удадение, ожидающем отправку
        for (let i = this.datas_products_left_delete.length - 1; i >= 0; --i) {
            let data = this.datas_products_left_delete[i];
            if (data.id_product === data_product.id_product && data.id_source === data_product.id_source) {
                this.datas_products_left_delete[i] = data_product; //Перезаписать
                return false;
            }
        }
        
        // Смотрим, есть ли уже этот элемент в массиве левых товаров, ожидающем отправку. Если есть, то пользователь передумал его отмечать и хочет удалить
        for (let i = this.datas_products_left.length - 1; i >= 0; --i) {
            let data = this.datas_products_left[i];
            if (data.id_product === data_product.id_product && data.id_source === data_product.id_source) {
                this.datas_products_left.splice(i, 1);
                break; // Дальше смотреть нет смысла, ибо значение уникальны                
            }
        }
        
        // Если эмемента в массиве на удаление нет то запоминаем в массив элемент
        this.datas_products_left_delete.push(data_product);
        return true;
    }
    
    /**
     * Получить иддексы данных, соответствующих значениям
     * 
     * @param {number}              id_source   id источника
     * @param {number | undefined}  id_product  id левого  товара
     * @param {number | undefined}  id_item     id правого товара
     * @returns {array}
     */
    findIndexesDatasRightBy(id_source, id_product, id_item){
        let isset_id_product = typeof(id_product)!=="undefined" && id_product!==null;
        let isset_id_item    = typeof(id_item)   !=="undefined" && id_item   !==null;        
        
        return this.datas_products_right.findIndex(data => 
            data.id_source === id_source
            && (!isset_id_product || (data.id_product === id_product))
            && (!isset_id_item    || (data.id_item    === id_item   )));
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