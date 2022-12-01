$(document).ready(function () {
    let $body = $('body');
    
    let datas_products_right = [];
    
    /**
     * Присваивание левому товару статуса STATUS_NOT_FOUND (левый крестик )
     */
    $body.on('click', '.product-list__item-mismatch-all', function (e) {
        e.stopPropagation();
        let $this = $(this);
        $this.hide();
        let $data = $this.data();
        
        function onResponce(response){
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
                    if (typeof(html) !== "undefined" && html !== null){
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
 
        lib.sendAjaxFromButton($data, onResponce);
    });
    
    /**
     * Кнопка "удалить товар"
     */    
    $body.on('click', '.js-del-item', function (e) {
        e.stopPropagation();
        let q = confirm('Уверены?');
        if (!q) 
            return false;
        
        let $this = $(this);
        let $data = $this.data();
        
        lib.sendAjaxFromButton($data, (response) => {
            if (response.status === 'ok'){
                let html = response.html_index_table;
                if (typeof(html) !== "undefined" && html !== null){
                    var container = $("#id_table_container");
                    container.html(html);
                    lib.slider_init();
                }
            } else if ( response.status === 'error'){
                alert(response.message);
            }
        });
    });
    
    /**
     * Кнопка "отменить все выделения статусов" на правых товарах"
     */     
    $body.on('click', '.js-reset-compare-all-visible-items', function () {
        let q = confirm('Уверены?');
        if (!q)
            return false;

        $('.product-list__product-list-item').each(function (a, b) {
            //console.log($(b));
            let $btn_reset_in_item = $(b).find('.js-reset-compare');
            if ($btn_reset_in_item.length)
                lib.reset_compare_item($btn_reset_in_item);
            console.log(a);
        })

        console.log('reload');
        if ($('#filter-items__comparisons').val() !== 'ALL') {
            window.location.reload();
        }
    });
    
    /**
     * RED BTN
     */ 
    $('body').on('click', '.slider__red_button', function (e) {
        e.stopPropagation();
        let $this = $(this);
        let $data = $this.data();
        
        if (is_batch_mode()){
            remember_product_right_data($data);
            
            let $item = $this.parents('.slider__slider-item');
            $item.find('.color-marker')
                .removeClass('nocompare')
                .removeClass('pre_match')
                .removeClass('other')
                .removeClass('match')
                .addClass('mismatch')
            $item.find('.slider__yellow_button').removeClass('-hover');
            $item.find('.slider__red_button').addClass('-hover');
        } else {
            $this.hide();
            
            lib.sendAjaxFromButton($data, (response) => {
                if (response.status === 'ok'){
                    let html = response.html_index_table;
                    if (typeof(html) !== "undefined" && html !== null){
                        var container = $("#id_table_container");
                        container.html(html);
                        lib.slider_init();
                    }
                } else
                if (response.status === 'error'){
                    alert(response.message);
                }

                $this.show();
            });
        };
    });

    /**
     * YELLOW BTN
     */ 
    $('body').on('click', '.slider__yellow_button', function (e) {
        e.stopPropagation();
        let $this = $(this);
        let $data = $this.data();
        if (is_batch_mode()){
            remember_product_right_data($data);
            
            let $item = $this.parents('.slider__slider-item');
            $item.find('.color-marker')
                 .removeClass('nocompare')
                 .removeClass('pre_match')
                 .removeClass('match')
                 .removeClass('other')
                 .removeClass('mismatch')
                 .addClass('pre_match');
            $item.find('.slider__yellow_button').addClass('-hover');
            $item.find('.slider__red_button').removeClass('-hover');
        } else {       
            $this.hide();
            
            lib.sendAjaxFromButton($data, (response) => {
                if (response.status === 'ok'){
                    let html = response.html_index_table;
                    if (typeof(html) !== "undefined" && html !== null){
                        var container = $("#id_table_container");
                        container.html(html);
                        lib.slider_init();
                    }
                }
                if (response.status === 'error'){
                    alert(response.message);
                }
                $this.show();
            });
        };
    });
    
    /*
     * Добавлено для того чтобы следущий обработчик получал валидное значение value, т.е. 0 | 1
     */
    $('#id_f_batch_mode').on('change', function (e) {
        let $this = $(this);
        $this.val($this.is(':checked')?1: 0);
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
    function addActionChangeFilter(id_filter, name_filter){
        let filter = $('#'+id_filter);
        filter.on('change', function (e) {
            e.stopPropagation();
            send_product_right_data();
            
            let value = $(filter).val();
            $.ajax({
                method: "post",
                url: "/product/change-filter",
                dataType:'json',
                data: {
                    'name': name_filter,
                    'value': value
                },
                success:function(data){
                    switch (data.status){
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

                    for (var key in data.other){
                        elem = $('#'+key);
                        elem.html(data.other[key]);
                    }
                },
                error:function(res){
                    console.log(res.responseText);
                }
            });
        });        
    }
    
    /**
     * Включен ли режим пакетной выборки элементов
     * 
     * @returns {Boolean}
     */
    is_batch_mode = function(){
        let $mode = $('#id_f_batch_mode');
        return $mode.is(':checked');
    };
    
    /**
     * Запомнить выбор от правых товаров
     * 
     * @param {object} item_data
     * @returns {undefined}
     */
    remember_product_right_data = function(item_data){
        // Сморим, есть ли уже этот элемент в массиве правых товаров, ожидающем отправку
        for (let data of datas_products_right) {
            if (data.id_item   === item_data.id_item &&
                data.id_source === item_data.id_source){
                    data = item_data;
                    return;
                }
        }
        
        // Запоминаем в массив элемент
        datas_products_right.push(item_data);    
    };
    
    /**
     * Отправить выбранные элементы на серсер
     * 
     * @returns {undefined}
     */
    send_product_right_data = function(){
        let data = {
            list_products_right: datas_products_right
        };
        lib.sendAjax('/product/compare-batch', data, (response) => {
            if (response.status === 'ok'){
                datas_products_right = [];
                let html = response.html_index_table;
                if (typeof(html) !== "undefined" && html !== null){
                    var container = $("#id_table_container");
                    container.html(html);
                    lib.slider_init();
                }
            }
            if (response.status === 'error'){
                alert(response.message);
            }
        });
    }
});