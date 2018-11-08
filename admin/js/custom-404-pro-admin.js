(function ($) {
	
	'use strict';
	
	$(function () {
		
		$('#c4p_mode').on('change', function() {
			var $that = $(this);
			var val = $that.val();
			$('#c4p_page, #c4p_url').hide();
			console.warn(val);
			if(val === 'page') {
				$('#c4p_page').show();
			} else if(val === 'url') {
				$('#c4p_url').show();
			}
		});

		$('#c4p_mode').trigger('change');

	});

})(jQuery);
