/*********************************************/
// jQuery resizeEnd Event v1.0.1
// Copyright (c) 2013 Giuseppe Gurgone

// This work is licensed for reuse under the MIT license.
// See the license file for details https://github.com/giuseppeg/jQuery-resizeEnd/blob/master/LICENSE

// About:
// jQuery resizeEnd defines a special event
// that is fired when the JavaScript "resize" event has finished.
//
// It also defines an alias function named after the event name
// which binds an event handler to the "resizeEnd" event,
// or trigger that event on an element.

// Usage:
//
// $(window).on("resizeEnd", function (event) {
//      // go nuts
// });
//
// or use its alias function:
//
// $(window).resizeEnd(function (event) {
//      // go nuts
// });

// Project Home - http://giuseppeg.github.io/jQuery-resizeEnd/
// GitHub repo  - http://github.com/giuseppeg/jQuery-resizeEnd/
(function ($, window) {
    var jqre = {};

    // Settings
    // eventName: the special event name.
    jqre.eventName = "resizeEnd";

    // Settings
    // delay: The numeric interval (in milliseconds)
    // at which the resizeEnd event polling loop executes.
    jqre.delay = 250;

    // Poll function
    // triggers the special event jqre.eventName when resize ends.
    // Executed every jqre.delay milliseconds while resizing.
    jqre.poll = function () {
        var elem = $(this),
                data = elem.data(jqre.eventName);

        // Clear the timer if we are still resizing
        // so that the delayed function is not exectued
        if (data.timeoutId) {
            window.clearTimeout(data.timeoutId);
        }

        // triggers the special event
        // after jqre.delay milliseconds of delay
        data.timeoutId = window.setTimeout(function () {
            elem.trigger(jqre.eventName);
        }, jqre.delay);
    };

    // Special Event definition
    $.event.special[ jqre.eventName ] = {

        // setup:
        // Called when an event handler function
        // for jqre.eventName is attached to an element
        setup: function () {
            var elem = $(this);
            elem.data(jqre.eventName, {});

            elem.on("resize", jqre.poll);
        },

        // teardown:
        // Called when the event handler function is unbound
        teardown: function () {
            var elem = $(this),
                    data = elem.data(jqre.eventName);

            if (data.timeoutId) {
                window.clearTimeout(data.timeoutId);
            }

            elem.removeData(jqre.eventName);
            elem.off("resize", jqre.poll);
        }
    };

    // Creates an alias function
    $.fn[ jqre.eventName ] = function (data, fn) {
        return arguments.length > 0 ?
                this.on(jqre.eventName, null, data, fn) :
                this.trigger(jqre.eventName);
    };

}(jQuery, this));




var lib = {};

    /*
     * Вспомогатеьная функция отправки AJAX
     * 
     * @param {type} url
     * @param {array} data
     * @param {function} onSuсcess
     * @returns {}
     * 
     */
    lib.sendAjax = function(url, data, onSuccess) {
        $.ajax({
            url: url,
            type: "POST",
            data: data,
            dataType: "json",
            success: function (response) {
                onSuccess(response);
            },
            error: function (jqXHR, exception) {
                if (jqXHR.status === 0) {
                    alert('Not connect. Verify Network.');
                } else if (jqXHR.status === 404) {
                    alert('Requested page not found (404).');
                } else if (jqXHR.status === 500) {
                    alert('Internal Server Error (500).');
                } else if (exception === 'parsererror') {
                    alert('Requested JSON parse failed.');
                } else if (exception === 'timeout') {
                    alert('Time out error.');
                } else if (exception === 'abort') {
                    alert('Ajax request aborted.');
                } else {
                    alert('Uncaught Error. ' + jqXHR.responseText);
                }
            }
        });
    }

    /*
     * Вспомогатеьная функция отправки AJAX с кнопки
     * 
     * @param {array} data Обязательно доджен содержать data['url']
     * @param {function} onSuсcess
     * @returns {}
     * 
     */
    lib.sendAjaxFromButton = function(data, onSuccess) {
        lib.sendAjax(data['url'], data, onSuccess);
    }

lib.url = class {

    get_params() {
        return this.u.pathname;
    }

    get_path_name() {
        return this.u.pathname;
    }

    constructor() {
        this.u = new URL(window.location.href);
        u.search;     // '?id=6369&node=3'
        u.pathname;   // '/product/view'

        let params = u.searchParams;
        params.set('aaaa', 'bbbb');
        params.toString(); // 'id=6369&node=3&aaaa=bbbb'


        /*
         u.searchParams.forEach(function(v,name){
         console.log(v)
         console.log(name)
         });
         
         */
    }

}

/** Добавляем в url параметры для jquery объектов <a>
 *  @param $items_a  например: $('a.class_name')
 *  @param params_to_url например: { id: 123, param_2: 'ssss' } добавится или обновится в каждом урле объектов $items_a
 *  @return void
 */
lib.change_url_in_items = function ($items_a, params_to_url) {
    $items_a.map(function (a, b) {

        let a_ = $('<a>', {
            href: $(b).attr('href')
        });

        let url = a_.prop('protocol') + '//' + a_.prop('hostname') + a_.prop('pathname') + a_.prop('search') + a_.prop('hash');


        let u = new URL(url);
        u.pathname;   // '/product/view'
        let params = u.searchParams;
        for (let i in params_to_url) {
            if (params_to_url.hasOwnProperty(i))
                params.set(i, params_to_url[i]);
        }

        url = u.pathname + '?' + params.toString();
        $(b).attr('href', url)
    })

}

lib.brief_on_hover1 = function (selector, prop, cb) {
    let left = 20, top = 20, arrow_position = 'left';

    if (typeof prop === 'object') {
        if (typeof prop.offset_left !== 'undefined')
            left = prop.offset_left;
        if (typeof prop.offset_top !== 'undefined')
            top = prop.offset_top;
        if (typeof prop.arrow_position !== 'undefined')
            arrow_position = prop.arrow_position; // left | right | top | top_center | bottom
    }
    // todo create brief on init()
    let html = '<div id="brief" class="[ BRIEF ] brief"><div class="brief__arrow "></div><div class="brief__message"></div></div>';
    let $brief = $('#brief');
    if (!$brief.length) {
        $('body').prepend(html);
        $brief = $('#brief');
    }

    function show_brief(evt) {
        let posX = evt.originalEvent.x - left;
        if (posX < 0) {
            arrow_position = 'right';
            posX = evt.originalEvent.x + 20;
        } else {
            arrow_position = 'left';
        }

        $brief.css({
            'left': posX,
            'top': evt.originalEvent.y - top,
        }).find('.brief__arrow').removeClass('-left').removeClass('-right').removeClass('-top').removeClass('-top_center').removeClass('-bottom').addClass('-' + arrow_position)
        let $this = $(this);

        let $message = $brief.find('.brief__message')

        if (typeof cb === 'function') {
            cb($message, $this);
        } else {
            $message.text($this.data('text_brief'));
        }
        $brief.show();
    }

    $('body').on('mousemove mouseup', selector, show_brief);

    $('body').on('mouseout mousedown', selector, function (evt) {
        $brief.hide();
        $brief.css('width', false);
    });
}

lib.brief_on_hover = function (selector, prop, cb) {
    let left = 215, right = 15, top = 15, arrow_position = 'left';
    if (typeof prop === 'object') {
        if (typeof prop.offset_left !== 'undefined')
            left = 215 + prop.offset_left;
        if (typeof prop.offset_right !== 'undefined')
            right = 15 + prop.offset_right;
        if (typeof prop.offset_top !== 'undefined')
            top = 15 + prop.offset_top;
        if (typeof prop.arrow_position !== 'undefined') {
            // left | right
            arrow_position = prop.arrow_position;

        }
    }

    // todo create brief on init()
    let html = '<div id="brief" class="[ BRIEF ] brief"><div class="brief__arrow "></div><div class="brief__message"></div></div>';
    let $brief = $('#brief');
    if (!$brief.length) {
        $('body').prepend(html);
        $brief = $('#brief');
    }

    function show_brief(evt) {
        $brief.css({
            'left': evt.originalEvent.x - left,
            'top': evt.originalEvent.y - top,
        }).find('.brief__arrow').removeClass('-left').removeClass('-right').removeClass('-top').removeClass('-top_center').removeClass('-bottom').addClass('-' + arrow_position)
        let $this = $(this);

        let $message = $brief.find('.brief__message')

        if (typeof cb === 'function') {
            cb($message, $this);
        } else {
            $message.text($this.data('text_brief'));
        }
        $brief.show();
    }

    //$('body').on('mousemove',selector, show_brief);

    /*
     $('body').on('mousemove',selector, function(evt){
     $brief.css({
     'left': evt.originalEvent.x - left,
     'top': evt.originalEvent.y - top,
     }).find('.brief__arrow').removeClass('-left').removeClass('-right').removeClass('-top').removeClass('-bottom').addClass('-'+arrow_position)
     let $this = $(this);
     
     let $message = $brief.find('.brief__message')
     
     if (typeof cb === 'function') {
     cb($message, $this);
     }else{
     $message.text( $this.data('text_brief') );
     }
     $brief.show();
     })
     
     */


    $('body').on('mousemove mouseup', selector, show_brief);


    $('body').on('mouseout mousedown', selector, function (evt) {
        $brief.hide();
        $brief.css('width', false);
    });

}

lib.copy_to_buffer = function (click_selector, copy_selector) {

    $('body').on('click', click_selector, function () {
        let copyText = $(copy_selector).text();


        //copyText.select();

        document.execCommand("copy");
        alert("Copied the text: " + copyText.value);
    })
}

lib._visible_cnt_right_items = function _visible_cnt_right_items() {
    // определим количество товаров
    let $block = $('.slider__view-1').eq(0);

    let $first_visible = $block.find('.slider__slider-item.item.slick-slide.slick-current');
    //let $items_block = $block.find('.slider__view-1._sliderTop.product-view__slider.slick-initialized.slick-slider');
    let items_block_width = $block.width();

    let item_width = 102;

    let res = Math.round(items_block_width / item_width);
    /*console.log(res);*/
    return res;
}

lib.inputHandler = function (wrapper, selector, callback, options) {

    // Force options to be an object
    var _options = options || {};

    if (typeof _options.minSymbol === "undefined")
        _options.minSymbol = 0;
    if (typeof _options.blurOn === "undefined")
        _options.blurOn = 1; // запускать когда пропадает фокус
    if (typeof _options.startFocusOn === "undefined")
        _options.startFocusOn = 0;
    if (typeof _options.delayMobile === "undefined")
        _options.delayMobile = 0;
    if (typeof _options.delay === "undefined")
        _options.delay = 1000;
    /*+ beforeStart_afterMinSymbol в функции processing - это когда нажимаешь на любую кнопку без всяких ожиданий,
     например Если мы используем minSymbol то целесообразно использовать _afterMinSymbol  _beforeMinSymbol
     что бы например показыать "ищу..." когда будет выполняться запрос на сервер (в *_beforeMinSymbol)
     а не показывать "ищу..." в функции (в *_afterMinSymbol)
     если minSymbol не используем то можно использовать только _afterMinSymbol
     */

    //+ beforeStart_beforeMinSymbol в функции processing - это когда нажимаешь на любую кнопку без всяких ожиданий
    //+ focusOut в событии blur что бы скрыть например авткомплит при смене фокуса


    var isMobile = function () {
        var a = navigator.userAgent || navigator.vendor || window.opera;
        return /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4));
    };
    var block = true;
    var sto;
    var str, str0, str1;
    var onlyArrows = true;

    $(wrapper).on('blur', selector, function (evt) {
        var _this = $(this);
        if (typeof _options.focusOut === "function")
            _options.focusOut(_this);
    });

    $(wrapper).on('focus', selector, function (evt) {
        var _this = $(this);
        if (typeof _options.focusIn === "function")
            _options.focusIn(_this);
        str = $(_this[0]).val();
    });

    if (_options.startFocusOn)
        $(wrapper).on('focus', selector, processing);

    if (_options.blurOn) {
        $(wrapper).on('blur', selector, function (evt) {
            var _this = $(this);
            if ($(_this[0]).val() !== str) {
                callback(_this, evt);
                str = $(_this[0]).val();
            }
            str = $(_this[0]).val();
            if (sto)
                clearTimeout(sto);
        });
    }

    function processingKeyup(evt) { // keydown keyup - ! делает два одинаковых события
        var _this = $(this);
        if (evt.keyCode === 37 || evt.keyCode === 38 || evt.keyCode === 39 || evt.keyCode === 40) {

        } else {
            if ($(_this[0]).val().length >= _options.minSymbol) {
                if (typeof _options.beforeStart_afterMinSymbol === "function")
                    _options.beforeStart_afterMinSymbol(_this);
            } else {
                if (typeof _options.beforeStart_beforeMinSymbol === "function")
                    _options.beforeStart_beforeMinSymbol(_this);
            }
        }
    }

    function processing(evt) { // keydown keyup - ! делает два одинаковых события

        var _this = $(this);
        var delay;

        if (evt.keyCode !== 37 || evt.keyCode !== 38 || evt.keyCode !== 39 || evt.keyCode !== 40) {
            onlyArrows = false;
        }

        if ((evt.keyCode === 13 && evt.type === 'keyup' && _this[0].localName !== 'textarea'/*&& str0.length >= 1*/)) { // нажали на enter сразу запускаем (не ждем 1 или 4 секунд)
            block = true;
            callback(_this, evt);
            str = $(_this[0]).val();

            //if (sto !== undefined && sto) clearTimeout(sto);
            if (isMobile() && $(_this[0]).val().length) {
                _this.blur()
            }
            return;
        }

        if (isMobile()) {
            delay = 4000; // для мобилы задержка 4 сек
            if (_options.delayMobile)
                delay = _options.delayMobile;
        } else {
            if (_options.delay)
                delay = _options.delay;
            else
                delay = 1000;
        }


        if (sto) {
            clearTimeout(sto);
            block = false;
        }
        sto = setTimeout(function () {


            // выборку делаем тут так как keyDown запускает событие до напечатывания последней буквы
            // обрати внимание что this в setTimeout не работает, используем сохраненный элемент
            //str = $(_this[0]).val();

            if (block === false) {
                //if (onlyArrows) block = true;
                //if (isMobile() && $(_this[0]).val().length && !onlyArrows) { _this.blur(); block = true; } // убирем фокус с инпута если это мобила и поле с текстом, если пустое поле оставляем фокус

                if (_options.startFocusOn)
                    callback(_this, evt);
                if (!block) {
                    if (evt.keyCode !== 37 && evt.keyCode !== 38 && evt.keyCode !== 39 && evt.keyCode !== 40) { // не реагируем на стрелки
                        if ($(_this[0]).val().length >= _options.minSymbol || (evt.keyCode === 13 && $(_this[0]).val().length >= _options.minSymbol) || (evt.keyCode === 8 && $(_this[0]).val().length >= _options.minSymbol) || (evt.keyCode === 46 && $(_this[0]).val().length >= _options.minSymbol)) {
                            //if ( $(_this[0]).val() !== str) {
                            block = true;
                            callback(_this, evt);
                            str = $(_this[0]).val();
                            if (isMobile() && $(_this[0]).val().length && _this[0].localName !== 'textarea') {
                                _this.blur()
                            }
                            //}
                        }
                    }
                }
            }
        }, delay);

    }

    $(wrapper).on('keyup', selector, processingKeyup);

    $(wrapper).on('keyup', selector, processing); //keydown
};

/*
lib.change_statistics_cnt = function ($item, action) {
    let $root = $item.parents('.product-list__product-list-item');
    if (!$root.length) {
        $root = $item.parents('.product-page');
    }

    let $marker_before = $item.find('.color-marker');

    let name_before = '';
    if ($marker_before.hasClass('match')) {
        name_before = 'match';
        //console.log(name_before);
    } else if ($marker_before.hasClass('pre_match')) {
        name_before = 'pre_match';
        //console.log(name_before);
    } else if ($marker_before.hasClass('mismatch')) {
        name_before = 'mismatch';
        //console.log(name_before);
    } else if ($marker_before.hasClass('other')) {
        name_before = 'other';
        //console.log(name_before);
    } else {
        name_before = 'nocompare';
        //console.log(name_before);
    }

    let stat_block = $root.find('.product-page__product-statistics-1234')
    if (!stat_block.length) {
        stat_block = $root.find('.product-list-item__compare-statistics')
    }


    let mismatch = parseInt(stat_block.find('.js-mismatch').text());
    let pre_match = parseInt(stat_block.find('.js-pre_match').text());
    let match = parseInt(stat_block.find('.js-match').text());
    let other = parseInt(stat_block.find('.js-other').text());
    let nocompare = parseInt(stat_block.find('.js-nocompare').text());

    if (action === 'pre_match')
        pre_match = pre_match + 1;
    if (action === 'match')
        match = match + 1;
    if (action === 'mismatch')
        mismatch = mismatch + 1;


    if (name_before === 'pre_match')
        pre_match = pre_match - 1;
    if (name_before === 'match')
        match = match - 1;
    if (name_before === 'mismatch')
        mismatch = mismatch - 1;
    if (name_before === 'other')
        other = other - 1;
    if (name_before === 'nocompare')
        nocompare = nocompare - 1;

    if (action === 'reset') {
        nocompare = match + pre_match + mismatch + other + nocompare + 1;

        stat_block.find('.js-pre_match').text(0);
        stat_block.find('.js-match').text(0);
        stat_block.find('.js-mismatch').text(0);
        stat_block.find('.js-other').text(0);
        stat_block.find('.js-nocompare').text(nocompare);

        let product_list_item__processed = $root.find('.product-list-item__processed');
        let nn = product_list_item__processed.text().split('/');
        product_list_item__processed.text('0' + '/' + parseInt(nn[1]));

        return true;
    }

    stat_block.find('.js-mismatch').text(mismatch);
    stat_block.find('.js-pre_match').text(pre_match);
    stat_block.find('.js-match').text(match);
    stat_block.find('.js-other').text(other);
    stat_block.find('.js-nocompare').text(nocompare);

    if (name_before === 'nocompare') {
        let processed_block = $root.find('.product-list-item__processed')
        // 11/20
        let nn = processed_block.text();
        let a_nn = nn.split('/');
        processed_block.text((parseInt(a_nn[0]) + 1) + '/' + a_nn[1])
    }
}
*/

lib.slider_refresh = function slider_refresh(selector) {
    let _selector = selector || '._sliderTop';
    $(_selector).slick('refresh');
}
lib.slider_destroy = function slider_destroy(selector) {
    let _selector = selector || '._sliderTop';
    $(_selector).slick('destroy');
}

lib.slider_init = function slider_init(start_slide) {
    let _start_slide = start_slide || 0;
    let $list = $('._sliderTop .slider__slider-item');
    $list.each(function (a, b) {
        if ($(b).hasClass('-current')) {
            _start_slide = a;
            /*console.log(_start_slide);*/
        }
    })


    $('._sliderTop').slick({
        // centerPadding: '60px',
        infinite: false,
        arrows: true,
        slidesToScroll: _start_slide ? 1 : lib._visible_cnt_right_items(),
        slidesToShow: _start_slide ? 1 : lib._visible_cnt_right_items(),
        variableWidth: true,
        centerMode: false,
        initialSlide: _start_slide
    });

}

//Убирает все полосы статусов с товаров
/*
lib.reset_compare_item = function ($this) {
    let $data = $this.data();
    
    lib.sendAjaxFromButton($data, (response) => {
        if (response.status === 'ok'){
            let html = response.html_index_table;
            var container = $("#id_table_container");
            container.html(html);
            lib.slider_init();

            let $compare_items = $item.find('.slider__slider-item'); // Все правые товары
            $compare_items.find('.color-marker')
                    .removeClass('pre_match')
                    .removeClass('match')
                    .removeClass('nocompare')
                    .removeClass('other')
                    .removeClass('mismatch')
                    .addClass('nocompare');
            $compare_items.find('.slider__yellow_button').removeClass('-hover');
            $compare_items.find('.slider__red_button').removeClass('-hover');
  
        } else if ( response.status === 'error'){
            alert(response.message);
        }
    });
};
*/
/*
lib.reset_compare_item = function ($this) {
    let p_id = $this.data('p_id');
    let source_id = $this.data('source_id');

    let $item = $this.parents('.product-list__product-list-item');
    if (!$item.length) {
        $item = $this.parents('.product-page');
    }

    let $compare_items = $item.find('.slider__slider-item');
    let $color_markers = $item.find('.color-marker');

    $compare_items.find('.color-marker')
            .removeClass('pre_match')
            .removeClass('match')
            .removeClass('nocompare')
            .removeClass('other')
            .removeClass('mismatch')
            .addClass('nocompare');
    $compare_items.find('.slider__yellow_button').removeClass('-hover');
    $compare_items.find('.slider__red_button').removeClass('-hover');

    //lib.change_statistics_cnt($this, 'reset');

    if (!p_id)
        return false;

    $.ajax({
        url: '/product/reset_compare?id=' + p_id + '&source_id=' + source_id,
        type: "get",
        beforeSend: function () {},
        dataType: "json",
        success: function (response) {

        }
    });
}
*/

$(function () {
//    $('body').on('click', '.js-reset-compare', function (e) {
//        e.stopPropagation();
//        let $this = $(this);
//        lib.reset_compare_item($this);
//    })



    lib.brief_on_hover('.js-addition-info-for-price', {'offset_left': -270, 'arrow_position': 'right'}, function ($message, $this) {
        //console.log($this.data('addition_info_for_price'));
        let html = '';
        let addition_info_for_price = $this.data('addition_info_for_price');

        for (let k in addition_info_for_price) {
            if (addition_info_for_price.hasOwnProperty(k)) {
                let title = k;
                let value = addition_info_for_price[k];
                html += '<span class="addition_info_for_price"><span class="__blue-title">' + title + ':</span>' + value + '</span>';

            }
        }

        $message.parents('.BRIEF').css('width', 320);
        $message.html(html ? html : 'для этого парсера, не установлены поля для подсказки, тут: settings/fields_extend_price')

    });

    lib.brief_on_hover('.main-item-title', {'offset_left': 148}, function ($message, $this) {
        let title = $this.text();
        $message.parents('.BRIEF').css('width', 320);

        $message.html(title)
    })

    // на правом товаре Написана константином как новая версия brief_on_hover. Но тогда остальные смещения нужно подобрать заново 
    lib.brief_on_hover1(".slider-item__border .-img, .slider-item__border .slider_images, .slider-item__border.slider_images", {'offset_left': 665, 'arrow_position': 'left'}, function ($message, $this) {
        let description_right = '<div>' + $this.attr('data-description_right') + '</div>';
        let img_right = '<img style="max-width: 100%;" src="' + $this.attr('data-img_right') + '" />';
        let footer_right = $this.attr('data-footer_right');
        let count_images_right = $this.attr('data-count_images_right');

        let description_left = '<div>' + $this.attr('data-description_left') + '</div>';
        let img_left = '<img style="max-width: 100%;" src="' + $this.attr('data-img_left') + '" />';
        let footer_left = $this.attr('data-footer_left');

        $message.parents('.BRIEF').css('width', 640);

        $message.html(
                "<table class=\"brief_table\">" +
                "<tr>" +
                "<td class = 'first' width=\"50%\">" + description_left + "</td>" +
                "<td class = 'second'>" + description_right + "</td>" +
                "</tr>" +
                "<tr>" +
                "<td class = 'first' width=\"50%\">" + img_left + "</td>" +
                "<td class = 'second'>" + img_right + "</td>" +
                "</tr>" +
                "<tr>" +
                "<td class = 'first' width=\"50%\">" + footer_left + "</td>" +
                "<td class = 'second'>" + footer_right + count_images_right + "</td>" +
                "</tr>" +
                "</table>"
                );

    });

    let $brief = $('#brief');
    let $body = $('body');
    lib.brief_on_hover('.js__p-left, .slider__left-item-img-wrapper-link, .products-list__img-wrapper-link',
            {'offset_left': -170, 'offset_top': -50, 'arrow_position': 'top'},
            function ($message, $this) {
                let title_ = $this.data("tree");
                if (!title_)
                    title_ = '-';

                let replace_1 = 'url("';
                let replace_2 = '")';

                let img_src = '';
                if ($this.find(".slider__left-item-img").length) {
                    img_src = $this.find(".slider__left-item-img").css('background-image');
                } else {
                    img_src = $this.find(".products-list__img").css('background-image');
                }
                if (img_src) {
                    img_src = img_src.replace(replace_1, '');
                    img_src = img_src.replace(replace_2, '');
                }

                let title = '<strong>Brand: ' + title_ + '</strong><br>';
                let description = '<div>' + $this.data("description") + '</div>';
                if (!img_src)
                    img_src = $this.data('img');
                let img = '<img style="max-width: 500px; height: auto" src="' + img_src + '" />';


                $message.html(title + img);
                //$message.parents('.BRIEF').css('width', 150);
                $message.parents('.BRIEF').css('width', 520);
            });



    $('.slider__left-item-other-img').hover(function () {
        let $this = $(this);

        let $link = $this.css('background-image');

        let root = $this.parents('.slider__left-item');
        root.find('.slider__left-item-img').css('background-image', $link)

    });

    $('.products-list__td2 .slider__left-item-other-img').hover(function () {
        let $this = $(this);

        let $link = $this.css('background-image');

        let root = $this.parents('.products-list__td2');
        root.find('.products-list__img').css('background-image', $link)


    });

    $body.on('click', '.products-list__td2  .products-list__img-wrapper-link', function (e) {
        console.log('link');
        window.open($(this).data('link'));
    })






})






/* SELECTED */
$(function () {
    var select, i, j, l, ll, selElmnt, create_div_selected, div_option, copy_from_option;
    /*look for any elements with the class "custom-select-my":*/
    select = document.getElementsByClassName("custom-select-my");
    l = select.length;
    /* select ы*/
    for (i = 0; i < l; i++) {
        selElmnt = select[i].getElementsByTagName("select")[0];
        ll = selElmnt.length;
        /*for each element, create a new DIV that will act as the selected item:*/
// a = div
// x = select
        create_div_selected = document.createElement("DIV");
        create_div_selected.setAttribute("class", "select-selected " + selElmnt.options[selElmnt.selectedIndex].className);
        create_div_selected.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;

        select[i].appendChild(create_div_selected);
        /*for each element, create a new DIV that will contain the option list:*/
        div_option = document.createElement("DIV");
        div_option.setAttribute("class", "select-items select-hide ");
        for (j = 0; j < ll; j++) {
            /*for each option in the original select element,
             create a new DIV that will act as an option item:*/
            copy_from_option = document.createElement("DIV");
            copy_from_option.innerHTML = selElmnt.options[j].innerHTML;
            copy_from_option.setAttribute('class', selElmnt.options[j].className);
            /* option click */
            copy_from_option.addEventListener("click", function (e) {
                /*when an item is clicked, update the original select box,
                 and the selected item:*/

                var y, i, k, clicked_select, h, sl, yl;
                clicked_select = this.parentNode.parentNode.getElementsByTagName("select")[0];
                sl = clicked_select.length;
                h = this.parentNode.previousSibling;
                for (i = 0; i < sl; i++) {
                    if (clicked_select.options[i].innerHTML == this.innerHTML) {
                        clicked_select.selectedIndex = i;
                        h.innerHTML = this.innerHTML;
                        h.removeAttribute('class');
                        h.setAttribute('class', 'select-selected ' + this.className);
                        h.classList.remove('same-as-selected');
                        y = this.parentNode.getElementsByClassName("same-as-selected");
                        yl = y.length;
                        for (k = 0; k < yl; k++) {
                            y[k].classList.remove('same-as-selected');
                            //y[k].removeAttribute("class");
                        }
                        this.setAttribute("class", "same-as-selected " + this.className);



                        break;
                    }
                }

                h.click();
                /*!!!!!*/
                clicked_select.dispatchEvent(new Event('change'));

            });

            div_option.appendChild(copy_from_option);
        }
        select[i].appendChild(div_option);
        create_div_selected.addEventListener("click", function (e) {
            /*when the select box is clicked, close any other select boxes,
             and open/close the current select box:*/
            e.stopPropagation();
            closeAllSelect(this);
            this.nextSibling.classList.toggle("select-hide");
            this.classList.toggle("select-arrow-active");
        });
    }
    function closeAllSelect(elmnt) {

        /*a function that will close all select boxes in the document,
         except the current select box:*/
        var x, y, i, xl, yl, arrNo = [];
        x = document.getElementsByClassName("select-items");
        y = document.getElementsByClassName("select-selected");
        xl = x.length;
        yl = y.length;
        for (i = 0; i < yl; i++) {
            if (elmnt == y[i]) {
                arrNo.push(i)
            } else {
                y[i].classList.remove("select-arrow-active");
            }
        }
        for (i = 0; i < xl; i++) {
            if (arrNo.indexOf(i)) {
                x[i].classList.add("select-hide");
            }
        }
    }
    /*if the user clicks anywhere outside the select box,
     then close all select boxes:*/
    //document.addEventListener("click", closeAllSelect);
    /* / SELECTED */
})

