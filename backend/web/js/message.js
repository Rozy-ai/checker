$(function(){
  
  function show_hide_fields(){
    let v =  $('#message-settings__table_rows_id').val();
    let col_2 = $('.js-settings-root .col:nth-child(2)');
    let col_3 = $('.js-settings-root .col:nth-child(3)');
    let compare_field = $('#message-settings__compare_field');
    let settings__visible_all_row = $('.settings__visible_all_row');
    let settings__visible_all_input = $('#message-settings__visible_all');
    
    
    if (parseInt(v) === -1){
      col_2.hide();
      col_3.hide();
      settings__visible_all_row.show();
      
    }else{
      col_2.show();
      col_3.show();
      
      // снимаем [ ] Видят все пользователи
      settings__visible_all_input.prop('checked', false);
      
      settings__visible_all_row.hide();
      if (parseInt($('#message-settings__compare_symbol').val()) === -1){
        compare_field.hide();
      }else{
        compare_field.show();
      }
    }
  }
  
  show_hide_fields();
  
  $('form').on('change','select',function(){
    show_hide_fields();
  })

})