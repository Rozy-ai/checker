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
    
    /**
     * Добавить визуальное отображение правого товара соответсвующего статусу 
     * Тут статус совпадает с классом ( кроме deleted )
     * @param {type} status
     * @returns {undefined}
     */
    addStatusVisual(status){
        status = status.toLowerCase();
        let marker = this.dom.find('.color-marker');
        
        switch (status){
            case 'nocompare':
                marker.addClass('nocompare');
                break;
            case 'pre_match':
                marker.addClass('pre_match');
                this.dom.find(CLASS_BUTTON_YELLOY).addClass('-hover');
                break;
            case 'other':
                marker.addClass('other');
                break;
            case 'match':
                marker.addClass('match');
                break;
            case 'mismatch':
                marker.addClass('mismatch');
                this.dom.find(CLASS_BUTTON_RED).addClass('-hover');
                break;
            case 'deleted':
                this.dom.removeClass(STATUS_PRODUCT_RIGHT_DELETED);
                break;
            /*    
            default:
                this.dom.find(CLASS_BUTTON_RED).removeClass('-hover');
                this.dom.find(CLASS_BUTTON_YELLOY).removeClass('-hover');
                marker
                    .removeClass('nocompare')
                    .removeClass('pre_match')
                    .removeClass('other')
                    .removeClass('match')
                    .removeClass('mismatch');
            */
        }
    }
    
    /**
     * Убрать визуальное отображение правого товара соответсвующего статусу
     * Тут статус совпадает с классом
     * @param {type} status
     * @returns {undefined}
     */
    removeStatusVisual(status){
        status = status.toLowerCase();
        let marker = this.dom.find('.color-marker');
        
        switch (status){
            case 'nocompare':
                marker.removeClass('nocompare');
                break;
            case 'pre_match':
                marker.removeClass('pre_match');
                this.dom.find(CLASS_BUTTON_YELLOY).removeClass('-hover');
                break;
            case 'other':
                marker.removeClass('other');
                break;
            case 'match':
                marker.removeClass('match');
                break;
            case 'mismatch':
                marker.removeClass('mismatch');
                this.dom.find(CLASS_BUTTON_RED).removeClass('-hover');
                break;
            case 'deleted':
                this.dom.removeClass(STATUS_PRODUCT_RIGHT_DELETED);
                break;
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
        return '';
    }
    
    isHasClassDeleted(){
        return this.dom.hasClass(STATUS_PRODUCT_RIGHT_DELETED);
    }
};