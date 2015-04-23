(function($) {
    'use strict';
    /**
     * All of the code for your admin-specific JavaScript source
     * should reside in this file.
     *
     * Note that this assume you're going to use jQuery, so it prepares
     * the $ function reference to be used within the scope of this
     * function.
     *
     * From here, you're able to define handlers for when the DOM is
     * ready:
     *
     * $(function() {
     *
     * });
     *
     * Or when the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and so on.
     *
     * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
     * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
     * be doing this, we should try to minimize doing that in our own work.
     */
    $(function() {
    	var mode = $('#mode').val();
    	if(mode === '') {
    		$('#c4p_page, #c4p_url').hide();
    	} else if(mode === 'page') {
    		$('#c4p_page').show();
    		$('#c4p_url').hide();
    	} else if(mode === 'url') {
    		$('#c4p_url').show();
    		$('#c4p_page').hide();
    	}
    	$('#c4p_mode').change(function() {
    		var val = $(this).val();
    		$('#mode').val(val);
	    	if(val === 'page') {
	    		$('#c4p_page').show();
	    		$('#c4p_url').hide();
	    	} else if(val === 'url') {
	    		$('#c4p_url').show();
	    		$('#c4p_page').hide();
	    	} else if(val === '') {
	    		$('#c4p_page, #c4p_url').hide();
	    	}
	    })	
    })
    
})(jQuery);