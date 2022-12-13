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
    
  
    
export class ProductBlock extends DomWithData{
    html;
    
    static getFromChild($child_object, data) {
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
        // Сохранить текущее отображение
        this.html = this.dom.html();
        
        // Рассчитываем новое отображение
        let td1_bsr = this.dom.find('#id_td1_bsr').text();
        let td1_sales30 = this.dom.find('#id_td1_sales30').text();
        let td1_price = this.dom.find('#id_td1_price').text();
        let td1_fba = this.dom.find('#id_td1_fba').text();
        
        let td2_padding_right = this.dom.find('.products-list__td2').css('padding-right');
        let td2_add_style = (typeof(td2_padding_right) !== "undefined" && td2_padding_right !== null && td2_padding_right.length > 0)?
            `style='padding-right:${td2_padding_right};'`:'';
            
        let td2_asin = this.dom.find('#id_td2_asin').text();
        let td2_toptext = this.dom.find('#id_td2_toptext').text();
        let td3_title = this.dom.find('#id_td3_title').text();
        let pre_match = this.dom.find(CLASS_ITEM_PRE_MATCH).text();
        let match     = this.dom.find(CLASS_ITEM_MATCH).text();
        let mismatch  = this.dom.find(CLASS_ITEM_MISMATCH).text();
        let other     = this.dom.find(CLASS_ITEM_OTHER).text();
        let nocompare = this.dom.find(CLASS_ITEM_NOCOMPARE).text();
        
        let html_minimize =
       `<td colspan="2" class="products-list__td1_minimize text-nowrap" style="text-align: left">
            <div class="d-inline-block" style="text-align: center">
                <div class="block_minimize_data d-inline-block"><span>BSR</span><br>${td1_bsr}</div>
                <div class="block_minimize_data d-inline-block"><span>Sales30</span><br>${td1_sales30}</div>
                <div class="block_minimize_data d-inline-block"><span>Price</span><br>${td1_price}</div>
                <div class="block_minimize_data d-inline-block"><span>FBA/FBM</span><br>${td1_fba}</div>
            <div>
        </td>
        <td class="products-list__td3 block_minimize">
            <div class="block_minimize_wrapper">
                <p class="minimize_row"><span class=minimize_row_asin>${td2_asin}</span>  <span>${td2_toptext}</span></p>
                <p class="minimize_wrapper_title">${td3_title}</p>               
            </div>
        </td>
        <td class="products-list__td4">
            <div class="product-list-item__data -first-margin block_statistic">
                <div class="product-list-item__compare-statistics">
                    <span class="js-pre_match pre_match">${pre_match}</span>
                    <span class="js-match match">${match}</span>
                    <span class="js-mismatch mismatch">${mismatch}</span>
                    <span class="js-other other">${other}</span>
                    <span class="js-nocompare nocompare">${nocompare}</span>
                </div>
            </div>
        </td>`;
        
        let html_minimize2 = 
       `<td class="products-list__td1">
            <div class="product-list-item__data"><span>BSR:</span><br>${td1_bsr}</div>
        </td>
        <td class="products-list__td2" ${td2_add_style}>
            <div>${td2_asin}</div>
        </td>
        <td class="products-list__td3">
            <div class="products-list__slider-wrapper">
                <div class="main-item-title">${td3_title}</div>
            </div>
        </td>
        <td class="products-list__td4">
            <div class="product-list-item__data -first-margin block_statistic">
                <div class="product-list-item__compare-statistics">
                    <span class="js-pre_match pre_match">${pre_match}</span>
                    <span class="js-match match">${match}</span>
                    <span class="js-mismatch mismatch">${mismatch}</span>
                    <span class="js-other other">${other}</span>
                    <span class="js-nocompare nocompare">${nocompare}</span>
                </div>
            </div>
        </td>`;
        
        // Устанавливаем его
        this.dom.html(html_minimize);
    }
    
    maximize(){
        this.dom.html(this.html);
        this.html = '';
    }
};