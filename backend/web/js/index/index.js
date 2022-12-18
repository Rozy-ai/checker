'use strict';

import {
    Ajax
} from './classes/Ajax.js'

import {
    CLASS_BUTTON_SHOW_PRODUCTS_ALL,
    Filters
} from './classes/Filters.js'

import {
    CLASS_BLOCK_PRODUCT_MIN,
    CLASS_BLOCK_BUTTON_DELETE,
    CLASS_BLOCK_BUTTON_CLOSE,
    CLASS_BLOCK_PRODUCT,
    STATUS_DELETED,
    STATUS_MISMATCH_ALL,
    STATUS_NOT_FOUND,
    STATUS_DEFAULT,
    ProductBlock
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
    Statistic
} from './classes/Statistic.js'

import {
    CLASS_PRODUCT_RIGHT,
    CLASS_BUTTON_RED,
    CLASS_BUTTON_YELLOY,
    ProductRight
} from './classes/ProductRight.js'

import {
    EVENT_CHANGE_DATA_LEFT,
    EVENT_CHANGE_DATA_RIGHT,
    EVENT_CHANGE_DATA_DELETE,   
    ACTION_DATA_CREATE,
    ACTION_DATA_CHANGE,
    ACTION_DATA_DELETE,
    ListDataForServer
} from './classes/ListDataForServer.js';

const CLASS_BUTTON_MISSMATCH_ALL        = '.product-list__item-mismatch-all'; // Левый крестик

function main(){
    let listDataForServer = new ListDataForServer();
    let $body = $('body');
    
    /*
     * Событие смены статуса правого товара (Сработает только в пакетном режиме)
     */
    document.addEventListener(EVENT_CHANGE_DATA_RIGHT, function(event) {
        let productRight = ProductRight.getBy(event.detail.data.id_source, event.detail.data.id_item);
        let blockProduct = ProductBlock.getFromChild(productRight.dom);
        let statistic    = Statistic.getFirstFromParent(blockProduct.dom);        
        
        if (event.detail.action === ACTION_DATA_DELETE){
            statistic.deleteUnit(event.detail.data.status);
            productRight.changeVisual(productRight.data.status, Filters.getModeHide()); //Тут первоначальный статус
            blockProduct.changeVisual(STATUS_DEFAULT, false);
        }
        
        if (event.detail.action === ACTION_DATA_CREATE){
            statistic.addUnit(event.detail.data.status);
            productRight.changeVisual(event.detail.data.status, Filters.getModeHide());
            // Eсли у всех правых товаров получился статус missmatch то левый должен автоматически стать mismatch
            if (blockProduct.isHasStatusNotMismatch() === false){
                let productLeft = blockProduct.getProductLeft();
                let data = Object.assign({}, productLeft.data);
                listDataForServer.addLeft(data);
            } else if (!blockProduct.isHasProductRightWithoutColormarker()){
                blockProduct.changeVisual(STATUS_NOT_FOUND, Filters.getModeHide());
            }
        }
        
        if (event.detail.action === ACTION_DATA_CHANGE){
            statistic.changeUnit(event.detail.statuslast, event.detail.data.status);
            productRight.changeVisual(event.detail.data.status, Filters.getModeHide());
            
        }
    });
    
    /*
     * Событие смены статуса левого товара (Левый крестик. Сработает только в пакетном режиме)
     */
    document.addEventListener(EVENT_CHANGE_DATA_LEFT, function(event) {
        let productLeft  = ProductLeft.getBy(event.detail.data.id_source, event.detail.data.id_product);
        let blockProduct = ProductBlock.getFromChild(productLeft.dom);
        let is_mode_hide = Filters.getModeHide();
        
        if (event.detail.action === ACTION_DATA_CREATE){
            // Тут нужно чтобы все правые товары стали тоже missmatch
            let productsRight = blockProduct.getProductsRight();
            for (let item of productsRight){
                let data = Object.assign({}, item.data);
                data.status = 'MISMATCH';
                listDataForServer.addRight(data);
            }
            
            blockProduct.changeVisual(STATUS_MISMATCH_ALL, is_mode_hide, Filters.getModeMinimize());
            productLeft.changeVisual(true, is_mode_hide);
        } else if (event.detail.action === ACTION_DATA_DELETE){
            blockProduct.changeVisual(STATUS_DEFAULT, is_mode_hide, Filters.getModeMinimize());
            productLeft.changeVisual(false, is_mode_hide);            
        }
    });
    
    document.addEventListener(EVENT_CHANGE_DATA_DELETE, function(event) {
        let productLeft  = ProductLeft.getBy(event.detail.data.id_source, event.detail.data.id_product);
        let blockProduct = ProductBlock.getFromChild(productLeft.dom);
        let is_mode_hide = Filters.getModeHide();
        
        if (event.detail.action === ACTION_DATA_CREATE){
            blockProduct.changeVisual(STATUS_DELETED, is_mode_hide, false);
            productLeft.changeVisual(false, is_mode_hide);
        } else if (event.detail.action === ACTION_DATA_DELETE){
            blockProduct.changeVisual(STATUS_DEFAULT, is_mode_hide, Filters.getModeMinimize());
        }
    });
    
    /**
     * Нажатие на свернутом блоке товаров
     */
    $body.on('click', CLASS_BLOCK_PRODUCT_MIN, function (e) {
        e.stopPropagation();
        let pid = $(this).data('pid');
        let blockProduct = ProductBlock.getByPid(pid);
        blockProduct.changeVisual('', false, false); // Статус не меняем. Просто не свернутый
    });
    
    /**
     * Нажатие на крестик в правом верхнем углу блока товара
     */
    $(document).on('click', CLASS_BLOCK_BUTTON_CLOSE, function(e){
    //$(CLASS_BLOCK_BUTTON_CLOSE).on('click', function (e) {
        e.stopPropagation();
        let blockProduct = ProductBlock.getFromChild($(this));
        blockProduct.changeVisual('', false, true); // Статус не меняем. Просто сворачиваем
    });
    
    /**
     * Присваивание левому товару статуса STATUS_NOT_FOUND (левый крестик )
     */
    $body.on('click', CLASS_BUTTON_MISSMATCH_ALL, function (e) {
        e.stopPropagation();
        let $this = $(this);
        let data_button = $this.data();         

        if (Filters.getModeBatch() === true ){
            let blockProduct = ProductBlock.getFromChild($this);
            let productLeft = blockProduct.getProductLeft();
            
            if (blockProduct.isHasStatusNotMismatch()){
                let q = confirm('Некоторые правые товары именют статус отличный от missmatch и будет перезаписан. Продолжить?');
                if (!q) {
                    $this.show();
                    return;
                }
            }
            
            listDataForServer.addLeft(productLeft.data);
        } else {
            $this.hide();
            Ajax.sendFromButton(data_button, onResponce);
        }
        
        function onResponce(response) {
            switch (response.status) {
                case 'have_match':
                    let q = confirm(response.message);
                    if (!q) {
                        $this.show();
                        return;
                    }
                    let data = response.data;
                    data['confirm'] = true;
                    Ajax.sendFromButton(data, onResponce);
                    break;
                case 'ok':
                    let html = response.html_index_table;
                    if (typeof (html) !== "undefined" && html !== null) {
                        var container = $("#id_table_container");
                        container.html(html);
                        lib.slider_init();
                    }
                    break;
                case 'error':
                    alert(response.message);
                    break;
                default:
                    alert('Не удалось получить ожидаемый ответ от сервера');
            }
        }
    });
    
    /**
     * RED BTN
     */
    $('body').on('click', CLASS_BUTTON_RED, function (e) {
        e.stopPropagation();
        let $this = $(this);
        let data  = $this.data();
        data.status = 'MISMATCH';
        
        if ( Filters.getModeBatch() === true) {
            // Добавить товар в список для отправки
           listDataForServer.addRight(data);
        } else {
            $this.hide();
            Ajax.sendFromButton(data, (response) => {
                if (response.status === 'ok') {
                    let html = response.html_index_table;
                    if (typeof (html) !== "undefined" && html !== null) {
                        var container = $("#id_table_container");
                        container.html(html);
                        lib.slider_init();
                    }
                } else
                if (response.status === 'error') {
                    alert(response.message);
                }

                $this.show();
            });
        };
    });
    
    /**
     * YELLOW BTN
     */
    $('body').on('click', CLASS_BUTTON_YELLOY, function (e) {
        e.stopPropagation();
        let $this = $(this);
        let productRigth = ProductRight.getFromChild($this);
        let data  = Object.assign({}, productRigth.data);
        data.status = 'PRE_MATCH';
        
        if ( Filters.getModeBatch() === true) {
            // Добавить товар в список для отправки
            listDataForServer.addRight(data);
        } else {
            $this.hide();
            Ajax.sendFromButton(data, (response) => {
                if (response.status === 'ok') {
                    let html = response.html_index_table;
                    if (typeof (html) !== "undefined" && html !== null) {
                        var container = $("#id_table_container");
                        container.html(html);
                        lib.slider_init();
                    }
                } else
                if (response.status === 'error') {
                    alert(response.message);
                }

                $this.show();
            });
        };
    });
    
    /**
     * Кнопка отменить выбор на всех правых товарах, соответствующих одному левому
     */
    $body.on('click', '.js-reset-compare', function (e) {
        e.stopPropagation();
        
        let blockProduct        = ProductBlock.getFromChild($(this));
        
        for (let i = listDataForServer.datas_products_right.length - 1; i >= 0; --i) {
            let data = listDataForServer.datas_products_right[i];
            if (data.id_source  === blockProduct.data.source_id  &&
                data.id_product === blockProduct.data.pid) {
                listDataForServer.deleteRightByIndex(i);
            }               
        }
        
        for (let i = listDataForServer.datas_products_left.length - 1; i >= 0; --i) {
            let data = listDataForServer.datas_products_left[i];
            if (data.id_source  === blockProduct.data.source_id  &&
                data.id_product === blockProduct.data.pid) {
                listDataForServer.deleteLeftByIndex(i);
            }
        }
        
        for (let i = listDataForServer.datas_products_left_delete.length - 1; i >= 0; --i) {
            let data = listDataForServer.datas_products_left_delete[i];
            if (data.id_source  === blockProduct.data.source_id  &&
                data.id_product === blockProduct.data.pid) {
                listDataForServer.deleteDeleteByIndex(i);
            }
        }
    });
    
    /**
     * Кнопка отменить выбор на всех правых товарах, на всей странице
     * Тут все просто. Чистим список выбора. И отмечаем товар визуально как был (тоесть свойство item.status)
     */
    $body.on('click', '.js-reset-compare-all-visible-items', function (e) {
        e.stopPropagation();
        
        for (let i = listDataForServer.datas_products_right.length - 1; i >= 0; --i) {
            listDataForServer.deleteRightByIndex(i);
        }
        
        for (let i = listDataForServer.datas_products_left.length - 1; i >= 0; --i) {
            listDataForServer.deleteLeftByIndex(i);
        }
        
        for (let i = listDataForServer.datas_products_left_delete.length - 1; i >= 0; --i) {
            listDataForServer.deleteDeleteByIndex(i);
        }
    });
    
    /**
     * Кнопка удалить товар
     */
    $body.on('click', CLASS_BLOCK_BUTTON_DELETE, function (e) {
        e.stopPropagation();
        let blockProduct = ProductBlock.getFromChild($(this));
        let productLeft = blockProduct.getProductLeft();
        listDataForServer.addDelete(productLeft.data);
    });
    
    /**
     * Копка показать все которая внизу (она работает как переключаетель режима скрытия после выбора товара)
     */
    $(CLASS_BUTTON_SHOW_PRODUCTS_ALL).on('click', function (e) {
        e.stopPropagation();
        changeModeHide(Filters.toggleModeHide());      
    });
    
    /**
     * Инициазизация событий на изменение фильтров
     */
    addActionChangeFilter('id_f_asin', 'f_asin');
    addActionChangeFilter('id_f_categories_root', 'f_categories_root');
    addActionChangeFilter('id_f_title', 'f_title');
    addActionChangeFilter('id_f_status', 'f_status');
    addActionChangeFilter('id_f_username', 'f_username');
    addActionChangeFilter('id_f_comparison_status', 'f_comparison_status');
    addActionChangeFilter('id_f_sort', 'f_sort');
    addActionChangeFilter('id_f_count_products_on_page', 'f_count_products_on_page');
    addActionChangeFilter('id_f_detail_view', 'f_detail_view');
    addActionChangeFilter('id_f_profile', 'f_profile');
    
    $(CLASS_ITEM_PRE_MATCH).on('click', function (e) {
        e.stopPropagation();
        let blockProducts = ProductBlock.getFromChild($(this));
        blockProducts.showProductsRight('pre_match');
    });
    
    $(CLASS_ITEM_MATCH).on('click', function (e) {
        e.stopPropagation();
        let blockProducts = ProductBlock.getFromChild($(this));
        blockProducts.showProductsRight('match');
    });
    
    $(CLASS_ITEM_MISMATCH).on('click', function (e) {
        e.stopPropagation();
        let blockProducts = ProductBlock.getFromChild($(this));
        blockProducts.showProductsRight('mismatch');
    });
    
    $(CLASS_ITEM_OTHER).on('click', function (e) {
        e.stopPropagation();
        let blockProducts = ProductBlock.getFromChild($(this));
        blockProducts.showProductsRight('other');        
    });

    $(CLASS_ITEM_NOCOMPARE).on('click', function (e) {
        e.stopPropagation();
        let blockProducts = ProductBlock.getFromChild($(this));
        blockProducts.showProductsRight('nocompare');
    });
    
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
    function addActionChangeFilter(id_filter, name_filter) {
        let filter = $('#' + id_filter);
        filter.on('change', function (e) {
        //$(document).on('change', '#'+id_filter, function(e){
            e.stopPropagation();
            let value;
            switch (id_filter){
                //case 'id_f_batch_mode': value = +new Filters().getModeBatch(); break;
                //case 'id_f_hide_mode': value = +new Filters().getModeHide(); break;
                default: value = filter.val();
            }
                
            let data = {
                'name': name_filter,
                'value': value,
                'data_comparisons':listDataForServer
            };
            
            Ajax.send("/product/change-filter", data, (response) => {
                switch (response.status) {
                    case 'ok':
                        if (id_filter === 'id_f_hide_mode'){
                            changeModeHide(value);
                        }else{
                            let html = response.html_index_table;
                            var container = $("#id_table_container");
                            container.html(html);
                            //lib.slider_init();
                            location.reload(); //Без этого подпупливает js и css в часности крестик выбора товара
                        }
                        if (response.is_compare_all === false){
                            alert('Не все сравнения удалось сохранить');
                        }
                        break;
                    case 'info':
                        alert(response.message);
                        break;
                    case 'error':
                        alert(response.message);
                        break;
                }
                //lib.slider_destroy();
                lib.slider_init();

                for (var key in response.other) {
                    let elem = $('#' + key);
                    elem.html(response.other[key]);
                }
            });
        });
    }
    
    /**
     * Для всех товаров сменить режим отображения
     * 
     * @param {type} is_mode_hide
     * @returns {undefined}
     */
    function changeModeHide(is_mode_hide){
        let is_minimize = Filters.getModeMinimize();
        
        if (is_mode_hide === false){
            $(CLASS_BLOCK_PRODUCT).show();
        }

        // Пробегаемся по списку удаленных товаров и отображаем их "по-другому"
        for(let data of listDataForServer.datas_products_left_delete){
            let product_block = ProductBlock.getByPid(data.id_product);
            product_block.changeVisual(STATUS_DELETED, is_mode_hide, is_minimize);
        };
        
        // Пробегаемся по списку выбранных левых товаров и отображаем их "по-другому"
        for(let data of listDataForServer.datas_products_left){
            let product_left = ProductLeft.getBy(data.id_source, data.id_product);
            let product_block = ProductBlock.getFromChild(product_left.dom);
            product_block.changeVisual('', is_mode_hide, is_minimize);
            product_left.changeVisual(true, is_mode_hide);
        };
        
        // Пробегаемся по списку выбранных правых товаров и отображаем их "по-другому"
        for (let data of listDataForServer.datas_products_right){
            let product_right = ProductRight.getBy(data.id_source, data.id_item);
            product_right.changeVisual(data.status, is_mode_hide);
        };
    }
}
document.addEventListener("DOMContentLoaded", main);