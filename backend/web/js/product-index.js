$(document).ready(function () {
    let $body = $('body');
    let datas_product_right = [];
    //let datas_product_left = [];
    
    /**
     * Получить DOM всего блока для данного элемента
     * @param {object} dom_object
     * @returns {unresolved}
     */
    getBlockProduct = function(dom_object){
        return dom_object.parents('.product-list__product-list-item');
    };
    
    //getProductsRight = function (dom_object){
    //    dom_object.find('.slider__slider-item');
    //};

    /**
     * Присваивание левому товару статуса STATUS_NOT_FOUND (левый крестик )
     */
    $body.on('click', '.product-list__item-mismatch-all', function (e) {        
        e.stopPropagation();
        let $this = $(this);        
        let data = $this.data();
        if (is_hide_mode()){
            let $block_product = getBlockProduct($this);
            // Добавляем в список все правые видимые товары в этом блоке
            let items = $block_product.find(
                    '.color-marker.pre_match',
                    '.color-marker.other',
                    '.color-marker.match',
                )
            if (items.length > 0){
                let q = confirm('Некоторые правые товары именют статус отличный от missmatch и будет перезаписан. Продолжить?');
                if (!q) {
                    $this.show();
                    return;
                }
            }
            // Добавить товар в список для отправки
            remember_product_left_data(data);
            change_visual_block_product($block_product);      // Изменить визуальное отображение (Пока скрыть)
        } else {
            $this.hide();
            lib.sendAjaxFromButton(data, onResponce);            
        }
                
        
        function onResponce(response) {
            switch (response.status) {
                case 'have_match':
                    let q = confirm(response.message);
                    if (!q) {
                        $this.show();
                        return;
                    }
                    $data['confirm'] = true;
                    lib.sendAjaxFromButton($data, onResponce);
                    break;
                case 'ok':
                    let html = response.html_index_table;
                    if (typeof (html) !== "undefined" && html !== null) {
                        var container = $("#id_table_container");
                        container.html(html);
                        lib.slider_init();
                    }
                    update_status_item();
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
     * Кнопка "удалить товар"
     */
    $body.on('click', '.js-del-item', function (e) {        
        e.stopPropagation();
        let q = confirm('Уверены?');
        if (!q)
            return false;
        
        js_del_item(this);
        
    });    
    
    js_del_item = function (product) {

        let $this = $(product);
        let $data = $this.data();
            $data.async = false;

        lib.sendAjaxFromButton($data, (response) => {
            if (response.status === 'ok') {
                let html = response.html_index_table;
                if (typeof (html) !== "undefined" ) {
                    var container = $("#id_table_container");
                    container.html(html);
                    lib.slider_init();
                }
                update_status_item();
            } else if (response.status === 'error') {
                alert(response.message);
            }
        });
    }   
        
        
    $body.on('click', '.js-del-all-visible-items', function () {
        let q = confirm('Уверены?');
        if (!q)
            return false;

        $('.product-list-item__del').each(function (a, b) {            
            //console.log($(b));
            js_del_item($(b));
        })
        
        console.log('reload');
        if ($('#filter-items__comparisons').val() !== 'ALL') {
            window.location.reload();
        }
    })    
    /**
     * Кнопка "отменить все выделения статусов" на правых товарах"
     */
    $body.on('click', '.js-reset-compare-all-visible-items', function () {
        let q = confirm('Уверены?');
        if (!q)
            return false;

        $('.product-list__product-list-item').each(function (a, b) {
            let $btn_reset_in_item = $(b).find('.js-reset-compare');
            if ($btn_reset_in_item.length)
                lib.reset_compare_item($btn_reset_in_item);
            console.log(a);
        });

        console.log('reload');
        //if ($('#filter-items__comparisons').val() !== 'ALL') {
            window.location.reload();
     //  }
    });

    /**
     * RED BTN
     */
    $('body').on('click', '.slider__red_button', function (e) {
        e.stopPropagation();
        let $this = $(this);
        let data = $this.data();

        if (is_batch_mode()) {
            let $item = $this.parents('.slider__slider-item');
            
            // Добавить товар в список для отправки
            if( remember_product_right_data(data) ) {
                // Если товар оказался новым то нужно обновить статистику
                let $block_product = $this.parents('.product-list__product-list-item');
                addUnitToStatistic($block_product, 'mismatch');
            }
            change_visual_product_right($item, 'mismatch');    // Изменить визуальное отображение
        } else {
            $this.hide();

            lib.sendAjaxFromButton(data, (response) => {
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
        }
        ;
        update_status_item();
    });

    /**
     * YELLOW BTN
     */
    $('body').on('click', '.slider__yellow_button', function (e) {
        e.stopPropagation();
        let $this = $(this);
        let data = $this.data();
        if (is_batch_mode()) {
            let $item = $this.parents('.slider__slider-item');
            // Добавить товар в список для отправки
            if( remember_product_right_data(data) ) {
                // Если товар оказался новым то нужно обновить статистику
                let $block_product = $this.parents('.product-list__product-list-item');
                addUnitToStatistic($block_product, 'pre_match');
            }
            change_visual_product_right($item, 'pre_match');      // Изменить визуальное отображение
        } else {
            $this.hide();

            lib.sendAjaxFromButton(data, (response) => {
                if (response.status === 'ok') {
                    let html = response.html_index_table;
                    if (typeof (html) !== "undefined" && html !== null) {
                        var container = $("#id_table_container");
                        container.html(html);
                        lib.slider_init();
                    } 
                }
                if (response.status === 'error') {
                    alert(response.message);
                }
                $this.show();
            });
        }
        ;
       update_status_item();
    });

    $('.slider_close').on('click', function (e) {
        e.stopPropagation();
        let $this = $(this);
        let $product_block = $this.parents('.product-list__product-list-item');
        $product_block.remove();
    });

    /*
     * Добавлено для того чтобы следущий обработчик получал валидное значение value, т.е. 0 | 1
     */
    $('#id_f_batch_mode').on('change', function (e) {
        let $this = $(this);
        $this.val($this.is(':checked') ? 1 : 0);
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
    addActionChangeFilter('id_f_batch_mode', 'f_batch_mode');

    $('.js-pre_match').on('click', function (e) {
        e.stopPropagation();
        let $parent = $(this).parents('.product-list__product-list-item');
        showProductsRight($parent ,'pre_match');
    });
    
    $('.js-match').on('click', function (e) {
        e.stopPropagation();
        let $parent = $(this).parents('.product-list__product-list-item');
        showProductsRight($parent, 'match');
    });
    
    $('.js-mismatch').on('click', function (e) {
        e.stopPropagation();
        let $parent = $(this).parents('.product-list__product-list-item');
        showProductsRight($parent, 'mismatch');
    });
    
    $('.js-other').on('click', function (e) {
        e.stopPropagation();
        let $parent = $(this).parents('.product-list__product-list-item');
        showProductsRight($parent, 'other');
    });

    $('.js-nocompare').on('click', function (e) {
        e.stopPropagation();
        let $parent = $(this).parents('.product-list__product-list-item');
        showProductsRight($parent, 'nocompare');
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

        if (filter.attr('data-init') === 'true') {
            return;
        }

        filter.attr('data-init', 'true');
        filter.on('change', function (e) {
            e.stopPropagation();
            send_product_right_data(); // Отослать данные на сервер

            let value = $(filter).val();
            $.ajax({
                method: "post",
                url: "/product/change-filter",
                dataType: 'json',
                data: {
                    'name': name_filter,
                    'value': value
                },
                success: function (data) {
                    switch (data.status) {
                        case 'ok':
                            let html = data.html_index_table;
                            var container = $("#id_table_container");
                            container.html(html);
                            break;
                        case 'info':
                            alert(data.message);
                            break;
                        case 'error':
                            alert(data.message);
                            break;
                    }
                    lib.slider_init();

                    for (var key in data.other) {
                        elem = $('#' + key);
                        elem.html(data.other[key]);
                    }
                },
                error: function (res) {
                    console.log(res.responseText);
                }
            });
        });
    }

    /**
     * Включен ли режим пакетной выборки элементов
     * (Пока решили что этот режим всегда включен)
     * 
     * @returns {Boolean}
     */
    is_batch_mode = function () {
        return true;
        // let $mode = $('#id_f_batch_mode');
        // return $mode.is(':checked');
    };

    /**
     * Включен ли режим скрытия правых товаров после выбора
     * (Пока берем копию кнопки от пакетной выборки)
     * 
     * @returns {Boolean}
     */
    is_hide_mode = function () {
        let $mode = $('#id_f_batch_mode');
        return $mode.is(':checked');
    };

    /**
     * Узнать количество видимых правых товаров в данном блоке
     * 
     * @param {object} $product - левый продукт, для которого нужно узнать количество видимых правых
     * @returns {integer}
     */
    get_count_products_right = function ($product) {
        let block_products = $product.parent('.product-list__product-list-item');
        let count_items = block_products.find('._sliderTop').count();
        return count_items;
    };         
            
    /** 
     * Фукция обновления фильтра статуса товаров
     * @returns {undefined}
     */                
    update_status_item = function () {
        var items = $('.PRODUCT-LIST-ITEM:visible').find('.SLIDER-ITEM');
        if ( !items.length ) {
           let status = $('#id_f_comparison_status');
           var status_next = status.find(':selected').next();     
           var status_prev = status.find(':selected').prev();
               if ( status_next.val().length > 0 ) {
                   status.val(status_next.val());                   
               } 
               status.trigger( "change" );
        } 
    };    

    
    /**
     * Запомнить выбор от правых товаров (Элементов)
     * 
     * @param {object} data_item
     * @returns {boolean} Является ли товар новым
     */
    remember_product_right_data = function (data_item) {
        // Сморим, есть ли уже этот элемент в массиве правых товаров, ожидающем отправку
        for (let data of datas_product_right) {
            if (data.id_item === data_item.id_item && data.id_source === data_item.id_source) {
                data = data_item; //Перезаписать
                return false;
            }
        }

        // Если эмемента в массиве нет то запоминаем в массив элемент
        datas_product_right.push(data_item);
        return true;
    };
    
    remember_product_left_data = function (data_product){
        // Сморим, есть ли уже этот элемент в массиве левых товаров, ожидающем отправку
        for (let data of datas_product_left){
            if (data.id_product === data_product.id_product && data.id_source === data_product.id_source) {
                data = data_product; //Перезаписать
                return false;
            }
        }
        
        // Если эмемента в массиве нет то запоминаем в массив элемент
        datas_product_left.push(data_product);
        return true;
    }

    /**
     * Отправить выбранные элементы на серсер
     * 
     * @returns {undefined}
     */
    send_product_right_data = function () {
        let data = {
            list_products_right: datas_product_right
        };
        lib.sendAjax('/product/compare-batch', data, (response) => {
            if (response.status === 'ok') {
                datas_product_right = [];
                let html = response.html_index_table;
                if (typeof (html) !== "undefined" && html !== null) {
                    var container = $("#id_table_container");
                    container.html(html);
                    lib.slider_init();
                }
            }
            if (response.status === 'error') {
                alert(response.message);
            }
        });
    };
    
    /**
     * Отправить данные отметок на сервер
     * 
     * @returns {undefined}
     */    
    send_datas_product = function () {
        let data = {
            list_product_right: datas_product_right,
            list_product_left:  datas_product_left
        };
        lib.sendAjax('/product/compare-products-batch', data, (response) => {
            if (response.status === 'ok') {
                datas_product_right = [];
                datas_product_left  = [];
                
                let html = response.html_index_table;
                if (typeof (html) !== "undefined" && html !== null) {
                    var container = $("#id_table_container");
                    container.html(html);
                    lib.slider_init();
                }
            }
            if (response.status === 'error') {
                alert(response.message);
            }
        });        
    };

    /**
     * Сменить визуальное отображение правого товара
     * 
     * @param {type} $item
     * @returns {undefined}
     */
    change_visual_product_right = function ($item, $class) {
        if (is_hide_mode()) {
            let items_block = $item.parents('.product-list__product-list-item');
            $item.remove();
            // Если в блоке не осталось выделеных элементов то обновляем список
            let count_products_block = items_block.find('.slider__slider-item').length;
            if (count_products_block <= 0) {
                send_product_right_data();
            }
        } else {
            $item.find('.color-marker')
                    .removeClass('nocompare')
                    .removeClass('pre_match')
                    .removeClass('other')
                    .removeClass('match')
                    .removeClass('mismatch')
                    .addClass($class);
            
            if ($class === 'mismatch'){
                $item.find('.slider__yellow_button').removeClass('-hover');
                $item.find('.slider__red_button').addClass('-hover');                
            }
            
            if ($class === 'pre_match'){
                $item.find('.slider__red_button').removeClass('-hover');
                $item.find('.slider__yellow_button').addClass('-hover');
            }
        }
    };
    
    /**
     * Сменить визуальное отображение всего блока товаров
     * 
     * @param {object} $block_product
     * @returns {undefined}
     */
    change_visual_block_product = function($block_product){
        $block_product.hide();
    };

    /**
     * Оставить правые товары видимыми в блоке только отмеченные данным классом
     * @param {object}    $block_items
     * @param {string} class_marker
     * @returns {undefined}
     */
    showProductsRight = function ($block_items, class_marker) {
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
     * Увеличить значение в статистике
     * @param {string} name_status_compare
     * @returns {undefined}
     */
    addUnitToStatistic = function ($block_product, status_compare){
        // Увеличивам количество в целевом квадратике
        let $class = 'span.js-'+status_compare;
        let $block = $block_product.find($class);
        $block.text(Number($block.text())+1);
        
        // Уменьшаем количество в белом квадратике
        $block = $block_product.find('.js-nocompare');
        $block.text(Number($block.text())-1);
        
        // Меняем запись общее
        $block = $block_product.find('.product-list-item__processed');
        let val = $block.text().split('/');
        if (val.length !== 2) return;
        let v1 = Number(val[0])+1;
        $block.text(v1+'/'+val[1]);
    };
});