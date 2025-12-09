document.addEventListener( 'click', function ( event ) {
	if ( !event.target.classList.contains( 'edd-button__copy' ) ) {
		return;
	}
	const target = event.target.getAttribute( 'data-clipboard-target' );
	const element = document.querySelector( target );

	element.select();
	element.setSelectionRange( 0, 99999 );
	navigator.clipboard.writeText( element.value );

	const originalText = event.target.innerText;

	event.target.classList.add( 'updated-message' );
	event.target.innerText = edd_vars.copy_success;

	setTimeout( function() {
		event.target.classList.remove( 'updated-message' );
		event.target.innerText = originalText;
	}, 2000 );
} );
