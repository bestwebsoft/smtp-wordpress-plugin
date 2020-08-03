( function( $ ) {
	$( document ).ready( function() {
		// functionaluty for showing error when filesize is bigger than maxfilesize
		$( '#bwssmtp_test_file_attach' ).on( 'change', function() {
			var fileSize = this.files[0].size;
			var maxFileSize = $( '#bwssmtp_test_file_attach_size' ).val().replace( /[a-zA-Z]/gim, '' );
			maxFileSize = maxFileSize * Math.pow(10, 6);
			if ( maxFileSize < fileSize ) {
				$( '#bwssmtp_test_send' ).bind( 'click.myclick', function( e ) {
					e.preventDefault();
				} );
				$( '#bwssmtp_error_to_show' ).show();
			} else {
				$( '#bwssmtp_test_send' ).unbind( '.myclick' );
				$( '#bwssmtp_error_to_show' ).hide();
			}
		} );
	} );
} )( jQuery );