

$(function(){
  let start_settings = $('.js__settings .js__product.-start-settings');
  console.log(start_settings.data());
  
  lib.filter = {};
  // lib.filter.p_id = start_settings.data('p_id');
  // lib.filter.source_id = start_settings.data('source_id');
  lib.filter.comparison = start_settings.data('comparison');
  lib.filter.profile = start_settings.data('profile');

  clipboard = new ClipboardJS('.clipboard');
  clipboard.on('success', function(e) {
  
    alert('Скопировано: '+ e.text);
    
    e.clearSelection();
  });
  
  
  
  /* страница продукта */
  let $body = $('body');
  
  $body.on('click','.js__dynamic-link',function(){
  
  });
  
  
  $body.on('click','.product-page__product-statistics-1234 span',function(){
    let $this = $(this);
    let $items = $('.slider__slider-item');
    $('._sliderTop').show();
    $items.removeClass('-hide');
    
    $('.product-page__product-statistics-1234 span').removeClass('-current');
    $this.addClass('-current');
    
    if ($(this).hasClass('-reset_filter_1234')){
      $(this).addClass('-hidden');
      
      //return;
    }else{
      $this.parents('.product-page__product-statistics-1234').find('.js.-reset_filter_1234').removeClass('-hidden');
    }
    
    function filter(selector){
      return $items.filter(function(a,b){
        return !$(b).find(selector).length;  // !наоборот... все кроме
      })
    }
    
    let comparison = 'ALL';
    if ( $(this).hasClass('js-match') ){
      filter('.match').addClass('-hide');
      comparison = 'MATCH';
    }
    
    if ( $(this).hasClass('js-pre_match') ){
      filter('.pre_match').addClass('-hide');
      comparison = 'PRE_MATCH';
    }
    
    if ( $(this).hasClass('js-mismatch') ){
      filter('.mismatch').addClass('-hide');
      comparison = 'MISMATCH';
    }
    
    if ( $(this).hasClass('js-other') ){
      filter('.other').addClass('-hide');
      comparison = 'OTHER';
    }
    
    if ( $(this).hasClass('js-nocompare') ){
      filter('.nocompare').addClass('-hide');
      comparison = 'NOCOMPARE';
    }
  
    lib.slider_destroy();
    lib.slider_init();
  
    
    //$('.slider__slider-item .linkImg.slider-item__link-img').attr('href');
   
    if (!$('.slider__slider-item:not(.-hide)').length) $('._sliderTop').hide();
  
    lib.set_filter_comparison(comparison);
   
    //console.log(comparison);
    update_url_in_items();

    /*
    lib.get_arrow(  $('.next.js__arrow'), {
      p_id: start_settings.data('p_id'),
      comparison: comparison,
      source_id: start_settings.data('source_id'),
      direction: 'next',
    });
   */
  
    lib.get_dynamic_elements( {
      p_id: start_settings.data('p_id'),
      comparison: comparison,
      source_id: start_settings.data('source_id'),
    });
    
    
  })
  
  lib.set_filter_comparison = function(comparison){
    $('.filter.__comparison-select').val(comparison)
    $('#arrow__filter-comparison').val(comparison);
  };
 
  
  $body.on('click','.slider__left-item__btn-red, .slider__left-item__btn-green', function(e){

    e.stopPropagation();
    let $this = $(this);
    let $root = $this.parents('.slider__left-item');

    // определим количество товаров
    let $first_visible = $('.slider__slider-item.item.slick-slide.slick-current');
    let $items_block = $('.slider__view-1._sliderTop.product-view__slider.slick-initialized.slick-slider');
    let items_block_width = $items_block.width();
    let item_width = $first_visible.width() + parseInt($first_visible.css('margin-right'));
    let item_showed_cnt = Math.round(items_block_width / item_width);
    let items_list = [], items_list_nocompare = [];
    let next = $first_visible;
    

    
    for (let i = 0; (item_showed_cnt) > i; i++){
      if ( next.find('.color-marker.nocompare').length ) {
        items_list_nocompare.push(next);
      }
      items_list.push(next);
      next = next.next();
    }
  
    
    
    if ($this.is('.slider__left-item__btn-green')){
    
      for (let x = 0; items_list_nocompare.length > x; x++){
        items_list_nocompare[x].find('.slider__green_button')[0].click();
      }
    
    }else if($this.is('.slider__left-item__btn-red')){
      console.log(items_list_nocompare);
      for (let x = 0; items_list_nocompare.length > x; x++){
        items_list_nocompare[x].find('.slider__red_button')[0].click();
      }
    
    }
    
    
   
  });
  /*
  $body.on('click','.slider__left-item__btn-red', function(e){
    console.log('btn');
    e.stopPropagation();
    let $this = $(this);
    let $root = $this.parents('.slider__left-item');
    
    let pid = $root.data('pid');
    
    $.ajax({
      url: '/product/missall?id='+pid+'&return=1',
      type: "get",
      beforeSend: function() {
      
      },
      dataType: "json",
      success: function(response){
        if (response.res === 'ok'){
          
          $('.id-layout__right-arrow .next')[0].click();
        }
      }
    });
  })
  */
  
  
  $('.compare__user-visible-fields').on('click',function(){
    let $this = $(this);
    let pid = $this.data('pid');
    if (!pid) return;
    let dataToSend = {};
    dataToSend.pid = pid;
  
    $.ajax({
      url: '/product/user_visible_fields',
      type: "POST",
      dataType: "json",
      data: dataToSend,
      beforeSend: function() {},
      success: function(response) {
        if (response.res === 'ok'){
          $this.text(response.text);
        }
      }
    });
  
  })
  
  
  // amazon
  /*
  $('.view-settings__amazon').on('click',function(){
    let next = $('a.next').attr('href');
    let prev = $('a.prev').attr('href');
    let new_prev_url, new_next_url;
    
    if ( $('#view-settings__amazon').prop('checked') ) {
      let u1 = new URL(window.location.origin + next);
      let p1 = u1.searchParams;
      p1.set('item_1__ignore_red' , '1');
      p1.set('direction', 'next');
      new_next_url = u1.pathname +'?'+ p1.toString()
  
      let u2 = new URL(window.location.origin + prev);
      let p2 = u2.searchParams;
      p2.set('item_1__ignore_red' , '1');
      p2.set('direction', 'prev');
      new_prev_url = u2.pathname +'?'+ p2.toString()
  
      
      console.log(new_next_url);
      console.log(new_prev_url);
      // 'next','slick-next'
      // 'prev','slick-prev'
    }else{
      let u1 = new URL(window.location.origin + next);
      let p1 = u1.searchParams;
      p1.set('item_1__ignore_red' , '0');
      p1.set('direction', 'next');
      new_next_url = u1.pathname +'?'+ p1.toString()
  
      let u2 = new URL(window.location.origin + prev);
      let p2 = u2.searchParams;
      p2.set('item_1__ignore_red' , '0');
      p2.set('direction', 'prev');
      new_prev_url = u2.pathname +'?'+ p2.toString()
  
      console.log(new_next_url);
      console.log(new_prev_url);
    }
  
    $('a.next').attr('href',new_next_url);
    $('a.prev').attr('href',new_prev_url);
  })
   */
  
  
  $body.on('click','.js__arrow',function(){
    let $this = $(this);
    let direction = $this.data('direction');
    let item_1__ignore_red = '0';
    if ( $('#view-settings__amazon').prop('checked') ){
      item_1__ignore_red = '1';
    }else{
      item_1__ignore_red = '0';
    }
    
    let u2 = new URL(window.location.href);
    let p2 = u2.searchParams;
    p2.set('item_1__ignore_red', item_1__ignore_red);
    p2.set('direction', direction);
    
    console.log(u2.pathname + '?' + p2.toString());
    window.location = u2.pathname + '?' + p2.toString();
    
    
  })
  
 
  
  /*
  function hide_show_arrows(){
    $('.js__arrow').each(function(a, b){
      // console.log(a);
      // console.log(b);
      let $this = $(b);
      let ignore_checked = $this.data('arrow_ignore_checked');
      let ignore_dont_checked = $this.data('arrow_ignore_dont_checked');
    
      if($('#view-settings__amazon').prop('checked')){
        if(ignore_checked) $this.removeClass('-hidden'); else $this.addClass('-hidden');
      }else{
        if(ignore_dont_checked) $this.removeClass('-hidden'); else $this.addClass('-hidden');
      }
    
    })
  }
  hide_show_arrows();
  
  
  $body.on('click','#view-settings__amazon',function(){
    hide_show_arrows();
  })
  */
  
  
  $body.on('click','.js__arrow_2',function(){
    let $this = $(this);
    let direction = $this.data('direction');
    /*
    let show_all_right_items = '0';
    if ( $('#view-settings__on-of-right-items').prop('checked') ){
      show_all_right_items = '1';
    }else{
      show_all_right_items = '0';
    }
   */
  
    let $items = $('.slider__slider-item.-current');
    let $next = $items.next();
    let $prev = $items.prev();
    let node = $('.slider__slider-item').eq(0).data('node_id');
    
    let u2 = new URL(window.location.href);
    let p2 = u2.searchParams;
  
    if (direction === 'next'){
      if ($next.length) node = $next.data('node_id');
    }else if (direction === 'prev'){
      if ($prev.length) node = $prev.data('node_id');
      else node = $('.slider__slider-item').eq($('.slider__slider-item').length-1).data('node_id')
    }
    
    p2.set('node', node);
    p2.delete('direction');
    
    //console.log(u2.pathname + '?' + p2.toString());
    window.location = u2.pathname + '?' + p2.toString();
    
  })
  
  /*
  $('.js__show-on-hover').on('hover',function(){
    $(this).parents('.js__root').find('.js__arrow').show();
  })
  
   */
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  // on-of-all
  $('.view-settings__on-of-right-items').on('click',function(){
  
    if ( $('#view-settings__on-of-right-items').prop('checked') ){
      let u1 = new URL(window.location.href);
      let p1 = u1.searchParams;
      p1.set('item_2__show_all', '1');
      
      window.location = u1.pathname + '?' + p1.toString();
    }else{
      let u2 = new URL(window.location.href);
      let p2 = u2.searchParams;
      p2.set('item_2__show_all', '0');
      
      window.location = u2.pathname + '?' + p2.toString();
    }
  })
  

  $body.on('click','.slider_close.-in-p-page',function(){
    //$(this).parents('.navbar__fixed-slider').hide();
  
    $('.navbar__fixed-slider').addClass('-hidden')
  })

  let show = false;
  
  function ON_scroll(set_selector, set_side){
    let show = false;
    let selector,$selector,side;
    let cb_hidden,cb_visible;
    let n_toggle = 0, offset = -50;
    let tmp_class = '_'+ Date.now();
    let _this = this, $this = $(this);

    this.get_tmp_class_name = function(){
      return tmp_class;
    }
    
    let init = function(set_selector, options){
      selector = set_selector;
      $selector = $(selector);
      
      let _options = options  || {};
      side = set_side || 'top';
      console.log('init');
    };
    
    this.when_hidden = function(cb){
      cb_hidden = cb;
      //if (typeof cb === 'function') cb($selector);
    }
    
    this.when_visible = function(cb){
      cb_visible = cb;
      //if (typeof cb === 'function') cb($selector);
    }
    
    $(window).on('scroll',function(){
      let block = $selector[0].getBoundingClientRect();
      let n_now = block[side] + offset;
      if (n_now < n_toggle){
        if (!show) {
          show = true;
          cb_hidden($selector);
          console.log( 'hide' );
        }
      }else{
        if (show) {
          show = false;
          cb_visible($selector);
          console.log( 'show' );
        }
      }
    });
  
    init(set_selector, set_side);
  }
  
  
  let $item = $('.wrapper_for_scroll_handler.__.block');
  let scroll = new ON_scroll('.line_for_scroll_handler');
  scroll.when_hidden(function($selector){
    
    //let tmp = $('<div class="'+ scroll.get_tmp_class_name() +'"></div>');
    //$item.before( tmp.css({ 'height' : $selector.height()}) );
    
    $item.find('.__plus').addClass('container');
    $item.css({
      'position' : 'fixed',
      'top': '50px','left': '0',
      'z-index' : '9999',
      'box-shadow': '0 0px 6px 0px #00000047',
    });
    
  });
  scroll.when_visible(function($selector,$this){
    // $( '.'+ scroll.get_tmp_class_name() ).remove();
  
    $item.find('.__plus').removeClass('container');
    $item.css({
      'position' : '',
      'top': '','left': '',
      'z-index' : '',
      'box-shadow': '',
    });
    
  });
  
  $body.on('click','.p-nav-right.__toggle-advanced-view-btn',function(){
    let advanced_view = $item.find('.position-1');
    //if (advanced_view.is('.-hidden')) advanced_view.addClass('-hidden'); else advanced_view.addClass('-hidden');
    
    advanced_view.toggleClass('-hidden');
    
  })
  
  
  
  //scroll.start();
  
  
/*
  $(window).on('scroll',function(){
    
    let block = $('.tbl.two_col.COMPARE')[0].getBoundingClientRect();
    
    if (block.top < 262){
      console.log( 'show' );
      if (!show){
        show = true;
        // -hidden  REMOVE
        $('.navbar__fixed-slider.-hidden').removeClass('-hidden')
        
        if ( $('.position-1 .slider__layout').length )
          $('.position-2').append( $('.slider__layout') ).append('<div class="slider_close -in-p-page"><div class="-line -line-1"></div><div class="-line -line-2"></div></div>')
    
        $('section.home').css('padding-top', '318px')
      }
      
    }else{
      show = false;
      console.log( 'hide' );
      $('.position-2 .slider_close.-in-p-page').remove();
  
      $('.navbar__fixed-slider').addClass('-hidden')
  
      if ( $('.position-2 .slider__layout').length )
        $('.position-1').append( $('.slider__layout') )
  
      $('section.home').css('padding-top', '')
  
    }
    
  })
*/
  
  // в fixed-slider__conteiner
  
  // section.home padding-top: 74px; →  318px;
  
  // top: 56px;
  // background: white;
  
  $('#compare-table__select-item-2,#s_E_Sales,#s_eBay_stock').on('change',function(){
    let $this = $(this).find('option:selected');
    let id = $this.data('pid');
    let node = $this.data('nid');
    let source_id = $this.data('source_id');
    window.location = '/product/view?id='+id+'&source_id='+source_id+'&node='+node;
  })
  
  
/*
  $body.on('click','.slider__left-item-img-wrapper-link',function(e){
    console.log('link');
    window.open($(this).data('link'));
  })
  
 */
  
  /* RED BTN */
  $('body').on('click','.slider__red_button',function(e){
    let $this = $(this);
    let $root = $this.parents('.slider__slider-item');
    lib.change_statistics_cnt($root,'mismatch');
    
    if (!$('.slider__red_button').length){
      $('.product-view__slider').remove();
    }
    
    let url = $this.data('link');
    if (!url) return false;
  
    //let isChecked = $('#view-settings__on-of-right-items:checked').length;
    let isChecked = true;
    if( !isChecked ){
      url += '&ignore-right-hidden=1'
    }else{
      url += '&ignore-right-hidden=0'
    }
    
    if (!isChecked) $root.remove();
    
    $.ajax({
      url: url,
      type: "get",
      beforeSend: function() {},
      dataType: "json",
      success: function(response){
        $root.find('.color-marker')
             .removeClass('nocompare')
             .removeClass('pre_match')
             .removeClass('other')
             .removeClass('match')
             .addClass('mismatch')
        $root.find('.slider__yellow_button').removeClass('-hover');
        $root.find('.slider__red_button').addClass('-hover');
      }
    });
    
  })
  
  /* GREEN BTN */
  $('body').on('click','.slider__yellow_button',function(e){
    
    let $this = $(this);
    let $item = $this.parents('.slider__slider-item');
    lib.change_statistics_cnt($item,'pre_match');
    
    //let $slider_block = $this.parents('.products-list__slider-wrapper');
    //let $root = $this.parents('.product-list__product-list-item');
    let $root = $this.parents('.slider__slider-item');
    
    let url = $this.data('link');
    if (!url) return false;
    // console.log(url+'&list=1');
    // return 1;
    $.ajax({
      url: url +'&list=1',
      type: "get",
      beforeSend: function() {},
      dataType: "json",
      success: function(response){
        $item.find('.color-marker')
             .removeClass('nocompare')
             .removeClass('pre_match')
             .removeClass('match')
             .removeClass('other')
             .removeClass('mismatch')
             .addClass('pre_match');
        $item.find('.slider__yellow_button').addClass('-hover');
        $item.find('.slider__red_button').removeClass('-hover');
      }
    });
    
  })
  
  
  
  
  
  


/*
  if ($list.length > 7) {
    center_mode = true;
    infinite_mode = true;
  }

*/

  
  
  
  
  lib.slider_init();
  
/*
  let swiper = new Swiper('.swiper-container', {
    spaceBetween: 6,
    roundLengths: true,
    slidesPerColumnFill: "column",
    slidesPerView: "auto"
    
  });
  */
  
  lib.p_nav_right__swiper = new Swiper('.p-nav-right.__swiper', {
    /*speed: 400,*/
    spaceBetween: 6,
    roundLengths: true,
    slidesPerColumnFill: "column",
    slidesPerView: "auto",
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
  });
  
  
  function init_left_img(){
    $("#zoom_01").ezPlus({
      responsive : true,
      scrollZoom : true,
      showLens: true,
      minZoomLevel:0.5,
      tint: true,
      tintColour: '#000',
      tintOpacity: 0.5,
      zoomLevel: 0.5,
      zoomEnabled:true,
      onShow: function(){
        $('.compare__right-item .control ').hide()
      },
      onHide: function(){
        $('.compare__right-item .control ').show()
      },
      zoomWindowWidth: 500,
      zoomWindowHeight: 320
    });
  }
  
  function init_right_img(){
    $("#zoom_02").ezPlus({
      responsive : true,
      scrollZoom : true,
      showLens: true,
      tint: true,
      tintColour: '#000',
      tintOpacity: 0.5,
      minZoomLevel:0.5,
      zoomLevel: 0.5,
      zoomEnabled:true,
      zoomWindowPosition: 11,
      
      onShow: function(){
        $('.compare__left-item .control ').hide()
      },
      onHide: function(){
        $('.compare__left-item .control ').show()
      },
      zoomWindowWidth: 500,
      zoomWindowHeight: 320
    });
  }
  
  
  init_left_img();
  init_right_img();
  
  $('.js__slider-img').hover(function(){
    let $this = $(this);
    let $link = $this.prop('src');
    
    let root = $this.parents('.js__root');
    //root.find('.js__item-img').css('background-image','url("'+$link+'")').attr('src',$link);
    root.find('.left-item__img-tag').attr('src',$link);
    root.find('.right-item__img-tag').attr('src',$link);
  
    init_left_img();
    init_right_img();
  
  });
  
  $('select[name="select-item-2"]').hover(function(){
    console.log(111);
  })
  
  /*
  $body.on('click','.js__item-img',function(){
    let $this = $(this);
    window.open($this.data('link'));
  })
  
   */
  
  
  lib.brief_on_hover('.product-page__product-statistics-1234 span',{'offset_left': -30},function($message,$this){
    let description = '<div>'+$this.data("text_brief")+'</div>';
    $message.parents('.BRIEF').css('width', 150);
    $message.html(description)
    
  });
  
  

  let cb = {
    'image_place': function(evt){
      let place = $(this).data('image-place');
      $('#' + place).attr('src', $(this).attr('src'));
    },
    'set': function(){
      $("[data-image-place]").click(cb.image_place);
    }
  };
  cb.set();
  $('#product-view-pjax').on('pjax:end', cb.set);

  // $('._sliderTop').getCurrent()
  
  lib.update_comparison_cnt = function(comparison_cnt,$items){
    $items.find('option').map(function(a,b){
      let name = $(b).val();
      let new_str = comparison_cnt[name].name + ' ('+ comparison_cnt[name].cnt +')';
      $(b).text(new_str);
    })
  };
  
  lib.get_dynamic_elements = function(data,cb){
    let dataToSend = $.extend({
      p_id: false,
      comparison: false,
      source_id: false,
      profile: $('#product_page__filter-profile').val(),
    }, data || {} );
  
    if (!dataToSend.p_id || !dataToSend.comparison || !dataToSend.source_id) {
      console.log('Указаны не все данные для получения ссылки для '+ dataToSend.direction);
      return false;
    }
    
    let select =  $('.js__move-direction-select');
    let next_wrapper =  $('.js__main_next_wrapper');
    let prev_wrapper =  $('.js__main_prev_wrapper');
    
    $.ajax({
      url: "/product/get_products_by_params",
      type: "POST",
      data: dataToSend,
      beforeSend: function(){
        let _save = select.clone();
        select.html('').append('<option>загружаю...</option>');
        
      },
      dataType: "json",
      success: function(response){
        let res_items = response.items;

        lib.get_dynamic_elements.result_handler_prev_next(res_items.next_items,next_wrapper,'next');
        lib.get_dynamic_elements.result_handler_prev_next(res_items.prev_items,prev_wrapper,'prev');
        lib.get_dynamic_elements.result_handler_select(res_items,select);
        lib.get_dynamic_elements.result_handler_right_products(res_items.this_item_right_items,select);
        
        // обновляем срелки 1 <- amazon ->   2 <- right -> 3 [x] left 4 [v][x] right 5 меняем url
        lib.get_dynamic_elements.result_handler_this_product_links(res_items);
      
        if (typeof cb === 'function') cb(response);
  
        lib.update_comparison_cnt( response.comparison_cnt, $('.filter.__comparison-select') );
        lib.update_comparison_cnt( response.comparison_cnt, $('#arrow__filter-comparison') );
        
      }
    });
    
  };
  
  lib.get_dynamic_elements.result_handler_this_product_links = function(res_items){
    // обновляем
    function get_first(items){
      for (let i in items)
        if (items.hasOwnProperty(i)){
          return items[i];
        }
    }
    
    // 1 <- amazon ->
    let $a_arrow_left =  $('.js__p-a-arrow-left');
    let $a_arrow_right = $('.js__p-a-arrow-right');
    // 2 <- right ->
    let $r_arrow_left =  $('.js__p-r-arrow-left');
    let $r_arrow_right = $('.js__p-r-arrow-right');

    // удаляем
    $a_arrow_left.remove();
    $a_arrow_right.remove();
    $r_arrow_right.remove();
    $r_arrow_left.remove();
    
    // создаем заново
    // LEFT
    let $p_a_arrows_wrapper = $('.js__p-a-arrows-wrapper');
    
    let $p_a_l = $( get_template('p_a_arrow_left') );
    let $p_a_r = $( get_template('p_a_arrow_right') );
  
    let l_next_one = get_first(res_items.next_items);
    let l_prev_one = get_first(res_items.prev_items);

    if (l_next_one){
      $p_a_r.data('p_id',l_next_one.id)
      $p_a_r.data('p_side','p_left')
      $p_a_arrows_wrapper.prepend($p_a_r);
    }
  
    if (l_prev_one){
      $p_a_l.data('p_id',l_prev_one.id)
      $p_a_l.data('p_side','p_left')
      $p_a_arrows_wrapper.prepend($p_a_l);
    }
    
    // RIGHT
    let $p_r_arrows_wrapper = $('.js__p-r-arrows-wrapper');
    let $p_r_l = $( get_template('p_r_arrow_left') );
    let $p_r_r = $( get_template('p_r_arrow_right') );
    
    let current_node = start_settings.data('node');
    let current_p_id = start_settings.data('p_id');
    
    let all_r = res_items.this_item_right_items;
    
    function next_key_by_direction(all_r,current,direction){
      if (!all_r) return false;
      let current_node = parseInt(current);
      let nodes_1 = Object.keys(all_r);
      let nodes_2 = Object.keys(all_r);
          nodes_2.sort((a, b) => b - a);
  
      // next
      if (direction === 'right'){
        return nodes_1.find( function(element) {
          return parseInt(element) > current_node
        })
      }

      // prev
      if (direction === 'left'){
        return nodes_2.find(function(element){
          return parseInt(element) < current_node
        })
      }
      
    }
  
    let node_n_l = next_key_by_direction(all_r,current_node-1,'left');
    let node_n_r = next_key_by_direction(all_r,current_node-1,'right');

    if (node_n_l && all_r[node_n_l]){
      // урл этого же товара только node другой = node_n_l + фильтр
      $p_r_l.data('p_id',current_p_id);
      $p_r_l.data('node_id', parseInt(node_n_l) + 1);
      $p_r_arrows_wrapper.prepend($p_r_l);
    }
  
    if (node_n_r && all_r[node_n_r]){
      // урл этого же товара только node другой = node_n_l + фильтр
      $p_r_r.data('p_id',current_p_id);
      $p_r_r.data('node_id', parseInt(node_n_r) + 1);
      $p_r_arrows_wrapper.prepend($p_r_r);
    }
    
    // 3 [x] left
    let a_btn_red;
    // 4 [v][x] right
    let r_btn_green;
    let r_btn_red;
    
    // todo 5 меняем url
    
  };
  
  $body.on('click','.js__dynamic-link',function(){
    let $this = $(this);
    let node_id = $this.data('node_id') ?? 1;
    console.log('node_id: ' + node_id);
    let p_id = $this.data('p_id');
  
    let u = new URL(window.location.href);
    u.search;     // '?id=6369&node=3'
    u.pathname;   // '/product/view'
  
    let params = u.searchParams;
    params.set('id',p_id);
    params.set('node',node_id);
    params.set('comparisons',$('.filter.__comparison-select').val());
    params.set('filter-items__profile',$('#product_page__filter-profile').val());
    params.set('source_id',start_settings.data('source_id'));
    params.toString();          // 'id=6369&node=3&aaaa=bbbb'
  
    u.searchParams.forEach(function(v,name){
      // console.log(v)
      // console.log(name)
    });
    
    // http://checker.loc/product/view?id=8024&node=4&source_id=1&comparisons=NOCOMPARE&filter-items__profile=General
    let pa = '/product/view?'+ params.toString();
    console.log(pa);
    window.location = pa;
  });
  
  
  /*
  lib.brief_on_hover('.js__p-right', {'offset_left': 130,'offset_top': -50,'arrow_position': 'top_center'},
    function($message,$this){
 
      
      $message.html('load...');
      $message.parents('.BRIEF').css('width', 690);
  });
  */
  
  
  lib.get_dynamic_elements.result_handler_prev_next = function(res_items,wrapper,direction){
    wrapper.html('');
    
    for (let i = 0; i < res_items.length ; i++){
      let res_item = res_items[i];
  
      let item = get_template('p_min_view_item__l');
      
  
      let $item = $(item).css({
        'background-image' : 'url('+ res_item.img_main +')',
      });
      
      // когда кликают смотрим это амазон? значит id(amazon) формируем ссылку на амазон
      $item.data('p_id',res_item.id);
      $item.data('node_id',1);
      
      $item.data('img',res_item.img_main);
      $item.data('title',res_item.title);
      //$item.data('direction',direction); // left [B5464**] right
      //$item.data('p_side', 'p_left');    // p_left = это amazon
      
      wrapper.append($item);
      
      if (wrapper.selector === '.js__main_prev_wrapper') break;
    }
  }
  
  lib.get_dynamic_elements.result_handler_select = function(res_items,wrapper){
    wrapper.html('');

    function create_options(res_items,add_class,selected){
      for (let i = 0; i < res_items.length ; i++){
        let res_item = res_items[i];
        let option = $(get_template('p_min_view_item__select'));
        option.data('id',res_item.id);
        option.text(res_item.asin);
        option.addClass(add_class || '');
        if (typeof selected !== 'undefined') option.prop('selected',true);
    
        wrapper.append(option);
      }
    }
    
    let prev_items = res_items.prev_items;
    let this_item = res_items.this_item;
    let next_items = res_items.next_items;
  
    create_options(prev_items,'__prev');
    create_options(this_item,'__this',1);
    create_options(next_items, '__next');
  }
  
  lib.get_dynamic_elements.result_handler_right_products = function(res_items){
    
    lib.p_nav_right__swiper.removeAllSlides();
    
    for (let i in res_items){
      if (res_items.hasOwnProperty(i)){
        let r_item = res_items[i];
        let $item = $(get_template('p-nav-right__item'));
        
        let n = parseInt(i) + 1;
        if ( parseInt(start_settings.data('node')) === n ) $item.addClass('__current');
        
        $item.addClass(r_item.status);
        $item.data('node_id',n);
        $item.data('p_id',start_settings.data('p_id'));
        $item.data('text_brief',r_item.text_brief);
        $item.data('img',r_item.img_main);
        $item.append('<div class="p-min-view-item __item ">'+n+'</div>');
        // $item.css( {'background-image' : 'url('+ r_item.img_main +')'});
        
        
        lib.p_nav_right__swiper.appendSlide($item[0]);
      }
    }
    
  }
  
  
  lib.get_link_via_filters_now = function(p_id){
    // при клике смотрим на все фильтры и формируем ссылку
    
    let u = new URL(window.location.origin);
    let p = u.searchParams;
    // http://checker.loc/product/view?id=7854&source_id=1&comparisons=NOCOMPARE
    p.set('id' , p_id);
    p.set('comparisons' , '1');
    p.set('direction', 'next');
  
    // let url = a_.prop('protocol')+ '//' + a_.prop('hostname') + a_.prop('pathname') + a_.prop('search') + a_.prop('hash');
    //
    // let u = new URL(url);
    // u.pathname;   // '/product/view'
    // let params = u.searchParams;
    // for (let i in params_to_url){
    //   if (params_to_url.hasOwnProperty(i)) params.set(i,params_to_url[i]);
    // }
    //
    // url = u.pathname +'?'+ params.toString();
    // $(b).attr('href',url)
    
    return  u.pathname +'?'+ p.toString();
  }
  
  
  
  lib.get_arrow = function(change_links_in,data,cb){
    // http://checker.loc/product/view?comparisons=MATCH&id=2351&source_id=2
    
    let dataToSend = $.extend({
      p_id: false,
      comparison: false,
      source_id: false,
      direction: 'next',
    }, data || {} );
    
    
    if (!dataToSend.p_id || !dataToSend.comparison || !dataToSend.source_id) {
      console.log('Указаны не все данные для получения ссылки для '+ dataToSend.direction);
      return false;
    }
    let items = $(change_links_in);
    $.ajax({
      url: "/product/get_products_by_params",
      type: "POST",
      data: dataToSend,
      beforeSend: function(){
        // todo скрыть стрелки
        items.addClass('-hidden');
      },
      dataType: "json",
      success: function(response){
        if (response.res === 'ok'){
          
          
          // todo показать стрелки
          
          /*
          // установить новые ссылки
          if (typeof change_links_in !== 'undefined'){
            items.attr('href',response.link);
            items.removeClass('-hidden');
          }
         */
          
          
          
        }
  
        if (typeof cb === 'function') cb(response);
      }
    });
  }
  
  function get_template(select){
    if (select === 'p_min_view_item__l') return '<div class="[ p-min-view-item ] __ block main_product js__p-left js__dynamic-link"></div>';    //js__p-min-main-prev
    if (select === 'p_min_view_item__r') return '<div class="swiper-slide [ p-min-view-item ] __ block js__dynamic-link"></div>';
    if (select === 'p_min_view_item__select') return '<option class=""></option>';
    if (select === 'p-nav-right__item') return '<div class="swiper-slide [ p-min-view-item ] __ block js__p-right js__dynamic-link"></div>';
  
    /* amazon arrows */
    if (select === 'p_a_arrow_left') return '<button class="js__p-a-arrow-left js__dynamic-link prev prev-arr navigation-image-arr arrow left-img" title="Previous image - Item images">\n' +
      '            <svg class="gallery-svg" viewBox="0 0 12 20" width="8" height="14" focusable="false" aria-hidden="true">\n' +
      '              <path d="M10.0002222 0L0 9.9997778 10.0002222 20l1.6142581-1.6142581-8.4370764-8.3859641 8.4370764-8.4375209" fill-rule="evenodd" fill="#545658"></path>\n' +
      '            </svg>\n' +
      '          </button>\n';
    if (select === 'p_a_arrow_right') return '<button class="js__p-a-arrow-right js__dynamic-link next next-arr navigation-image-arr arrow left-img" title="Next image - Item images">\n' +
      '            <svg class="gallery-svg" viewBox="0 0 12 20" width="8" height="14" focusable="false" aria-hidden="true">\n' +
      '              <path d="M1.5622222 0L0 1.5622222 8.3853333 10 0 18.3857778 1.5622222 20l10-10" fill-rule="evenodd" fill="#545658"></path>\n' +
      '            </svg>\n' +
      '          </button>';
  
    /* p right arrows */
    if (select === 'p_r_arrow_left') return '<button data-direction="prev" class="js__p-r-arrow-left js__dynamic-link prev prev-arr navigation-image-arr arrow" role="button" aria-label="Previous image - Item images" title="Previous image - Item images">\n' +
      '  <svg class="gallery-svg" viewBox="0 0 12 20" width="8" height="14" focusable="false" aria-hidden="true">\n' +
      '    <path d="M10.0002222 0L0 9.9997778 10.0002222 20l1.6142581-1.6142581-8.4370764-8.3859641 8.4370764-8.4375209" fill-rule="evenodd" fill="#545658"></path>\n' +
      '  </svg>\n' +
      '</button>';
    if (select === 'p_r_arrow_right') return '<button data-direction="next" class="js__p-r-arrow-right js__dynamic-link next next-arr navigation-image-arr arrow" role="button" aria-label="Next image - Item images" title="Next image - Item images">\n' +
      '  <svg class="gallery-svg" viewBox="0 0 12 20" width="8" height="14" focusable="false" aria-hidden="true">\n' +
      '    <path d="M1.5622222 0L0 1.5622222 8.3853333 10 0 18.3857778 1.5622222 20l10-10" fill-rule="evenodd" fill="#545658"></path>\n' +
      '  </svg>\n' +
      '</button>';
   
    
  }
  
  
  
  
  function update_url_in_items(){
    lib.change_url_in_items( $('.slider__slider-item .linkImg.slider-item__link-img, .slider__page-n'), {
      comparisons: $('#arrow__filter-comparison').val()
    })
  }
  
  function click_to_right_item_filter(comparison){
    let _comparison = comparison || start_settings.data('comparison');

    
    if (['MATCH','MISMATCH','PRE_MATCH','OTHER','NOCOMPARE'].includes(_comparison)){
      $('.js-'+_comparison.toLowerCase())[0].click();
    }else{
      $('.js.-reset_filter_1234')[0].click();
    }
  
    update_url_in_items();
  }
  
  click_to_right_item_filter();

  
  /*
  lib.get_arrow($('.next.js__arrow'), {
    p_id: start_settings.data('p_id'),
    comparison: start_settings.data('comparison'),
    source_id: start_settings.data('source_id'),
    direction: 'next',
  }, function(response){
    _change_missall_link(response);
  });
  
  lib.get_arrow(  $('.prev.js__arrow'), {
    p_id: start_settings.data('p_id'),
    comparison: start_settings.data('comparison'),
    source_id: start_settings.data('source_id'),
    direction: 'prev',
  });
 */
  
  lib.get_dynamic_elements(  {
    p_id: start_settings.data('p_id'),
    comparison: start_settings.data('comparison'),
    source_id: start_settings.data('source_id'),
  });
  
  
  /* когда выбираем в выпадающем списке фильтр RESULT / MATCH /MISMATCH ... */
  $body.on('change','#product_page__filter-profile',function(){
    let comparison = $('.filter.__comparison-select').val();
  
    lib.get_dynamic_elements({
      p_id: start_settings.data('p_id'),
      comparison: comparison,
      source_id: start_settings.data('source_id'),
    });
  
    
  
  });
  $body.on('change','#arrow__filter-comparison, .filter.__comparison-select',function(){
    let comparison = $(this).val();
    click_to_right_item_filter(comparison)
    
    lib.get_dynamic_elements({
      p_id: start_settings.data('p_id'),
      comparison: comparison,
      source_id: start_settings.data('source_id'),
    },function(response){
      _change_missall_link(response);
    });
  
    /*
    lib.get_arrow(  $('.next.js__arrow'), {
      p_id: start_settings.data('p_id'),
      comparison: comparison,
      source_id: start_settings.data('source_id'),
      direction: 'next',
    },function(response){
      _change_missall_link(response);
    });
  
    lib.get_arrow(  $('.prev.js__arrow'), {
      p_id: start_settings.data('p_id'),
      comparison: comparison,
      source_id: start_settings.data('source_id'),
      direction: 'prev',
    });
   */
  
    
    
    // todo change url
  })
  
  $body.on('click','.js-missall',function(){
    let $data = $(this).data();

    $.ajax({
    	url: "/product/missall",
    	type: "POST",
    	data: $data,
    	beforeSend: function() {
     
    	},
    	dataType: "json",
    	success: function(response){
        window.location = $data.url_next;
    	}
    });
    
  })
  
  function _change_missall_link(response){
    let link =  response.link;

    //let missall_url = '/product/missall?id=' + start_settings.data('p_id') + '&source_id=' + start_settings.data('source_id') + '&return=1' + '&next_p=' + link;
    $('.js-missall').data({
      'id': start_settings.data('p_id'),
      'source_id': start_settings.data('source_id'),
      'return': 1,
      'url_next': link,
    })
    
  }
  
  
  $(window).on('resizeEnd',function(){
    //alert('1')
  });
 
  
  
  
})




