'use strict';

import {
    DomWithData
} from './DomWithData.js'

export const CLASS_PRODUCT_RIGHT               = '.slider__slider-item';
export const CLASS_BUTTON_RED                  = '.slider__red_button';
export const CLASS_BUTTON_YELLOY               = '.slider__yellow_button';

export class ProductRight extends DomWithData{
    
    static getFromChild($child_object, data) {
        return super.getFromChild(CLASS_PRODUCT_RIGHT, $child_object, data);
    }
    
    static getBy(id_source, id_item){
        let el = $(CLASS_PRODUCT_RIGHT+`[data-id_source=${id_source}][data-id_item=${id_item}]`+':first');
        if (el.length !== 1){
            return null;
        }
        return new this(el);
    }
    
    /**
     * Меняем классы и показываем или скрываем товары в зависимости от класса
     * 
     * @param {string} status
     * @param {boolean} is_mode_hide
     * @returns {undefined}
     */
    changeVisual(status, is_mode_hide = false){
        // Меняем классы
        this.dom.find('.color-marker')
                .removeClass('nocompare')
                .removeClass('pre_match')
                .removeClass('other')
                .removeClass('match')
                .removeClass('mismatch')
                .addClass(status);

        switch (status){
            case 'mismatch':
                this.dom.find(CLASS_BUTTON_YELLOY).removeClass('-hover');
                this.dom.find(CLASS_BUTTON_RED).addClass('-hover');                
                break;
            case 'pre_match':
                this.dom.find(CLASS_BUTTON_RED).removeClass('-hover');
                this.dom.find(CLASS_BUTTON_YELLOY).addClass('-hover');
                break;
            default:
                this.dom.find(CLASS_BUTTON_YELLOY).removeClass('-hover');
                this.dom.find(CLASS_BUTTON_RED).removeClass('-hover');
        }
        
        if (is_mode_hide){
            if (status){
                this.dom.hide();
            } else {
                this.dom.show();
            }
        } else {
            this.dom.show();
        }
    }
};