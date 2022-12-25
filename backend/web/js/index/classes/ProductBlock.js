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

export const STATUS_BLOCK_DEFAULT      = 'default';
export const STATUS_BLOCK_DELETE_ALL   = 'delete_all';
export const STATUS_BLOCK_MISMATCH_ALL = 'missmatch_all';
export const STATUS_BLOCK_SELECT_ALL   = 'notfound';

    
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
     * Являются ли все правые товары отмечеными статусом mismatch
     * 
     * @returns {Boolean}
     */ 
    isMismatchAll(){
        let list_product_right = this.getProductsRight();
        for (let item of list_product_right){
            let colorMarker = item.getStatatusColorMarker();
            if ( colorMarker !== 'mismatch') return false;
        }
        return true;
    }
    
    /**
     * Являются ли все правые товары отмеченыt как удаленные
     * 
     * @returns {Boolean}
     */
    isDeleteAll(){
        let list_product_right = this.getProductsRight();
        
        for (let item of list_product_right){
            if (!item.isHasClassDeleted()) return false;
        }   
        return true;
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
     * Добавляет отображение одного из состояний

     * @param {type} status
     *      STATUS_BLOCK_DEFAULT
     *      STATUS_BLOCK_DELETE_ALL
     *      STATUS_BLOCK_MISMATCH_ALL
     *      STATUS_BLOCK_SELECT_ALL
     * @returns {undefined}
     */
    addStatusVisual(status){
        switch (status){
            case STATUS_BLOCK_DEFAULT:
                break;
            case STATUS_BLOCK_DELETE_ALL:
                this.dom.addClass('status_block_delete_all');
                break;
            case STATUS_BLOCK_MISMATCH_ALL:
                this.dom.addClass('status_block_mismatch_all');
                break;
            case STATUS_BLOCK_SELECT_ALL:
                this.dom.addClass('status_block_selected_all');
                break;
        }
    }
    
    /**
     * Убирает отображение одного из состояний

     * @param {type} status
     *      STATUS_BLOCK_DEFAULT
     *      STATUS_BLOCK_DELETE_ALL
     *      STATUS_BLOCK_MISMATCH_ALL
     *      STATUS_BLOCK_SELECT_ALL
     * @returns {undefined}
     */
    removeStatusVisual(status){
        switch (status){
            case STATUS_BLOCK_DEFAULT:
                break;
            case STATUS_BLOCK_DELETE_ALL:
                this.dom.removeClass('status_block_delete_all');
                break;
            case STATUS_BLOCK_MISMATCH_ALL:
                this.dom.removeClass('status_block_mismatch_all');
                break;
            case STATUS_BLOCK_SELECT_ALL:
                this.dom.removeClass('status_block_selected_all');
                break;
        }
    }
    
    /**
     * Устанавливает режим просмотра товара
     * 
     * @param {type} is_mode_hide
     * @param {type} is_mode_minimize
     * @returns {undefined}
     */
    setModeVisual(is_mode_hide, is_mode_minimize){
        if (is_mode_hide){
            this.getDomMin().css('display', 'none');
            this.dom.css('display', 'none');
        } else {
            if (is_mode_minimize){
                this.dom.css('display', 'none');
                this.getDomMin().css('display', 'table-row');
            } else {
                this.getDomMin().css('display', 'none');
                this.dom.css('display', 'table-row');
            }
        }
    }
};