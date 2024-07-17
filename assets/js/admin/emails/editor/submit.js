document.querySelectorAll( '.edd-email-status-badge' ).forEach( function ( el ) {
	setTimeout( function () {
		if ( ! el.classList.contains( 'edd-hidden' ) ) {
			el.classList.add( 'edd-fadeout' );
		}
	}, 5000 );
} );

document.getElementById( 'submit' ).addEventListener( 'click', function ( event ) {
	document.querySelectorAll( '.edd-email-status-badge' ).forEach( function ( el ) {
		if ( !el.classList.contains( 'edd-hidden' ) ) {
			el.remove();
		} else {
			el.classList.remove( 'edd-hidden' );
		}
	} );
} );
