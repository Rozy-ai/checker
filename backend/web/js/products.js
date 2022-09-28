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
})

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
