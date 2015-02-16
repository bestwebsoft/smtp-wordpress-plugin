( function( $ ) {
	$( document ).ready( function() {

		/* Manage notices. */
		$( '#bwssmtp_settings_form input' ).bind( 'input paste change', function() {
			var bwssmtp_field = $( this ).attr( 'id' );
			$( '.bwssmtp_notice.bwssmtp_notice_error' ).filter( '.error_' + bwssmtp_field ).remove();
			if ( $( '.bwssmtp_notice.bwssmtp_notice_info' ).length == 0 ) {
				var bwssmtp_notice = $('<div/>', {
					'class' : 'bwssmtp_notice bwssmtp_notice_info',
					'html'  : '<p>' + bwssmtp_translation['new_settings'] +'</p>'
				}).insertBefore( '#bwssmtp_settings_form' );
				$( '.success_settings, .error_settings' ).remove();
			}
		});

		/* Manage fields which contains errors. */
		$( 'input.bwssmtp_error' ).bind( 'input paste change', function() {
			$( this ).removeClass( 'bwssmtp_error' );
		});

		// Manage SMTP Authentication.
		$( '#bwssmtp_authentication' ).change( function() {
			if ( ! $( this ).is( ':checked' ) ) {
				$( '.bwssmtp_authentication_settings' ).addClass( 'bwssmtp_hidden' );
			} else {
				$( '.bwssmtp_authentication_settings' ).removeClass( 'bwssmtp_hidden' );
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