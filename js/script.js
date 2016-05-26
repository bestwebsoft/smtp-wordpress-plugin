( function( $ ) {
	$( document ).ready( function() {
		/* Manage fields which contains errors.*/
		$( 'input.bwssmtp_error' ).bind( 'input paste change', function() {
			$( this ).removeClass( 'bwssmtp_error' );
		});

		/* Manage SMTP Authentication */
		$( '#bwssmtp_authentication' ).change( function() {
			if ( ! $( this ).is( ':checked' ) ) {
				$( '.bwssmtp_authentication_settings' ).addClass( 'bwssmtp_hidden' );
			} else {
				$( '.bwssmtp_authentication_settings' ).removeClass( 'bwssmtp_hidden' );
			}
		});

		/* Manage SMTP Authentication */
		$( '#bwssmtp_use_plugin_settings_from' ).change( function() {
			if ( ! $( this ).is( ':checked' ) ) {
				$( '.bwssmtp_plugin_settings_from' ).addClass( 'bwssmtp_hidden' );
			} else {
				$( '.bwssmtp_plugin_settings_from' ).removeClass( 'bwssmtp_hidden' );
			}
		});

		/* Enter only numbers in the Port field. */
		$( '#bwssmtp_port' ).keydown( function( e ){
			var key = e.charCode || e.keyCode || 0;
			var keys = [ 8, 9, 46, 49, 50, 51, 52, 53, 54, 55, 56, 57, 48, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105 ];
			return ( keys.indexOf( key ) != -1 ) ? true : false;
		});
	});
})( jQuery );