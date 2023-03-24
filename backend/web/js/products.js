$(function(){
  $('body').on('scroll',function(){
  
    
  })
  
  lib.slider_init();
  /*
  $('._sliderTop').slick({
    infinite: false,
    arrows: true,
    slidesToScroll: lib._visible_cnt_right_items(),
    variableWidth: true,
    slidesToShow: 1,
    
  });
  */
  
  $('#compare-table__select-item-2,#s_E_Sales,#s_eBay_stock').on('change',function(){
    let $this = $(this).find('option:selected');
    let id = $this.data('pid');
    let node = $this.data('nid');
    let source_id = $this.data('source_id');
    window.location = '/product/view?id='+id+'&source_id='+source_id+'&node='+node;
  })
  

  
})

$(document).ready(function(){

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
    statusCheck();
    
  
  $('body').on('click','.js-update',function(){
    let $data = $(this).data();
    debugger;
    $.ajax({
    	url: $data.url,
    	type: "GET",
    	beforeSend: function() {
            $( '#preloader' ).show();
    	},
    	dataType: "json",
    	success: function(response){
            
            if (  !response.error ) {
                datetime = new Date(response.time*1000).toLocaleString();
                $('.js-update').attr('title',response.message);
                $('.js-update:before').text(datetime);                
                //alert(/*Слава Богу*/'Импорт успешно выполнен! \n'+response.message);  
                window.location.reload();                                 
            } else {
                $( '#preloader' ).hide();
                alert('Ошибка импорта! '+response.message);
            }
            
    	},
        error: function (jqXHR, exception) {
            if (jqXHR.status === 0) {
                alert('Not connect. Verify Network.');
            } else if (jqXHR.status === 404) {
                alert('Requested page not found (404).');
            } else if (jqXHR.status === 500) {
                alert('Internal Server Error (500):'+jqXHR.responseText);                    
            } else if (exception === 'parsererror') {
                alert('Requested JSON parse failed.');
            } else if (exception === 'timeout') {
                alert('Time out error.');
            } else if (exception === 'abort') {
                alert('Ajax request aborted.');
            } else {
                alert('Ошибка импорта: '.jqXHR.responseText);
            }
            $( '#preloader' ).hide();
        }
    });    
  });  

})

$('.slider__yellow_button').click(function(){
    statusCheck();
})
$('.slider__red_button').click(function(){
    statusCheck();
})

// marker
$('body').on('mouseover', '.slider__right-item-other-marker', function (e) {
  $(this).find('.slider__right-item-other-marker_image').show();
})

$('body').on('mouseout', '.slider__right-item-other-marker', function (e) {
  $(this).find('.slider__right-item-other-marker_image').hide();
})
//


window.addEventListener('load', function(){
  
  $('._____sliderTop').slick({
    infinite: false,
    arrows: true,
    slidesToScroll: 3,
    variableWidth: true,
    slidesToShow: 3,
    
  });
  
  
  $(window).on('resizeEnd',function(){
    //alert('1')
  });
  

})

function statusCheck()
{
/*
    $(".product-list__product-list-item").each(function(index,value){
        let title = $(this).find('.main-item-title').text();
        let mismatch=$(this).find('.mismatch').text();
        let processed=$(this).find('.product-list-item__processed').text();
        let total=processed.split('/');

        //console.log(title+' '+mismatch+' - '+total[1])
        if (parseInt(mismatch)==parseInt(total[1]))
        {
            $(this).find('.products-list__td1 .product-list-item__data:last-child').html('<span>Status:</span> ');
            $(this).find('.products-list__td1 .product-list-item__data:last-child>span').after('<p style="color:#c13737"/>Not found</p>');
        }
        else
        {
            $(this).find('.products-list__td1 .product-list-item__data:last-child').html('<span>Status:</span>');
            $(this).find('.products-list__td1 .product-list-item__data:last-child>span').after('<p/>Not check</p>');
        }
    });
*/
}