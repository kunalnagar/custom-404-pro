(function($) {
    'use strict';
    $(function() {
        $('#c4p_logs_download_options').change(function() {
            var val = $(this).val();
            if (val === '') {
                $('#c4p_logs_download').attr('disabled', true);
            } else {
                $('#c4p_logs_download').removeAttr('disabled');
            }
            $('#c4p_logs_download_format').val(val);
        })
        $('#c4p_logs_download').click(function() {
            $('#c4p_stats_table').tableExport({
                type: $('#c4p_logs_download_format').val(),
                escape: 'false'
            });
        });
        var mode = $('#mode').val();
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
        $('#clear_logs').click(function() {
        	var $that = $(this);
        	$(this).html('Clearing logs...Please wait');
        	$(this).attr('disabled', true);
        	$.ajax({
        		url: ajaxurl,
        		type: 'POST',
        		data: {
        			action: 'c4p_clear_logs'
        		},
        		success: function(data) {
        			if(data == 'done') {
        				$that.html('Clear Logs');
        				$that.removeAttr('disabled');
        				alert('Logs deleted!');
        			}
        		}
        	})
        });
    })
})(jQuery);
