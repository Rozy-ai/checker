jQuery(function ($) {
    $("[data-toggle='tooltip']").tooltip();
});

jQuery(function ($) {
    $('#profile-configs').on('click', '.profile-field-item', function (e) {
        console.log(".profile-field-item clicked");

        let $clone = $('#profile-config-blank').clone(false).removeAttr('id').css("display", "");
        $clone.find('input[type=hidden]').val($(this).data('id'));
        $clone.find('.field-name').html($(this).data('name'));
        // $clone.find('.field-comment').html($(this).data('comment'));

        $('#profile-configs').append($clone);
    });
    $('#profile-configs').on('click', '.remove-profile-config', function () {
        $(this).parent().parent().remove();
    });
});