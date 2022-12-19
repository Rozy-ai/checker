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
    CLASS_BLOCK_BUTTON_CLOSE,
    CLASS_BLOCK_PRODUCT,
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
    ListDataForServer
} from './classes/ListDataForServer.js';

const CLASS_BUTTON_MISSMATCH_ALL        = '.product-list__item-mismatch-all'; // Левый крестик
const CLASS_BUTTON_DELETE_PRODUCT_RIGTH = '.js-del-item';

function main(){
    //let filters = new Filters();
    let listDataForServer = new ListDataForServer();
    let $body = $('body');
    
    $body.on('click', CLASS_BLOCK_PRODUCT_MIN, function (e) {
        e.stopPropagation();
        let blockProductRight = new ProductBlock($(this));
        blockProductRight.maximize();
    });
    
    /**
     * Присваивание левому товару статуса STATUS_NOT_FOUND (левый крестик )
     */
    $body.on('click', CLASS_BUTTON_MISSMATCH_ALL, function (e) {
        e.stopPropagation();
        let $this = $(this);
        let data_button = $this.data();
        let filters = new Filters();
        let is_mode_hide = filters.getModeHide();         

        if (filters.getModeBatch() === true ){
            let productBlock = ProductBlock.getFromChild($this);
            let productLeft = productBlock.getProductLeft();
            
            if (productBlock.isHaveStatusProductsRight()){
                let q = confirm('Некоторые правые товары именют статус отличный от missmatch и будет перезаписан. Продолжить?');
                if (!q) {
                    $this.show();
                    return;
                }
            }
            
            listDataForServer.addLeft(productLeft.data);
            productBlock.changeVisual(true, is_mode_hide);
            productLeft.changeVisual(true, is_mode_hide);
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
        let filters = new Filters();
        let $this = $(this);
        let data  = $this.data();
        
        if ( filters.getModeBatch() === true) {
            let $productRigth = ProductRight.getFromChild($this, data);
            // Добавить товар в список для отправки
            if (listDataForServer.addRight($productRigth.data)){
                // Если товар оказался новым то нужно обновить статистику
                let $productBlock = ProductBlock.getFromChild($productRigth.dom);
                let $statistic = Statistic.getFirstFromParent($productBlock.dom);
                $statistic.addUnit('mismatch');
            } 
            $productRigth.changeVisual('mismatch', filters.getModeHide());
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
     * YELLOW
     */
    $('body').on('click', CLASS_BUTTON_YELLOY, function (e) {
        e.stopPropagation();
        let filters = new Filters();
        let $this = $(this);
        let data  = $this.data();
        
        if ( filters.getModeBatch() === true) {
            let $productRigth = ProductRight.getFromChild($this, data);
            // Добавить товар в список для отправки
            if (listDataForServer.addRight($productRigth.data)){
                // Если товар оказался новым то нужно обновить статистику
                let $productBlock = ProductBlock.getFromChild($productRigth.dom);
                let $statistic = Statistic.getFirstFromParent($productBlock.dom);
                $statistic.addUnit('pre_match');
            }
            $productRigth.changeVisual('pre_match', filters.getModeHide());
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
     * Кнопка отменить выбор на всех правых товарах, сообветствующих одному левому
     */
    $body.on('click', '.js-reset-compare', function (e) {
        e.stopPropagation();

        let filters = new Filters();
        let is_mode_hide        = filters.getModeHide();
        
        let productBlock        = ProductBlock.getFromChild($(this));
        let list_product_right  = productBlock.getProductsRight();      //Масив всех правых продуктов (без статусов)
        let product_left        = productBlock.getProductLeft();
        let id_product          = product_left.data.id_product;
        let statistic           = productBlock.getStatistic();
        
        //Убираем все сравнения что есть с данным id левого товара
        //Идем от масива к визуалу, т.к. отсутствие выбора в массиве важнее
        listDataForServer.datas_products_right = listDataForServer.datas_products_right.reduce(function(previousValue, currentItem){
            
            if (currentItem.id_product !== id_product){
                previousValue.push(currentItem);
            }
            // Отмечаем соответственный правый товар визуально
            list_product_right.find(productRight => {
                let data = productRight.data;
                if (data.id_source  === currentItem.id_source  &&
                    data.id_product === currentItem.id_product &&
                    data.id_item    === currentItem.id_item)
                {
                    productRight.changeVisual('', is_mode_hide);
                }
            });

            return previousValue;
        }, []);
        
        //Формируем новый массив выбранных левых товаров из всех кромне того что имеет id = id_product
        listDataForServer.datas_products_left = listDataForServer.datas_products_left.reduce(function(previousValue, currentItem){
            if (currentItem.id_product === id_product){
                product_left.changeVisual(false, is_mode_hide);
            } else {
                previousValue.push(currentItem);
            }
            return previousValue;
        }, []);
        
        // Сброс статистики
        statistic.reset();
    });
    
    /**
     * Кнопка отменить выбор на всех правых товарах, на всей странице
     * Тут все просто. Чистим список выбора. И отмечаем товар визуально как был (тоесть свойство item.status)
     */
    $body.on('click', '.js-reset-compare-all-visible-items', function (e) {
        e.stopPropagation();
        let filters = new Filters();
        let is_mode_hide        = filters.getModeHide();
        
        // Сначала обработаем все блоки товаров
        $(CLASS_BLOCK_PRODUCT).each(function(index){
            let product_block = new ProductBlock($(this));
            product_block.changeVisual(false, is_mode_hide);
        });
        
        // Теперь обработаеи все левые товары
        $(CLASS_PRODUCT_LEFT).each(function(index){
            let product_left = new ProductLeft($(this));
            product_left.changeVisual(false, is_mode_hide);
        });
        
        // Изменим визуальный статус правых товаров к изначальному виду
        $(CLASS_PRODUCT_RIGHT).each(function(index){
            let productRight = new ProductRight($(this));
            let status = productRight.data.status;
            productRight.changeVisual(status, is_mode_hide);
        });
        
        //Сбрасываем все блоки статистики
        $(CLASS_STATISTIC).each(function(index){
            let statistic = new Statistic($(this));
            statistic.reset();
        });
        
        
        // Очистить массивов выбора
        listDataForServer.datas_products_right.length = 0;
        listDataForServer.datas_products_left.length = 0;
    });
    
    /**
     * Нажатие на крестик в правом верхнем углу блока товара
     */
    $(CLASS_BLOCK_BUTTON_CLOSE).on('click', function (e) {
        e.stopPropagation();
        let $this = $(this);
        let blockProduct = ProductBlock.getFromChild($this);
        
        if (Filters.getModeMinimize()){
            blockProduct.minimize();
        }else {
            blockProduct.dom.hide();
        }
    });
    
    /**
     * Переключатель режима отображения
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
    addActionChangeFilter('id_f_profile_type', 'f_profile_type');
    addActionChangeFilter('id_f_username', 'f_username');
    addActionChangeFilter('id_f_comparison_status', 'f_comparison_status');
    addActionChangeFilter('id_f_sort', 'f_sort');
    addActionChangeFilter('id_f_count_products_on_page', 'f_count_products_on_page');
    addActionChangeFilter('id_f_detail_view', 'f_detail_view');
    addActionChangeFilter('id_f_profile', 'f_profile');
    
    $(CLASS_ITEM_PRE_MATCH).on('click', function (e) {
        e.stopPropagation();
        let $parent = $(this).parents('.product-list__product-list-item');
        showProductsRight($parent ,'pre_match');
    });
    
    $(CLASS_ITEM_MATCH).on('click', function (e) {
        e.stopPropagation();
        let $parent = $(this).parents('.product-list__product-list-item');
        showProductsRight($parent, 'match');
    });
    
    $(CLASS_ITEM_MISMATCH).on('click', function (e) {
        e.stopPropagation();
        let $parent = $(this).parents('.product-list__product-list-item');
        showProductsRight($parent, 'mismatch');
    });
    
    $(CLASS_ITEM_OTHER).on('click', function (e) {
        e.stopPropagation();
        let $parent = $(this).parents('.product-list__product-list-item');
        showProductsRight($parent, 'other');
    });

    $(CLASS_ITEM_NOCOMPARE).on('click', function (e) {
        e.stopPropagation();
        let $parent = $(this).parents('.product-list__product-list-item');
        showProductsRight($parent, 'nocompare');
    });
    
    /**************************************************************************
     *** Вспомогательные функции
     **************************************************************************/
    
    /**
     * Оставить правые товары видимыми в блоке только отмеченные данным классом
     * @param {object}    $block_items
     * @param {string} class_marker
     * @returns {undefined}
     */
    function showProductsRight ($block_items, class_marker) {
        let $items = $block_items.find('.slider__slider-item');
        
        $items.each(function(index, item){
            let $item = $(item);
            let $marker = $item.find('.color-marker');
            
            if ($marker.hasClass(class_marker)) {
                $item.show();
            } else {
                $item.hide();
            }
        });
    };
    
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
            
            Ajax.send("/products/change-filter", data, (response) => {
                switch (response.status) {
                    case 'ok':
                        if (id_filter === 'id_f_hide_mode'){
                            changeModeHide(value);
                        }else{
                            let html = response.html_index_table;
                            var container = $("#id_table_container");
                            container.html(html);
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
        // Покажем на экране все блоки
        //$(CLASS_BLOCK_PRODUCT).each(function(index){
        //    let product_block = new ProductBlock($(this));
        //    product_block.changeVisual(true, is_hide_mode());
        //});
        
        // Пробегаемся по списку выбранных левых товаров и отображаем их "по-другому"
        for(let data of listDataForServer.datas_products_left){
            let product_left = ProductLeft.getBy(data.id_source, data.id_product);
            let product_block = ProductBlock.getFromChild(product_left.dom);
            product_block.changeVisual(true, is_mode_hide); //true - значит показать
            product_left.changeVisual(true, is_mode_hide);
        };
        
        // Пробегаемся по списку выбранных правых товаров и отображаем их "по-другому"
        for (let data of listDataForServer.datas_products_right){
            let product_right = ProductRight.getBy(data.id_source, data.id_item);
            product_right.changeVisual(data.status.toLowerCase(), is_mode_hide);
        };
    }
}
document.addEventListener("DOMContentLoaded", main);