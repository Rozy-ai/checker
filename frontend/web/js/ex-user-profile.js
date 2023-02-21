jQuery(function ($) {
    function moveProfileFields($exProfileIdSelect) {
        let id = $exProfileIdSelect.children(':selected').val();
        $('#ex-user-profile-actual').children().appendTo('#ex-user-profile-defs');
        if (!id)
            return;
        $('.profile-id-' + id).appendTo('#ex-user-profile-actual');
    }
    $('.ex-user-profile').appendTo('#ex-user-profile-defs');

    $('#externaluser-ex_profile_id').on('change', function (e) {
        moveProfileFields($(this));
    });

    if ($('#externaluser-ex_profile_id').val()) {
        moveProfileFields($('#externaluser-ex_profile_id'));
    }
});