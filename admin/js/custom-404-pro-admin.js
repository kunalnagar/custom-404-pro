( function( $ ){
	$( document ).ready(
		function () {
				$( '#c4p_mode' ).on(
					'change',
					function () {
						var $that = $( this );
						var val   = $that.val();
						$( '#mode_url' ).removeAttr( 'required' );
						$( '#c4p_page, #c4p_url' ).hide();
						console.warn( val );
						if (val === 'page') {
							  $( '#c4p_page' ).show();
						} else if (val === 'url') {
							$( '#mode_url' ).attr( 'required', 'required' );
							$( '#c4p_url' ).show();
						}
					}
				);

				$( '#c4p_mode' ).trigger( 'change' );
		}
	);
})( jQuery );
