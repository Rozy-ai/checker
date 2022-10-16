$(document).ready(function(){
  
  let $body = $('body');


  $body.on('click','.product-list__product-list-item .slider_close.-in-list',function(){
    $(this).parents('.product-list__product-list-item').remove();
  })
  
  
  function del_item($this){
    let p_id = $this.data('pid');
    let source_id = $this.data('source_id');
  
    $.ajax({
      url: "/product/del_item?id="+p_id+'&source_id='+source_id,
      type: "GET",
      beforeSend: function() {
        $this.css('opacity','0.5');
      },
      dataType: "json",
        success: function(response){
          if (response.res === 'ok'){
              $this.remove();
          }
        },
      	error: function (jqXHR, exception) {
            if (jqXHR.status === 0) {
                    alert('Not connect. Verify Network.');
            } else if (jqXHR.status == 404) {
                    alert('Requested page not found (404).');
            } else if (jqXHR.status == 500) {
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
  
  $body.on('click','.js-del-item',function(){
    let q = confirm('Уверены?');
    if(!q) return false;
    
    let $this = $(this);
    let $root_item = $this.parents('.product-list__product-list-item');
    del_item($root_item);
  
  });
  
  $body.on('click','.js-del-all-visible-items',function(){
    let q = confirm('Уверены?');
    if(!q) return false;
    
    $('.product-list__product-list-item').each(function(a,b){
      //console.log($(b));
      del_item($(b));
    })
    
  })
  
  $body.on('click','.js-reset-compare-all-visible-items',function(){
    let q = confirm('Уверены?');
    if(!q) return false;
    
    $('.product-list__product-list-item').each(function(a,b){
      //console.log($(b));
      let $btn_reset_in_item = $(b).find('.js-reset-compare');
      if ($btn_reset_in_item.length) lib.reset_compare_item($btn_reset_in_item);
      console.log(a);
    })
    
    console.log('reload');
    if ($('#filter-items__comparisons').val() !== 'ALL') {
      window.location.reload();
    }

    
  })
  
  
  $body.on('click','.product-list__product-list-item  .product-list-item__btn-red, .product-list__product-list-item .slider__left-item__btn-yellow',function(e){
    e.stopPropagation();
    let $this = $(this);
    let $root = $this.parents('.product-list__product-list-item');
    let $block = $root.find('.slider__view-1');
  
    // определим количество товаров
    let $first_visible = $block.find('.slider__slider-item.item.slick-slide.slick-current');
    //let $items_block = $block.find('.slider__view-1._sliderTop.product-view__slider.slick-initialized.slick-slider');
    let items_block_width = $block.width();
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
    
    // slick-current slick-active
    
    console.log(items_list_nocompare);
    
    let x;
    if ($this.is('.slider__left-item__btn-yellow')){
      
      for (x = 0; items_list_nocompare.length > x; x++){
        items_list_nocompare[x].find('.slider__yellow_button')[0].click();
        
      }
    
    }else if($this.is('.product-list-item__btn-red')){
  
      for (x = 0; items_list_nocompare.length > x; x++){
        items_list_nocompare[x].find('.slider__red_button')[0].click();
        console.log(items_list_nocompare[x].find('.slider__red_button')[0]);
      }
  
    }
  
    
    /*
    *
      if ($('#filter-items__comparisons').val() !== 'YES_NO_OTHER' && $('#filter-items__comparisons').val() !== 'PRE_MATCH'){
        $root.remove();
      }
    * */
    
    
  });

  /* присваивание левому товару статуса STATUS_NOT_FOUND */
  $body.on('click','.product-list__item-mismatch-all',function(e){
    e.stopPropagation();
    let $this = $(this);
    let $root = $this.parents('.product-list__product-list-item');
    let url = $this.data('url');
    
    $.ajax({
    	url: url,
    	type: "GET",
    	beforeSend: function() {},
    	dataType: "json",
    	success: function(response){
            if (response.res === 'ok'){
                $root.remove();
            }
    	},
        error: function (jqXHR, exception) {
            if (jqXHR.status === 0) {
                    alert('Not connect. Verify Network.');
            } else if (jqXHR.status == 404) {
                    alert('Requested page not found (404).');
            } else if (jqXHR.status == 500) {
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
    
  });
  
  /*
  $body.on('click','.products-list__td2  .product-list-item__btn-red',function(e){
    console.log('btn');
    e.stopPropagation();
    let $this = $(this);
    let $root = $this.parents('.product-list__product-list-item');
    
    let u = new URL(window.location.href);
    let params = u.searchParams;
    if (!$('input[name=filter-items__no-compare]:checked').length){
      $root.remove();
    }else{
      $root.find('.product-view__slider').remove();
    }
    let pid = $root.data('pid');
    let source_id = $root.data('source_id');

    $.ajax({
    	url: '/product/missall?id='+pid+'&return=1'+'&source_id='+source_id,
    	type: "get",
    	beforeSend: function() {
    
    	},
    	dataType: "json",
    	success: function(response){
        if (response.res === 'ok'){}
    	}
    });
  })
  
*/
  
  /* YELLOW BTN */
  $('body').on('click','.slider__yellow_button',function(e){
    let $this = $(this);
    let $item = $this.parents('.slider__slider-item');
    
    //lib.change_statistics_cnt($item,'match');
    lib.change_statistics_cnt($item,'pre_match');
    
    let $slider_block = $this.parents('.products-list__slider-wrapper');
    let $root = $this.parents('.product-list__product-list-item');
  
    
  
    // определяем какой это view 1 (short) || view 2 (detail)
    let $view__slider = $this.parents('.product-view__slider');
    let view = '.slider__view-1'; // short
    if (!$view__slider.hasClass('slider__view-1')){
      view = '.slider__view-2';  // detail
    }
    
    if($('#filter-items__comparisons').val() !== 'ALL'){
      // $item.remove();
      
      let $slider = $root.find('._sliderTop');
      // c первого по ТЕКУЩИЙ
      let this_index = $item.prevAll().length;
      $slider.slick('slickRemove', this_index);
      
    }
    
    console.log($slider_block);
    
    if (!$slider_block.find(view + ' .slider__yellow_button').length){
      $slider_block.remove();
  

      let u = new URL(window.location.href);
      let params = u.searchParams;
      
      if ($('#filter-items__comparisons').val() !== 'YES_NO_OTHER' && $('#filter-items__comparisons').val() !== 'PRE_MATCH'){
        $root.remove();
      }
    }
  
    let url = $this.data('link');
    if (!url) return false;
    // console.log(url+'&list=1');
    // return 1;
    $.ajax({
      url: url+'&list=1',
      type: "get",
      beforeSend: function() {},
      dataType: "json",
      success: function(response){
        $item.find('.color-marker')
             .removeClass('nocompare').removeClass('other').removeClass('mismatch').removeClass('match').addClass('pre_match');
        $item.find('.slider__red_button').removeClass('-hover');
        $item.find('.slider__yellow_button').addClass('-hover');
  
        
      }
    });
    
  })
  
  /* RED BTN */
  $('body').on('mousedown click','.slider__red_button',function(e){
    let $this = $(this);
    let $item = $this.parents('.slider__slider-item');
    
    lib.change_statistics_cnt($item,'mismatch');
    
    // определяем какой это view 1 (short) || view 2 (detail)
    let $view__slider = $this.parents('.product-view__slider');
    
    let view = '.slider__view-1'; // short
    if (!$view__slider.hasClass('slider__view-1')){
      view = '.slider__view-2';  // detail
    }

    let $slider_block = $this.parents('.products-list__slider-wrapper');
   
    let $root = $this.parents('.product-list__product-list-item');
  
    if ($('#filter-items__comparisons').val() !== 'MISMATCH'){
      if ($('#filter-items__comparisons').val() !== 'ALL'){
        //$item.remove();
  
        let $slider = $root.find('._sliderTop');
        // c первого по ТЕКУЩИЙ
        let this_index = $item.prevAll().length;
        $slider.slick('slickRemove', this_index);
      }
    }
    
    if (!$slider_block.find(view + ' .slider__red_button').length){
      $slider_block.remove();
  
      let u = new URL(window.location.href);
      let params = u.searchParams;
      if (!$('input[name=filter-items__no-compare]:checked').length){
        $root.remove();
      }
    }
    
    let url = $this.data('link');
    if (!url) return false;
    
    console.log(url);
    
    $.ajax({
      url: url+'&list=1',
      type: "get",
      beforeSend: function() {},
      dataType: "json",
      success: function(response){
        $item.find('.color-marker')
             .removeClass('other')
             .removeClass('pre_match')
             .removeClass('nocompare')
             .removeClass('match')
             .addClass('mismatch');
        $item.find('.slider__yellow_button').removeClass('-hover');
        $item.find('.slider__red_button').addClass('-hover');
        
      },
        error: function (jqXHR, exception) {
            if (jqXHR.status === 0) {
                    alert('Not connect. Verify Network.');
            } else if (jqXHR.status == 404) {
                    alert('Requested page not found (404).');
            } else if (jqXHR.status == 500) {
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
    
  })
  
  let submit = $('.products__filter-submit')[0];
  $('.products__filter-form select').on('change',function(){
    submit.click();
  })
  
  
  
  
  $('body').on('scroll',function(){
    
    let block = $('.products__products-list')[0].getBoundingClientRect();
    
    let height = $('.products__filter-items').height() + $('#w0').height();
    
    if (block.top < height){
      console.log( 'go' );
      // -hidden  REMOVE
      $('.navbar__fixed-slider.-hidden').removeClass('-hidden')
      
      if ( $('.position-1 .products__filter-items').length )
        $('.position-2').append( $('.position-1 .products__filter-items') );
        $('.position-2').parents('.navbar__fixed-slider').css('display', 'block');
      
      let height = $('.navbar__fixed-slider').height() + 35 + $('#w0').height();
      $('section.home').css('padding-top', height); // 262px
  
      $('.navigation').hide();
      $('.js-title-and-source_selector').hide();
    
    }else{
      
    
      console.log( 'back' );
      $('.navbar__fixed-slider').addClass('-hidden')
      if ( $('.position-2 .products__filter-items').length )
        $('.position-1').append( $('.position-2 .products__filter-items') )
      $('section.home').css('padding-top', '')
      
      $('.position-2').parents('.navbar__fixed-slider').css('display', '');
    
      $('.navigation').show();
      $('.js-title-and-source_selector').show();
  
    }
    
    
  })
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  let cb = {
    'apply_match': function(evt){
      if(confirm('Товары совпадают?')){
        let id = $(this).data('product');
        let node = $(this).data('node');
        $.get("/product/compare", {id: id, node: node, status: 'MATCH', index: 1})
         .done(function(data){
           if(data.status === 'OK'){
             $.pjax.reload('#product-index-pjax', {replace: true, push: false});
           }
         });
      }
    },
    'set': function(){
      $('button.apply-match').click(cb.apply_match);
    }
  };
  cb.set();
  $('#product-index-pjax').on('pjax:end', cb.set);
  

  $('.sliderTop').slick({
    infinite: true,
    arrows: true,
    slidesToShow: 3,
    slidesToScroll: lib._visible_cnt_right_items(),
  });
  
  
  /*
  $body.on('click','.js-import-from-sql',function(){
    let $this = $(this);
    let source_id = $this.data('source_id');
    
    let dataToSend = {};
    dataToSend.use_import_sql_file_path = 1;
    dataToSend.source_id = source_id;
    $.ajax({
    	url: "/import/step_2",
    	type: "POST",
    	data: dataToSend,
    	beforeSend: function() {
     
    	},
    	dataType: "json",
    	success: function(response){
    	  if (response.res === 'ok'){
          
          //console.log(response.info);
          let res = response.info;
          let str = 'Результат импорта из sql фаила:' + "\n";
          for (let i in res){
            str += i +':'+ res[i] + "\n";
          }
          alert(str);
    	    //document.location.reload();
        }
    	}
    });
    
  })
  */

  
  
});



