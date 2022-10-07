$(function(){
  let $body = $('body');
  $body.on('click','.-set_role',function(){
    let $this = $(this);
    let uid = $this.data('uid');
    if (!uid) return;
    let dataToSend = {};
        dataToSend.uid = uid;
    
    $.ajax({
      url: '/user/set_role',
      type: "POST",
      dataType: "json",
      data: dataToSend,
      beforeSend: function() {},
      success: function(response) {
        if (response.res === 'ok'){
          $this.text(response.role);
        }
      }
    });
  })
  
  

})

