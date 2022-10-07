$(function(){
  if ($('#dynamicmodel-source_id').val() === ''){
    let source_id = $('.export_step_2_title').data('source_id');
    console.log(source_id);
    $('#dynamicmodel-source_id').val(source_id);
  }
  
  function set_url(){
    let source_id = $('.export_step_2_title').data('source_id');
    let comparison = $('#dynamicmodel-comparisons input:checked').val();
    let profile = $('#dynamicmodel-profile').val()
    let url = '/exports/step_4?source_id='+ source_id +'&comparisons='+ comparison +'&profile='+profile+'';
    $('a.js-download').prop('href', url);
  }
  
  
  set_url();
  
  $('body').on('click','.js-download',function(){
    set_url();
  })
  
  $('body').on('click','#dynamicmodel-use_previous_saved',function(){
    if (this.checked){
      $('#dynamicmodel-ignore_step_3').parent().show();
    }else{
      $('#dynamicmodel-ignore_step_3').parent().hide();

    }
  });
  
  $('body').on('click','#dynamicmodel-ignore_step_3',function(){
  
    set_url();
    
    if (this.checked){
      $('.js-next').hide();
      $('a.js-download').show();
    }else{
      $('.js-next').show();
      $('a.js-download').hide();
    }
  })
  
})