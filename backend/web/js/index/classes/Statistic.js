'use strict';

import {
    DomWithData
} from './DomWithData.js'

export const CLASS_STATISTIC    = 'div.block_statistic';
const CLASS_PROCESSED       = '.product-list-item__processed'; // Блок статистики, показывающий сколько товаров отработано
const CLASS_ITEM_STATISTIC  = 'span.js-';

export const CLASS_ITEM_PRE_MATCH  = '.js-pre_match';
export const CLASS_ITEM_MATCH      = '.js-match';
export const CLASS_ITEM_MISMATCH   = '.js-mismatch';
export const CLASS_ITEM_OTHER      = '.js-other';
export const CLASS_ITEM_NOCOMPARE  = '.js-nocompare';
export const CLASS_ITEM_STAT       = '.js-product-stat';

/**
 * Класс статистики. Может работать с несколькими родителями(блоками). Изменения идут во всех сразу
 * @type {type}
 */
export class Statistic extends DomWithData{

    static getFromChild($child_object, data) {
        return super.getFromChild(CLASS_STATISTIC, $child_object, data);
    };

    static getFirstFromParent($dom){
        return super.getFirstFromParent(CLASS_STATISTIC, $dom);
    };

    static getFromParents($doms){
        return super.getFromParent(CLASS_STATISTIC, $doms);
    };

    /**
     * Увеличить значение в статистике
     *
     * @param {string} status_compare каким статусом отмечать правый товар
     * @returns {undefined}
     */
    addUnit(status_compare){
        // Увеличивам количество в целевом квадратике
        let $class = CLASS_ITEM_STATISTIC+status_compare.toLowerCase();
        let $block = this.dom.find($class);
        $block.text(Number($block.first().text())+1);

        // Уменьшаем количество в белом квадратике
        $block = this.dom.find(CLASS_ITEM_NOCOMPARE);
        $block.text(Number($block.first().text())-1);

        // Меняем запись общее
        $block = this.dom.find(CLASS_PROCESSED);
        let val = $block.first().text().split('/');
        if (val.length !== 2) return;
        let v1 = Number(val[0])+1;
        $block.text(v1+'/'+val[1]);
    };

    /**
     * Уменьшить значение в статистике
     *
     * @param {string} status_compare какой статус был у правого товара
     * @returns {undefined}
     */
    deleteUnit(status_compare){
        // Уменьшаем количество в целевом квадратике
        let $class = CLASS_ITEM_STATISTIC+status_compare.toLowerCase();
        let $block = this.dom.find($class);
        $block.text(Number($block.first().text())-1);

        // Увеличиваем количество в белом квадратике
        $block = this.dom.find(CLASS_ITEM_NOCOMPARE);
        $block.text(Number($block.first().text())+1);

        // Меняем запись общее
        $block = this.dom.find(CLASS_PROCESSED);
        let val = $block.first().text().split('/');
        if (val.length !== 2) return;
        let v1 = Number(val[0])-1;
        $block.text(v1+'/'+val[1]);
    };

    /**
     * Изменить количетво статуса
     *
     * @param {string} status_last
     * @param {string} status_new
     * @returns {undefined}
     */
    changeUnit(status_last, status_new){
        // Уменьшаем количество в прошлом квадратике
        let $class = CLASS_ITEM_STATISTIC+status_last.toLowerCase();
        let $block = this.dom.find($class);
        $block.text(Number($block.first().text())-1);

        // Увеличиваем количество в новом квадратике
        $class = CLASS_ITEM_STATISTIC+status_new.toLowerCase();
        $block = this.dom.find($class);
        $block.text(Number($block.first().text())+1);
    };

    /**
     * Сбросить все квадратики в первоначальное состояние. Та цифра что указана в data атрибуте
     * @returns {undefined}
     */
    reset(){
        this.dom.find(CLASS_ITEM_STATISTIC+'pre_match').text(this.data.pre_match);
        this.dom.find(CLASS_ITEM_STATISTIC+'match').text(this.data.match);
        this.dom.find(CLASS_ITEM_STATISTIC+'mismatch').text(this.data.mismatch);
        this.dom.find(CLASS_ITEM_STATISTIC+'other').text(this.data.other);
        this.dom.find(CLASS_ITEM_STATISTIC+'nocompare').text(this.data.nocompare);

        this.dom.find(CLASS_PROCESSED).text(this.data.processed);
    };

    //getValue(class_item){
    //    return +this.dom.find(class_item).text();
    //}
};
