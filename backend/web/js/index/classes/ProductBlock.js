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
export const CLASS_BLOCK_PRODUCT_ROW = '.product-list__product-list-item';
export const CLASS_BLOCK_PRODUCT = '.block_maximize';
export const CLASS_BLOCK_PRODUCT_MIN = '.block_minimize';
const CLASS_BLOCK_PRODUCT_DELETED  = 'product-list__deleted';
const CLASS_BLOCK_PRODUCT_COMPLETED = 'product-list__completed';

const CLASS_HIDE = '-hidden';

export const STATUS_BLOCK_DEFAULT      = 'default';
export const STATUS_BLOCK_DELETE_ALL   = 'delete_all';
export const STATUS_BLOCK_MISMATCH_ALL = 'missmatch_all';
export const STATUS_BLOCK_PREMATCH_ALL = 'pre_match';
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
    
    /**
     * Получить только data атрибуты правых товаров
     * @returns {Object}
     */
    getDatasRight(){
        let list = [];
        this.dom.find(CLASS_PRODUCT_RIGHT).each(function(index, el){
            let productRight = new ProductRight($(this));
            list.push(productRight.data);
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
     * @param {type} is_strict Строгое соответствие. Если false то может быть без выбора
     * @returns {Boolean}
     */
    isMismatchAll(is_strict = true){
        let list_product_right = this.getProductsRight();
        for (let item of list_product_right){
            let colorMarker = item.getStatatusColorMarker();
            if ( colorMarker !== 'mismatch' &&
                (is_strict || colorMarker !== 'nocompare') &&
                (is_strict || colorMarker !== '') ) return false;
        }
        return true;
    }

    /**
     * Являются ли все правые товары отмечеными статусом pre_match
     * @param {type} is_strict Строгое соответствие. Если false то может быть без выбора
     * @returns {Boolean}
     */
    isPrematchAll(is_strict = true){
        let list_product_right = this.getProductsRight();
        for (let item of list_product_right){
            let colorMarker = item.getStatatusColorMarker();
            if ( colorMarker !== 'pre_match' &&
                (is_strict || colorMarker !== 'nocompare') &&
                (is_strict || colorMarker !== '') ) return false;
        }
        return true;
    }
    
    /**
     * Являются ли все правые товары отмеченыt как удаленные
     * 
     * @returns {Boolean}
     */
    isDeletedAll(){
        let list_product_right = this.getProductsRight();
        for (let item of list_product_right){
            let colorMarker = item.getStatatusColorMarker();
            if ( colorMarker !== 'deleted') return false;
        }
        return true;
    }
    
    isSelectAll(){
        let list_product_right = this.getProductsRight();
        for (let item of list_product_right){
            let colorMarker = item.getStatatusColorMarker();
            if ( colorMarker === '' || colorMarker === 'nocompare') return false;
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
     * Устанавливает отображение одного из состояний

     * @param {type} status
     *      STATUS_BLOCK_DEFAULT
     *      STATUS_BLOCK_DELETE_ALL
     *      STATUS_BLOCK_MISMATCH_ALL
     *      STATUS_BLOCK_SELECT_ALL
     * @returns {undefined}
     */
    setStatusVisual(status){
        let domMin = this.getDomMin();
        
        this.dom.removeClass('status_block_delete_all');
        this.dom.removeClass('status_block_mismatch_all');
        this.dom.removeClass('status_block_selected_all');
        
        domMin.removeClass('status_block_delete_all');
        domMin.removeClass('status_block_mismatch_all');
        domMin.removeClass('status_block_selected_all');
        
        switch (status){
            case STATUS_BLOCK_DELETE_ALL:
                this.dom.addClass('status_block_delete_all');
                domMin.addClass('status_block_delete_all');
                break;
            case STATUS_BLOCK_MISMATCH_ALL:
                this.dom.addClass('status_block_mismatch_all');
                domMin.addClass('status_block_mismatch_all');
                break;
            case STATUS_BLOCK_SELECT_ALL:
                this.dom.addClass('status_block_selected_all');
                domMin.addClass('status_block_selected_all');
                break;
            case STATUS_BLOCK_DEFAULT:
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