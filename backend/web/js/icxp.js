$(function(){
  console.log('Work script')
  let start_settings = $('.icxp .js__settings .js__product.-icxp-settings');
  console.log(start_settings.data());
  
   
  /* страница продукта */
  let $body = $('body');
   
  
  $(window).on('resizeEnd',function(){
    //alert('1')
  });
 
  $(document).ready(function(){
    $('[data-toggle="popover"]').popover();
  });
    
})

