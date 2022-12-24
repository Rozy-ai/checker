'use strict';

import {
    DomWithData
} from './DomWithData.js'

import {
    CLASS_PRODUCT_LEFT,
    CLASS_PRODUCT_BUTTON_CLOSE,
    ProductLeft
} from './ProductLeft.js'

import {
    CLASS_PRODUCT_RIGHT,
    ProductRight
} from './ProductRight.js'

import {
    CLASS_ITEM_PRE_MATCH,
    CLASS_ITEM_MATCH,
    CLASS_ITEM_MISMATCH,
    CLASS_ITEM_OTHER,
    CLASS_ITEM_NOCOMPARE,
    CLASS_STATISTIC,
    Statistic
} from './Statistic.js'

export const CLASS_BLOCK_BUTTON_DELETE = '.js-del-item';
export const CLASS_BLOCK_BUTTON_CLOSE = '.slider_close';
//export const CLASS_BLOCK_PRODUCT = '.product-list__product-list-item';
export const CLASS_BLOCK_PRODUCT = '.block_maximize';
export const CLASS_BLOCK_PRODUCT_MIN = '.block_minimize';
const CLASS_BLOCK_PRODUCT_DELETED  = 'product-list__deleted';
const CLASS_BLOCK_PRODUCT_COMPLETED = 'product-list__completed';

const CLASS_HIDE = '-hidden';

export const STATUS_DELETED     = 'deleted';        // Блок ототображается с удаленным левым товаром
export const STATUS_MISMATCH_ALL= 'missmatched';    // Отображение с нажатым красным крестиком слева
export const STATUS_NOT_FOUND   = 'notfound';       // Отображение когда закончились правые товары без сравнения
export const STATUS_DEFAULT     = 'default';        // Блок отображается нормально без выделений
    
export class ProductBlock extends DomWithData{
    static getFromChild($child_object, data) {
        return super.getFromChild(CLASS_BLOCK_PRODUCT, $child_object, data);
    }
    
    static getByPid(pid){
        let dom = $(CLASS_BLOCK_PRODUCT+`[data-pid=${pid}]`);
        return new this(dom);
    }
    
    getDomMin(){
        let pid = this.dom.data('pid');
        return $(CLASS_BLOCK_PRODUCT_MIN+`[data-pid=${pid}]`);        
    }
    
    /**
     * Сколько всего правых товаров в блоке
     * 
     * @returns {number}
     */
    getCountProductRight(){
        return $(this.dom.find(CLASS_PRODUCT_RIGHT)).length;
    }
    
    /**
     * Сколько правых товаров в блоке не имеет статуса сравнения
     *
     * @returns {Number}
     */
    getCountProductRightWithNoStatus(){
        
    }
    
    /**
     * Количество видимых товаров в блоке
     * @returns {number}
     */
    getCountProductRightVisible(){
        return $(this.dom.find(CLASS_PRODUCT_RIGHT+':visible')).length;
    }
    
    /**
     * 
     * @returns {ProductLeft}
     */
    getProductLeft(){
        let dom = this.dom.find(CLASS_PRODUCT_LEFT+':first');
        let productLeft = new ProductLeft(dom);
        return productLeft;
    }
    
    /**
     * Получить модели всех правых продуктов что есть в данном блоке
     * 
     * @returns {ProducsRight[]}
     */
    getProductsRight(){
        let list = [];
        this.dom.find(CLASS_PRODUCT_RIGHT).each(function(index){
            let productRight = new ProductRight($(this));
            list.push(productRight);
        });
        return list;
    }
    
    getStatistic(){
        let dom = this.dom.find(CLASS_STATISTIC+':first');
        return new Statistic(dom);
    }
    
    /**
     * Имеется ли статус отслычный от missmatch
     * 
     * @param {type} and_is_compare
     *      true - товар имеет хоть катой то выбранный статус сравнения (не nocompare)
     *      false - товар может быть без отмеченного 
     * @returns {Boolean}
     */
    isHasStatusNotMismatch(and_is_compare){
        let list_product_right = this.getProductsRight();
        for (let item of list_product_right){
            let colorMarker = item.getStatatusColorMarker();
            
            if ( colorMarker !== 'mismatch' && (!and_is_compare || colorMarker !== 'nocompare')) {
                return true;
            }
        }
        return false;        
    }
    
    /**
     * Есть ли справа товары без отмеченных цветом статусов
     * 
     * @returns {bool}
     */
    isHasProductRightWithoutColormarker(){
        let list_product_right = this.getProductsRight();
        for (let item of list_product_right){
            let colorMarker = item.getStatatusColorMarker();
            if (colorMarker.length <= 0 || colorMarker === 'nocompare') return true;
        }
        return false;
    }

    /**
     * Изменить визуальное отображение блока товара
     * 
     * @param {string} status_visual Статус отображения. В зависимоси от статуса могум менаться и стили и отображение
     * @param {boolean} is_hide_mode Включено ли скрытие товаров после выбора
     * @param {boolean} is_min Свернутый или развернутый режим
     * @returns {undefined}
     */
    changeVisual(status_visual, is_hide_mode, is_min = false){
        if (is_hide_mode){
            this.getDomMin().css('display', 'none');
            this.dom.css('display', 'none');
        } else {
            if (is_min === true){
                this.dom.css('display', 'none');
                this.getDomMin().css('display', 'table-row');
            } else {
                this.getDomMin().css('display', 'none');
                this.dom.css('display', 'table-row');
            }
        }
        
        let productLeft = this.getProductLeft();
        switch (status_visual){
            // Когда навсегда удален левый товар вместе с правыми
            case STATUS_DELETED:
                this.dom.addClass(CLASS_BLOCK_PRODUCT_DELETED);
                this.dom.removeClass(CLASS_BLOCK_PRODUCT_COMPLETED); 
                productLeft.changeVisual(false, is_hide_mode);      // Выключаем левый крестик
                break;
            // Когда у всех правых товаров статус mismatch
            case STATUS_MISMATCH_ALL:
                this.dom.removeClass(CLASS_BLOCK_PRODUCT_DELETED);
                this.dom.removeClass(CLASS_BLOCK_PRODUCT_COMPLETED);
                productLeft.changeVisual(true, is_hide_mode);       // Включаем левый крестик
                break;
            // Когда в блоке закончилиь праыве товары без статусов
            case STATUS_NOT_FOUND:
                this.dom.removeClass(CLASS_BLOCK_PRODUCT_DELETED);  // Убираем выделение удаленного блока цветом
                productLeft.changeVisual(false, is_hide_mode);      // Выключаем левый крестик
                this.dom.addClass(CLASS_BLOCK_PRODUCT_COMPLETED);   // Зделаем блок светло зеленым
                break;
            // Отображение без всяких выделений
            case STATUS_DEFAULT:
                this.dom.removeClass(CLASS_BLOCK_PRODUCT_DELETED);
                this.dom.removeClass(CLASS_BLOCK_PRODUCT_COMPLETED);
                productLeft.changeVisual(false, is_hide_mode);      // Выключаем левый крестик
                break;
        }
    }
    /*
    minimize(){
        this.dom.addClass(CLASS_HIDE);
        
        let pid = this.dom.data('pid');
        $(CLASS_BLOCK_PRODUCT_MIN+`[data-pid=${pid}]`).removeClass(CLASS_HIDE);
    }
    
    maximize(){
        this.dom.addClass(CLASS_HIDE);
        
        let pid = this.dom.data('pid');
        $(CLASS_BLOCK_PRODUCT_MAX+`[data-pid=${pid}]`).removeClass(CLASS_HIDE);
    }
    */
    /**
     * Зделать правые товары видимыми в блоке только отмеченные маркером с данным классом
     * 
     * @param {string} class_marker класс маркера цвета, который нужно оставить 
     * @returns {undefined}
     */
    showProductsRight (class_marker) {
        let items = this.getProductsRight();
        for (let item of items){
            if (item.dom.find('.color-marker').hasClass(class_marker)){
                item.dom.show();
            } else {
                item.dom.hide();
            }
        };
    }
};