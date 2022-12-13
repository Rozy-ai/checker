'use strict';

import {
    DomWithData
} from './DomWithData.js'

export const CLASS_PRODUCT_LEFT = '.products-list__img-wrapper';
export const CLASS_PRODUCT_BUTTON_CLOSE = '.product-list__item-mismatch-all';

export class ProductLeft extends DomWithData{
    
    /**
     * 
     * @param {Object} $child_object    DOM обьект указывающий на текущий левый товар
     * @param {Object} data             data атрибуты, этого обьекта
     * @returns {unresolved}
     */
    static getFromChild($child_object, data) {
        return super.getFromChild(CLASS_PRODUCT_LEFT, $child_object, data);
    }
    
    static getBy(id_source, id_product){
        let el = $(CLASS_PRODUCT_LEFT+`[data-id_source=${id_source}][data-id_product=${id_product}]`+':first');
        return new this(el);
    }
    
    /**
     * Изменяем вид левого товара
     * В любом межиме меняем  классы отображения
     * 
     * @param {Boolean} is_mismached   Явдяется ли левый товар выбранным левым крестиком
     * @param {Boolean} is_mode_hide   Включен ли режим скрытия после выбора
     * @returns {undefined}
     */
    changeVisual(is_mismached, is_mode_hide = false){
        let button = this.dom.find(CLASS_PRODUCT_BUTTON_CLOSE);
        
        if (is_mismached){
            button.addClass('-hover');
        } else {
            button.removeClass('-hover');
        }
    }
};