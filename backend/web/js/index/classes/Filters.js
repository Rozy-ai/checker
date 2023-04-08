'use strict';

const FILTER_BATCH_MODE = '#id_f_batch_mode';
export const CLASS_BUTTON_SHOW_PRODUCTS_ALL = '.js-show_products_all';
const CLASS_BUTTON_SHOW_PRODUCTS_ALL_ACTIVE = 'btn-secondary';
const ID_FILTER_DETAIL_VIEW = '#id_f_detail_view';
const ID_FILTER_ASIN_TYPE = '#id_f_asin_type';
const ID_FILTER_ASIN = '#id_f_asin';

export class Filters {
    /**
     * Включен ли режим скрытия правых товаров после выбора
     * (Пока берем копию кнопки от пакетной выборки)
     * 
     * @returns {Boolean}
     */
    static getModeHide() {
        return !$( CLASS_BUTTON_SHOW_PRODUCTS_ALL ).hasClass( CLASS_BUTTON_SHOW_PRODUCTS_ALL_ACTIVE );
    }

    /**
     * Переключает кнопку Показать все в cкрыть выбранные и наоборот
     * @returns {Boolean}
     */
    static toggleModeHide() {
        let btn = $( CLASS_BUTTON_SHOW_PRODUCTS_ALL );
        btn.toggleClass( CLASS_BUTTON_SHOW_PRODUCTS_ALL_ACTIVE );

        if ( btn.hasClass( CLASS_BUTTON_SHOW_PRODUCTS_ALL_ACTIVE ) ) {
            btn.text( 'cкрыть выбранные' );
            return false;
        } else {
            btn.text( 'показать все' );
            return true;
        }
    }

    /**
     * Аткивно ли отображение списком
     * 
     * @returns {boolean}
     */
    static getModeMinimize() {
        let val = $( ID_FILTER_DETAIL_VIEW ).val();
        return ( val == 2 || val == 3 );
    }

    /**
     * Включен ли режим скрытия правых товаров после выбора
     * 
     * @returns {Boolean}
     */
    static getModeBatch() {
        return true;
        //let $mode = $(FILTER_BATCH_MODE);
        //return $mode.is(':checked');
    }

    static attachEvents() {
        $( 'body' ).on( 'change', ID_FILTER_ASIN_TYPE, ( e ) => {
            $( ID_FILTER_ASIN ).attr( 'placeholder', $( e.target ).find( 'option:selected' ).text() );
        } );
    }
}