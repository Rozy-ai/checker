'use strict';

import {
    Ajax
} from './Ajax.js';
    
export class ListDataForServer{
    datas_products_left  = [];
    datas_products_right = [];
    datas_products_left_delete = [];
    
    /**
     * Запомнить выбор от правых товаров (Элементов)
     * 
     * @param {object} data_item
     * @returns {boolean} Является ли товар новым
     */    
    addRight(data_item){
        // Сморим, есть ли уже этот элемент в массиве правых товаров, ожидающем отправку
        for (let i = this.datas_products_right.length - 1; i >= 0; --i) {
            let data = this.datas_products_right[i];
            if (data.id_item === data_item.id_item && data.id_source === data_item.id_source) {
                this.datas_products_right[i] = data_item; //Перезаписать
                return false;
            }
        }

        // Если эмемента в массиве нет то запоминаем в массив элемент
        this.datas_products_right.push(data_item);
        return true;
    }
    
    /**
     * Запомнить выбор левых
     * 
     * @param {object} data_product
     * @returns {boolean} Является ли товар новым
     */
    addLeft(data_product){
        // Сморим, есть ли уже этот элемент в массиве левых товаров, ожидающем отправку
        for (let i = this.datas_products_left.length - 1; i >= 0; --i) {
            let data = this.datas_products_left[i];
            if (data.id_product === data_product.id_product && data.id_source === data_product.id_source) {
                this.datas_products_left[i] = data_product; //Перезаписать
                return false;
            }
        }

        // Если эмемента в массиве нет то запоминаем в массив элемент
        this.datas_products_left.push(data_product);
        return true;
    }
    
    /**
     * Запомнить выбор левых на удаление
     * 
     * @param {type} data_product data данные левых товаров на удаление
     * @returns {Boolean} Если запись в массив была новая, то true
     */
    addLeftDelete(data_product){
        // Сморим, есть ли уже этот элемент в массиве левых товаров на удадение, ожидающем отправку
        for (let i = this.datas_products_left_delete.length - 1; i >= 0; --i) {
            let data = this.datas_products_left_delete[i];
            if (data.id_product === data_product.id_product && data.id_source === data_product.id_source) {
                this.datas_products_left_delete[i] = data_product; //Перезаписать
                return false;
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
    
    sendToServer(onSuccess){
        let data = {
            list_products_right: this.datas_products_left,
            list_products_left: this.datas_products_right
        };
        
        Ajax.send('/product/compare-batch', data, (response) => {
            if (response.status === 'ok'){
                this.datas_products_left  = [];
                this.datas_products_right = [];
            }
            onSuccess(response);
        });
    }
};