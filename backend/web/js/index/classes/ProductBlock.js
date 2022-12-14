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

export const CLASS_BLOCK_BUTTON_CLOSE = '.slider_close';
export const CLASS_BLOCK_PRODUCT = '.product-list__product-list-item';

const CLASS_HIDE = 'd-none';
const CLASS_BLOCK_PRODUCT_MAX = '.block_maximize';
export const CLASS_BLOCK_PRODUCT_MIN = '.block_minimize';
export const CLASS_BLOCK_BUTTON_DELETE = 'js-del-item';
    
export class ProductBlock extends DomWithData{
    
    static getFromChild($child_object, data, is_maximize = true) {
        return super.getFromChild(CLASS_BLOCK_PRODUCT, $child_object, data);
    }
    
    getCountProductRight(){
        return $(this.dom.find(CLASS_PRODUCT_RIGHT)).length;
    }
    
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
     * Проверяет, есть ли в списке правых продуктов товары с отмеченными статусами
     * @returns {Boolean}
     */
    isHaveStatusProductsRight(){
        let list_product_right = this.getProductsRight();
        for (let product of list_product_right){
            if (
                typeof(product.status) !== "undefined" && 
                product.status !== null &&
                product.status !== 'MISMATCH' &&
                product.status.length > 0
            ) return true; 
        }
        return false;
    }
    
    /**
     * Изменить визуальное отображение блока товара
     *    Если выключен режим скрытия выбранных товаров, блок не выделяется никак (Выделяется содержимое)
     *    Если включен режим скрытия выбранных товаров то блок необходимо визуально скрыть или показать
     * @param {type} is_done      Блок товара больше не участвует в выборе товара (Или скрываетя или красиво отмечается или другое)
     * @param {type} is_hide_mode Режим скрытия после выбора
     * @returns {undefined}
     */
    changeVisual(is_done, is_hide_mode){
        if (is_hide_mode){
            if (is_done){
                this.dom.hide();
            } else {
                this.dom.show();
            }
        } else {
            this.dom.show();
            //if (is_done){
            //    this.minimize();
            //} else {
            //    this.maxmize();
            //}
        }
    }
    
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