'use strict';

var win = $( window );
win.on( 'load', function () {
    $( '#preloader' ).delay( 350 ).fadeOut( 'slow' );
    $( 'body' ).delay( 350 ).css( { 'overflow': `visible` } );
} );

import {
    Ajax
} from './classes/Ajax.js';

import {
    CLASS_BUTTON_SHOW_PRODUCTS_ALL,
    Filters
} from './classes/Filters.js';

import {
    CLASS_BLOCK_BUTTON_DELETE,
    CLASS_BLOCK_BUTTON_CLOSE,
    CLASS_BLOCK_PRODUCT,
    CLASS_BLOCK_PRODUCT_MIN,
    STATUS_BLOCK_DEFAULT,
    STATUS_BLOCK_DELETE_ALL,
    STATUS_BLOCK_MISMATCH_ALL,
    STATUS_BLOCK_SELECT_ALL,
    STATUS_BLOCK_PREMATCH_ALL,
    ProductBlock,
} from './classes/ProductBlock.js'

import {
    CLASS_PRODUCT_LEFT,
    ProductLeft,
} from './classes/ProductLeft.js'

import {
    CLASS_STATISTIC,
    CLASS_ITEM_PRE_MATCH,
    CLASS_ITEM_MATCH,
    CLASS_ITEM_MISMATCH,
    CLASS_ITEM_OTHER,
    CLASS_ITEM_NOCOMPARE,
    CLASS_ITEM_STAT,
    Statistic
} from './classes/Statistic.js'

import {
    CLASS_PRODUCT_RIGHT,
    CLASS_BUTTON_RED,
    CLASS_BUTTON_YELLOY,
    ProductRight
} from './classes/ProductRight.js'

import {
    EVENT_CHANGE_DATA_RIGHT,
    ACTION_DATA_CREATE,
    ACTION_DATA_CHANGE,
    ACTION_DATA_DELETE,
    ListDataForServer
} from './classes/ListDataForServer.js';

const CLASS_BUTTON_MISSMATCH_ALL = '.product-list__item-mismatch-all'; // Левый крестик
const CLASS_BUTTON_RESET_FILTERS = '#id_button_reset_filters';
const CLASS_BUTTON_ADDITIONAL_FILTERS = '#additional_filter_link';
const CLASS_BLOCK_BUTTON_DELETE_ALL = '.js-del-all-visible-items';
const CLASS_CHECKBOX_EXPORT_FILTERED = '#id_export_filtered';
const CLASS_BUTTON_EDIT_PROFILE = '.product-list-item__edit-profile';
const CLASS_ELEMENT_PROFILE = '.product-list-item__profile';
const CLASS_BUTTON_PRODUCT_FAVOR = '.products-list__favor';

function main() {
    let listDataForServer = new ListDataForServer();
    let $body = $( 'body' );

    let active_filtr_status = $( '#id_f_comparison_status' ).val();


    /**
     * Обработка события изменения статуса правого товара
     */
    document.addEventListener( EVENT_CHANGE_DATA_RIGHT, function ( event ) {
        let productLeft = ProductLeft.getBy( event.detail.data.id_source, event.detail.data.id_product );
        let blockProduct = ProductBlock.getFromChild( productLeft.dom );
        let productRight = ProductRight.getBy( event.detail.data.id_source, event.detail.data.id_item );

        let parents_for_statistic = $( `[data-pid=${blockProduct.data.pid}]` );
        let statistic = Statistic.getFromParents( parents_for_statistic );

        let is_mode_hide = Filters.getModeHide();
        let is_mode_minimize = Filters.getModeMinimize();

        switch ( event.detail.action ) {
            // В массив добавился статус правого товара
            case ACTION_DATA_CREATE:
                productRight.setStatusVisual( event.detail.data.status );
                productRight.setModeVisual( is_mode_hide );

                if ( blockProduct.isMismatchAll() ) {
                    blockProduct.setStatusVisual( STATUS_BLOCK_MISMATCH_ALL );
                    blockProduct.setModeVisual( false, true );    //Видимый, свернутый
                } else if ( blockProduct.isDeletedAll() ) {
                    blockProduct.setStatusVisual( STATUS_BLOCK_DELETE_ALL );
                    blockProduct.setModeVisual( false, true );    //Видимый, свернутый
                } else if ( blockProduct.isSelectAll() ) {
                    blockProduct.setStatusVisual( STATUS_BLOCK_SELECT_ALL );
                    blockProduct.setModeVisual( false, true );    //Видимый, свернутый
                }

                // Добавим статус в статискику
                statistic.addUnit( event.detail.data.status );

                //Если правые все правые продукты имеют статусы то нужна отправка на сервер с перезагрузкой
                checkAndSendAllStatuses( Filters.getModeHide() );

                break;
            case ACTION_DATA_DELETE:
                productRight.setStatusVisual( '' );  // Убрать выделение
                productRight.setModeVisual( false ); // Отобразить в любом случае

                //Уберем из статистики
                statistic.deleteUnit( event.detail.data.status );

                blockProduct.setStatusVisual( STATUS_BLOCK_DEFAULT );
                blockProduct.setModeVisual( false, false );   // Видимый, развернутый
                break;

            case ACTION_DATA_CHANGE:
                productRight.setStatusVisual( event.detail.data.status );

                let filter_status = $( '#id_f_comparison_status' ).val();

                if ( filter_status === "MISMATCH" || filter_status === "PRE_MATCH" ) {
                    if ( event.detail.data.status !== filter_status ) {
                        productRight.setModeVisual( true );
                        if ( filter_status === "MISMATCH" && blockProduct.isPrematchAll() ) {
                            blockProduct.setStatusVisual( STATUS_BLOCK_PREMATCH_ALL );
                            blockProduct.setModeVisual( false, true ); //Видимый, свернутый
                        } else {
                            if ( blockProduct.isMismatchAll() ) {
                                blockProduct.setStatusVisual( STATUS_BLOCK_MISMATCH_ALL );
                                blockProduct.setModeVisual( false, true ); //Видимый, свернутый
                            }
                        }
                    }
                    // Сохраняем новый статус без перегрузки страницы (если надо будет вернуть статус)
                    sendListDatasAsync();
                } else {
                    if ( blockProduct.isMismatchAll() ) {
                        blockProduct.setStatusVisual( STATUS_BLOCK_MISMATCH_ALL );
                        blockProduct.setModeVisual( false, true ); //Видимый, свернутый
                    } else if ( blockProduct.isDeletedAll() ) {
                        blockProduct.setStatusVisual( STATUS_BLOCK_DELETE_ALL );
                        blockProduct.setModeVisual( false, true ); //Видимый, свернутый
                    } else if ( blockProduct.isSelectAll() ) {
                        blockProduct.setStatusVisual( STATUS_BLOCK_SELECT_ALL );
                        blockProduct.setModeVisual( false, true ); //Видимый, свернутый
                    } else {
                        blockProduct.setStatusVisual( STATUS_BLOCK_DEFAULT );
                        blockProduct.setModeVisual( false, false ); //Видимый, развернутый
                    }
                }
                //Изменим статистику
                statistic.changeUnit( event.detail.status_last, event.detail.data.status );
                break;
        }
        ;
    } );

    /**
     * Обработка нажания на "Сбросить" все фильтры.
     * Вместе с отправкой текущих выборов
     */
    $( 'body' ).on( 'click', CLASS_BUTTON_RESET_FILTERS, function ( e ) {
        e.stopPropagation();

        Ajax.send( "/product/reset-filters", { listDataForServer: listDataForServer }, ( response ) => {
            switch ( response.status ) {
                case 'ok':
                    let html = response.html_index_table;
                    var container = $( "#id_table_container" );
                    container.html( html );
                    //location.reload(); //Без этого подпупливает js и css в часности крестик выбора товара
                    break;
                case 'info':
                    alert( response.message );
                    break;
                case 'error':
                    alert( response.message );
                    break;
            }
            lib.slider_init();

            for ( var key in response.other ) {
                let elem = $( '#' + key );
                elem.html( response.other[key] );
            }
        } );
    } );

    // Расширенный фильтр
    $body.on( 'click', CLASS_BUTTON_ADDITIONAL_FILTERS, ( e ) => {
        e.preventDefault();
        const $this = $( e.target ),
            $filters = $this.siblings( '.additional-filters' );
        $filters.toggle();
        $this.find( 'i' ).toggleClass( 'bi-caret-down-fill bi-caret-up-fill' );
    } );

    /**
     * Нажатие на свернутом блоке товаров
     */
    $body.on( 'click', CLASS_BLOCK_PRODUCT_MIN, function ( e ) {
        e.stopPropagation();
        let pid = $( this ).data( 'pid' );
        let blockProduct = ProductBlock.getByPid( pid );

        blockProduct.setModeVisual( false, false );  //Видимый, развернутый
        blockProduct.dom.find( CLASS_PRODUCT_RIGHT ).show();
    } );

    /**
     * Нажатие на крестик в правом верхнем углу блока товара
     */
    $( document ).on( 'click', CLASS_BLOCK_BUTTON_CLOSE, function ( e ) {
        //$(CLASS_BLOCK_BUTTON_CLOSE).on('click', function (e) {
        e.stopPropagation();
        let blockProduct = ProductBlock.getFromChild( $( this ) );
        blockProduct.setModeVisual( false, true );    //Видимый, свернутый
    } );

    /**
     * Присваивание левому товару статуса STATUS_NOT_FOUND (левый крестик )
     */
    $body.on( 'click', CLASS_BUTTON_MISSMATCH_ALL, function ( e ) {
        e.stopPropagation();
        let $this = $( this );
        let data_button = $this.data();

        if ( Filters.getModeBatch() === true ) {
            let blockProduct = ProductBlock.getFromChild( $this );

            if ( !blockProduct.isMismatchAll( false ) ) {
                if ( !confirm( 'Некоторые правые товары именют статус отличный от missmatch и будет перезаписан. Продолжить?' ) ) {
                    return;
                }
            }

            let datas_items_right = Object.assign( [], blockProduct.getDatasRight() );
            datas_items_right.forEach( ( data ) => data.status = 'MISMATCH' );

            listDataForServer.addDatasRightList( datas_items_right );
        } else {
            $this.hide();
            Ajax.sendFromButton( data_button, onResponce );
        }

        function onResponce( response ) {
            switch ( response.status ) {
                case 'have_match':
                    let q = confirm( response.message );
                    if ( !q ) {
                        $this.show();
                        return;
                    }
                    let data = response.data;
                    data['confirm'] = true;
                    Ajax.sendFromButton( data, onResponce );
                    break;
                case 'ok':
                    let html = response.html_index_table;
                    if ( typeof ( html ) !== "undefined" && html !== null ) {
                        var container = $( "#id_table_container" );
                        container.html( html );
                        lib.slider_init();
                    }
                    break;
                case 'error':
                    alert( response.message );
                    break;
                default:
                    alert( 'Не удалось получить ожидаемый ответ от сервера' );
            }
        }
    } );

    /**
     * RED BTN
     */
    $( 'body' ).on( 'click', CLASS_BUTTON_RED, function ( e ) {
        e.stopPropagation();
        let $this = $( this );

        let productRigth = ProductRight.getFromChild( $this );
        let data = Object.assign( {}, productRigth.data );
        data.status = 'MISMATCH';

        if ( Filters.getModeBatch() === true ) {
            // Добавить товар в список для отправки
            listDataForServer.addDataRight( data );
        } else {
            $this.hide();
            Ajax.sendFromButton( data, ( response ) => {
                if ( response.status === 'ok' ) {
                    let html = response.html_index_table;
                    if ( typeof ( html ) !== "undefined" && html !== null ) {
                        var container = $( "#id_table_container" );
                        container.html( html );
                        lib.slider_init();
                    }
                } else
                    if ( response.status === 'error' ) {
                        alert( response.message );
                    }

                $this.show();
            } );
        }
        ;
    } );

    /**
     * YELLOW BTN
     */
    $( 'body' ).on( 'click', CLASS_BUTTON_YELLOY, function ( e ) {
        e.stopPropagation();
        let $this = $( this );
        let productRigth = ProductRight.getFromChild( $this );
        let data = Object.assign( {}, productRigth.data );
        data.status = 'PRE_MATCH';

        if ( Filters.getModeBatch() === true ) {
            // Добавить товар в список для отправки
            listDataForServer.addDataRight( data );
        } else {
            $this.hide();
            Ajax.sendFromButton( data, ( response ) => {
                if ( response.status === 'ok' ) {
                    let html = response.html_index_table;
                    if ( typeof ( html ) !== "undefined" && html !== null ) {
                        var container = $( "#id_table_container" );
                        container.html( html );
                        lib.slider_init();
                    }
                } else
                    if ( response.status === 'error' ) {
                        alert( response.message );
                    }

                $this.show();
            } );
        }
        ;
    } );

    /**
     * Кнопка отменить выбор на всех правых товарах, соответствующих одному левому
     */
    $body.on( 'click', '.js-reset-compare', function ( e ) {
        e.stopPropagation();

        let blockProduct = ProductBlock.getFromChild( $( this ) );
        listDataForServer.deleteIfExistsRightBy( blockProduct.data.source_id, blockProduct.data.pid );
    } );

    /**
     * Кнопка отменить выбор на всех правых товарах, на всей странице
     * Визуал меняется на событиях
     */
    $body.on( 'click', '.js-reset-compare-all-visible-items', function ( e ) {
        e.stopPropagation();

        listDataForServer.deleteRightAll();
    } );

    /**
     * Кнопка удалить. Нужно удалить только товары со статусом mismatch
     */
    $body.on( 'click', CLASS_BLOCK_BUTTON_DELETE, function ( e ) {
        e.stopPropagation();
        let blockProduct = ProductBlock.getFromChild( $( this ) );

        listDataForServer.findBy( blockProduct.data.pid, 'MISMATCH' ).forEach( ( data ) => {
            let data_new = Object.assign( {}, data );
            data_new.status = 'DELETED';
            listDataForServer.addDataRight( data_new );
        } );
    } );

    /**
     * Кнопка удалить все. Нужно удалить только товары со статусом mismatch
     */
    $body.on( 'click', CLASS_BLOCK_BUTTON_DELETE_ALL, function ( e ) {
        e.stopPropagation();
        listDataForServer.findAllBy( 'MISMATCH' ).forEach( ( data ) => {
            let data_new = Object.assign( {}, data );
            data_new.status = 'DELETED';
            listDataForServer.addDataRight( data_new );
        } );
    } );

    /**
     * Копка показать все которая внизу (она работает как переключаетель режима скрытия после выбора товара)
     */
    $( CLASS_BUTTON_SHOW_PRODUCTS_ALL ).on( 'click', function ( e ) {
        e.stopPropagation();
        changeModeHide( Filters.toggleModeHide() );
    } );

    /**
     * Инициазизация событий на изменение фильтров
     */
    addActionChangeFilter( 'id_f_asin', 'f_asin' );
    addActionChangeFilter( 'id_f_asin_multiple', 'f_asin_multiple' );
    addActionChangeFilter( 'id_f_categories_root', 'f_categories_root' );
    addActionChangeFilter( 'id_f_title', 'f_title' );
    addActionChangeFilter( 'id_f_status', 'f_status' );
    addActionChangeFilter( 'id_f_username', 'f_username' );
    addActionChangeFilter( 'id_f_comparison_status', 'f_comparison_status' );
    addActionChangeFilter( 'id_f_sort', 'f_sort' );
    addActionChangeFilter( 'id_f_count_products_on_page', 'f_count_products_on_page' );
    addActionChangeFilter( 'id_f_count_products_on_page_footer', 'f_count_products_on_page' );
    addActionChangeFilter( 'id_f_detail_view', 'f_detail_view' );
    addActionChangeFilter( 'id_f_profile', 'f_profile' );
    addActionChangeFilter( 'id_f_new', 'f_new' );
    addActionChangeFilter( 'id_f_favor', 'f_favor' );

    $( CLASS_ITEM_STAT ).on( 'click', ( e ) => {
        const $this = $( e.target ).closest( CLASS_ITEM_STAT );
        $this.siblings( `${CLASS_ITEM_STAT}.active` ).removeClass( 'active' );
        $this.addClass( 'active' );
    } );

    $( CLASS_ITEM_PRE_MATCH ).on( 'click', function ( e ) {
        e.stopPropagation();
        let blockProducts = ProductBlock.getFromChild( $( this ) );
        blockProducts.showProductsRight( 'pre_match' );
    } );

    $( CLASS_ITEM_MATCH ).on( 'click', function ( e ) {
        e.stopPropagation();
        let blockProducts = ProductBlock.getFromChild( $( this ) );
        blockProducts.showProductsRight( 'match' );
    } );

    $( CLASS_ITEM_MISMATCH ).on( 'click', function ( e ) {
        e.stopPropagation();
        let blockProducts = ProductBlock.getFromChild( $( this ) );
        blockProducts.showProductsRight( 'mismatch' );
    } );

    $( CLASS_ITEM_OTHER ).on( 'click', function ( e ) {
        e.stopPropagation();
        let blockProducts = ProductBlock.getFromChild( $( this ) );
        blockProducts.showProductsRight( 'other' );
    } );

    $( CLASS_ITEM_NOCOMPARE ).on( 'click', function ( e ) {
        e.stopPropagation();
        let blockProducts = ProductBlock.getFromChild( $( this ) );
        blockProducts.showProductsRight( 'nocompare' );
    } );

    /**
     * Обработка нажатия на пагинатор
     * Если есть данные выбора, то предварительно отправляем на сервер
     */
    $( '#id_paginator a' ).click( async function ( e ) {
        e.preventDefault();
        if ( await sendListDatasAsync() ) {
            // Нужно сбросить выборы, ибо как потом выполнится еще и window.onbeforeunload
            listDataForServer.reset();
            location.href = $( this ).attr( 'href' );
        }
    } );

    /**
     * На этот момент в массивах данных быть не должно. Данные будут если предыдущие отправки завершились не удачно
     * Отправку данных выполнить тут очень не надежно и не всегда работает на разных браузерах.
     *
     * Если в массиве остальсь данные то выводим предупреждение
     * @returns {undefined}
     */
    window.onbeforeunload = function () {
        if ( listDataForServer.isHasData() ) {
            return false;
        }
    };

    /**************************************************************************
     *** Вспомогательные функции
     **************************************************************************/

    /**
     * Отправка на сервер нового значения фильтра
     * При успешном ответе происходит обновление списка и инициализация слайдера на котором отображены правые товары
     *
     * @param {string} id_filter    id фильтра
     * @param {string} name_filter  имя фильтра
     * @returns {undefined}
     */
    function addActionChangeFilter( id_filter, name_filter ) {
        let filter = $( '#' + id_filter );
        filter.on( 'change', function ( e ) {
            //$(document).on('change', '#'+id_filter, function(e){
            e.stopPropagation();
            let value;
            switch ( id_filter ) {
                case 'id_f_asin_multiple': value = filter.val().trim(); break;
                case 'id_f_new': value = filter.prop( 'checked' ) ? 1 : 0; break;
                case 'id_f_favor': value = filter.prop( 'checked' ) ? 1 : 0; break;
                //case 'id_f_batch_mode': value = +new Filters().getModeBatch(); break;
                //case 'id_f_hide_mode': value = +new Filters().getModeHide(); break;
                default:
                    value = filter.val();
            }

            let data = {
                'name': name_filter,
                'value': value,
                'data_comparisons': listDataForServer
            };

            Ajax.send( "/product/change-filter", data, ( response ) => {
                switch ( response.status ) {
                    case 'ok':
                        if ( id_filter === 'id_f_hide_mode' ) {
                            changeModeHide( value );
                        } else {
                            let html = response.html_index_table;
                            var container = $( "#id_table_container" );
                            container.html( html );
                            //lib.slider_init();
                            location.reload(); //Без этого подпупливает js и css в часности крестик выбора товара
                        }
                        if ( response.is_compare_all === false ) {
                            alert( 'Не все сравнения удалось сохранить' );
                        }
                        break;
                    case 'info':
                        alert( response.message );
                        break;
                    case 'error':
                        alert( response.message );
                        break;
                }
                //lib.slider_destroy();
                lib.slider_init();

                for ( var key in response.other ) {
                    let elem = $( '#' + key );
                    elem.html( response.other[key] );
                }
            } );
        } );
    }

    /**
     * Для всех товаров сменить режим отображения
     *
     * @param {type} is_mode_hide
     * @returns {undefined}
     */
    function changeModeHide( is_mode_hide ) {

        if ( is_mode_hide === false ) {
            // Отображаем все
            $( CLASS_BLOCK_PRODUCT ).show();
            $( CLASS_BLOCK_PRODUCT_MIN ).hide();
            $( CLASS_PRODUCT_RIGHT ).show();
        } else {
            let filter_status = $( '#id_f_comparison_status' ).val()


            // Скрываем все выбранные
            for ( let data of listDataForServer.datas_products_right ) {
                let product_right = ProductRight.getBy( data.id_source, data.id_item );
                if ( filter_status === "MISMATCH" || filter_status === "PRE_MATCH" ) {
                    if ( data.status !== filter_status ) {
                        product_right.dom.hide();
                    }
                } else {
                    product_right.dom.hide();
                }


                let blockProduct = ProductBlock.getByPid( product_right.data.id_product );
                if ( blockProduct.isMismatchAll() && filter_status !== 'MISMATCH' ) {
                    blockProduct.setModeVisual( false, true );    // Видимый, свернутый
                } else if ( blockProduct.isDeletedAll() ) {
                    blockProduct.setModeVisual( false, true );    // Видимый, свернутый
                } else if ( blockProduct.isSelectAll() ) {
                    if ( filter_status === "MISMATCH" || filter_status === "PRE_MATCH" ) {
                        if ( ( filter_status === "MISMATCH" && blockProduct.isPrematchAll() ) || filter_status === "PRE_MATCH" && blockProduct.isMismatchAll() ) {
                            blockProduct.setModeVisual( false, true );    // Видимый, свернутый
                        }
                    } else {
                        blockProduct.setModeVisual( false, true );    // Видимый, свернутый
                    }
                }
            }
        }
    }

    /**
     * Асинхроммо отсылаем данные пакетного сравнения товаров на сервер. Вызывается перед переходом на другую страницу
     * В случае ошибки или предупреджение со стороны сервера будет выведено предупреждение.
     *
     * @returns {boolean}
     *    true - подтверждено дадьшейшее действие
     *    false - отмена дальнейшкнр лействия
     */
    async function sendListDatasAsync() {
        //Если данных нет то и отсылать не нужно
        if ( !listDataForServer.datas_products_right.length )
            return true;

        try {
            let response = await Ajax.sendAsync( '/product/compare-batch', { listDataForServer: listDataForServer } );

            switch ( response.status ) {
                case 'ok':
                    return true;
                case 'info':
                    confirm( response.message + '. Продолжить?' );
                case 'error':
                    return confirm( response.message + '. Продолжить?' );
                default:
                    // Случай ошибки ajax
                    return confirm( response + '. Продолжить?' );
            }
        } catch ( e ) {
            return confirm( e + ' Продолжить?' );
        }
    }

    /**
     * Проверить, все ли правые товары выбраны. Включет в себя выбор изначальный + выборы в массивах.
     * Если все правые товары выбраны - отлылаем их на сервер с предупреждением что товары закончились
     *
     * @param {type} is_mode_hide
     * @returns {undefined}
     */
    function checkAndSendAllStatuses( is_mode_hide ) {
        // Есть ли на странице color_marker = 'nocompare'. Есль есть то значит есть и продукт с неотмеченным статусом
        if ( $( '.color-marker.nocompare' ).length ) return;

        // if (confirm('Товары без статусов закончились. Даные сохранятся и страница будет перезагружена')) {
        // Если все условия выполнены то отправляем данные товаров на сервер
        sendListDatasAsync().then( function ( is_confirm ) {
            if ( !is_confirm ) {
                return;
            }
            listDataForServer.reset();
            location.reload();
        } );
        $( '#preloader' ).show();

        // } else {
        //     if (Filters.getModeHide()) {
        //         changeModeHide(Filters.toggleModeHide());
        //     }
        // }
    }

    /**************************************************************************
     *** Старый шлак
     **************************************************************************/

    /**
     * Скроллинг
     */
    $( 'body' ).on( 'scroll', function () {
        let block = $( '.products__products-list' )[0].getBoundingClientRect();
        let height = $( '.products__filter-items' ).height() + $( '#w0' ).height();

        if ( block.top < height ) {
            // -hidden  REMOVE
            $( '.navbar__fixed-slider.-hidden' ).removeClass( '-hidden' );

            if ( $( '.position-1 .products__filter-items' ).length ) {
                $( '.position-2' ).append( $( '.position-1 .products__filter-items' ) );
            }
            $( '.position-2' ).parents( '.navbar__fixed-slider' ).css( 'display', 'block' );

            let height = $( '.navbar__fixed-slider' ).height() + 35 + $( '#w0' ).height();
            $( 'section.home' ).css( 'padding-top', height ); // 262px

            $( '.navigation' ).hide();
            $( '.js-title-and-source_selector' ).hide();
        } else {
            $( '.navbar__fixed-slider' ).addClass( '-hidden' );
            if ( $( '.position-2 .products__filter-items' ).length ) {
                $( '.position-1' ).append( $( '.position-2 .products__filter-items' ) );
            }
            $( 'section.home' ).css( 'padding-top', '' );
            $( '.position-2' ).parents( '.navbar__fixed-slider' ).css( 'display', '' );
            $( '.navigation' ).show();
            $( '.js-title-and-source_selector' ).show();

        }
    } );

    $( 'body' ).on( 'change', CLASS_CHECKBOX_EXPORT_FILTERED, ( e ) => {
        const $this = $( e.target ),
            $exportLink = $this.parent().siblings( '.product-list-item__export' );
        if ( $this.prop( 'checked' ) ) {
            $exportLink.attr( 'href', `${$exportLink.attr( 'href' )}&filtered=true` );
        } else {
            $exportLink.attr( 'href', `${$exportLink.attr( 'href' ).replace( '&filtered=true', '' )}` );
        }
    } );

    $( 'body' ).on( 'click', CLASS_BUTTON_EDIT_PROFILE, ( e ) => {
        const $btn = $( e.target ),
            $profile = $btn.siblings( '.product-list-item__profile' );
        $profile.attr( 'contenteditable', $profile.attr( 'contenteditable' ) !== 'true' );

        if ($profile.attr( 'contenteditable' ) === 'true') {
            $profile.focus();
        }
    } );

    $( 'body' ).on( 'keypress', CLASS_ELEMENT_PROFILE, ( e ) => {
        if ( e.keyCode === 13 ) {
            e.preventDefault();
            $( e.target ).blur();
        }
    } );

    $( 'body' ).on( 'blur', CLASS_ELEMENT_PROFILE, ( e ) => {
        const $this = $( e.target );
        $this.attr('contenteditable', 'false');

        if ($this.text().trim() === $this.data('value')) {
            return;
        }

        Ajax.send(
            "/product/change-profile",
            {
                pid: $this.data( 'pid' ),
                value: $this.text().trim(),
                source_id: $this.data('source-id'),
            },
            ( res ) => {
                $this.attr('data-value', res.value);
            }
        );
    } );

    $(' body').on('click', CLASS_BUTTON_PRODUCT_FAVOR, (e) => {
        const $this = $(e.target),
            $parent = $this.closest('.products-list__img-wrapper');

            Ajax.send(
                "/product/toggle-product-favor",
                {
                    product_id: $parent.data('id_product'),
                    source_id: $parent.data('id_source'),
                },
                ( res ) => {
                    if (res.favored) {
                        $this.removeClass('bi-star').addClass('bi-star-fill favored');
                    } else {
                        $this.removeClass('bi-star-fill favored').addClass('bi-star');
                    }
                }
            );    
    });
}
document.addEventListener( "DOMContentLoaded", main );
