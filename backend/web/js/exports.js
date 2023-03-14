$(function(){
  function id_position(){
    let out = [];
    $('.export_items .item').each(function(a,b){
      let out_ = {};
      out_['id'] = $(b).data('item_id');
      out_['position'] = $(b).find('.-position').text().trim();
      out.push(out_);
    });
      return out;
  }
  
  
  $('.export_items').sortable({
    containment: "parent",
    update: function(){
      update_position(function(){

        let dataToSend = {};
        dataToSend.id_position = id_position();
        console.log(dataToSend);
        $.ajax({
          url: "/exports/change_position",
          type: "POST",
          data: dataToSend,
          beforeSend: function() {
      
          },
          dataType: "json",
          success: function(response){
            console.log('in db changed');
          }
        });
      
      
      });
      
      
      
      /*

      
       */
      
    }
  });
  
  $('.item_checkbox').on('click',function(){
    let $this = $(this);
    let $item = $this.parents('.item');
    let item_id = $item.data('item_id');
    let source_id = $this.parents('.js-table-root').data('source_id');
    let checked = $this.prop('checked');
  
    let dataToSend = {};
    dataToSend.source_id = source_id;
    dataToSend.checked = checked ? 1 : 0;
    dataToSend.id = item_id;
    $.ajax({
      url: "/exports/select_one",
      type: "POST",
      data: dataToSend,
      beforeSend: function() {
      
      },
      dataType: "json",
      success: function(response){
      }
    });
    
  });
  
  $('.js-select-all').on('click',function(){
    let $this = $(this);
    let source_id = $this.parents('.js-table-root').data('source_id')
    let checked = $this.prop('checked');
    if (!checked) $('.item_checkbox').prop('checked',false);
    else $('.item_checkbox').prop('checked',true);
    
    let dataToSend = {};
    dataToSend.source_id = source_id;
    dataToSend.checked = checked ? 1 : 0;
    $.ajax({
    	url: "/exports/select_all",
    	type: "POST",
    	data: dataToSend,
    	beforeSend: function() {
    
    	},
    	dataType: "json",
    	success: function(response){
    	}
    });
    
    
  })
  
  function update_position(cb){
    let $this = $(this);
    $('.item .-position').each(function(a,b){
      $(b).text(a)
    })
    if (typeof cb === 'function') cb($this);
  }
  
  update_position();
  
  $('.js-step-4').on('click',function(){
    let $this = $(this);
    let $root = $('.js-table-root');
    let $items = $('.export_items .item');
    let source_id = $root.data('source_id');
    let comparisons = $root.data('comparisons');
    let profile = $root.data('profile');
    let is_new = $root.data('new');
    
    let out = [];
    $items.each(function(a,b){
      let item = {};
      item.id = $(b).data('item_id');
      item.checked = $(b).find('.item_checkbox:checked').length ? 1 : 0;
      item.name = $(b).data('name')
      item.type = $(b).data('type')
      out.push(item);
    })
    
    let dataToSend = {};
    dataToSend.items = out;
    dataToSend.source_id = source_id;
    dataToSend.comparisons = comparisons;
    dataToSend.profile = profile;
    dataToSend.is_new = is_new;
    $.ajax({
    	url: "/exports/step_4",
    	type: "POST",
    	data: dataToSend,
    	beforeSend: function() {
    
    	},
    	dataType: "json",
    	success: function(res){
        if (res.res === 'ok'){
          window.location = '/'+res.file;
        }
    	}
    });
    
  });

})