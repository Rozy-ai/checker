'use strict';

import {
    DomWithData
} from './DomWithData.js'

export const CLASS_PRODUCT_RIGHT               = '.slider__slider-item';
export const CLASS_BUTTON_RED                  = '.slider__red_button';
export const CLASS_BUTTON_YELLOY               = '.slider__yellow_button';

export const STATUS_PRODUCT_RIGHT_DELETED      = 'status_product_right_deleted';

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
    
    setStatusVisual(status){
        status = status.toLowerCase();
        
        this.dom.find('.color-marker')
                .removeClass('nocompare')
                .removeClass('pre_match')
                .removeClass('other')
                .removeClass('match')
                .removeClass('mismatch')
                .removeClass('deleted')
                .addClass(status);
        
        this.dom.find(CLASS_BUTTON_YELLOY).removeClass('-hover');
        this.dom.find(CLASS_BUTTON_RED).removeClass('-hover');
        this.dom.find('.slider-item__border').removeClass(STATUS_PRODUCT_RIGHT_DELETED);
        
        switch (status){
            case 'mismatch':
                this.dom.find(CLASS_BUTTON_RED).addClass('-hover');
                break;
            case 'pre_match':
                this.dom.find(CLASS_BUTTON_YELLOY).addClass('-hover');
                break;
            case 'deleted':
                this.dom.find('.slider-item__border').addClass(STATUS_PRODUCT_RIGHT_DELETED);
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
    setModeVisual(is_mode_hide){
        if (is_mode_hide){
            this.dom.hide();
        } else {
            this.dom.show();
        }
    }
    
    /**
     * Какой статус установлен у данного товара
     * @returns {String}
     */
    getStatatusColorMarker(){
        let colorMarker = this.dom.find('.color-marker');
        if (colorMarker.hasClass('nocompare')) return 'nocompare';
        if (colorMarker.hasClass('pre_match')) return 'pre_match';
        if (colorMarker.hasClass('other'))     return 'other';
        if (colorMarker.hasClass('match'))     return 'match';
        if (colorMarker.hasClass('mismatch'))  return 'mismatch';
        if (colorMarker.hasClass('deleted'))   return 'deleted';
        return '';
    }
};