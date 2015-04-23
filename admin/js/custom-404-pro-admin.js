(function($) {
    'use strict';
    $(function() {
        var mode = $('#mode').val();
        console.log(mode);
        if (mode === '') {
            $('#c4p_page, #c4p_url').hide();
        } else if (mode === 'page') {
            $('#c4p_page').show();
            $('#c4p_url').find('input').removeAttr('required');
            $('#c4p_url').hide();
        } else if (mode === 'url') {
            $('#c4p_url').find('input').attr('required', true);
            $('#c4p_url').show();
            $('#c4p_page').hide();
        }
        $('#c4p_mode').change(function() {
            var val = $(this).val();
            $('#mode').val(val);
            if (val === 'page') {
                $('#c4p_page').show();
                $('#c4p_url').find('input').removeAttr('required');
                $('#c4p_url').hide();
            } else if (val === 'url') {
                $('#c4p_url').find('input').attr('required', true);
                $('#c4p_url').show();
                $('#c4p_page').hide();
            } else if (val === '') {
                $('#c4p_page, #c4p_url').hide();
            }
        })
    })
})(jQuery);