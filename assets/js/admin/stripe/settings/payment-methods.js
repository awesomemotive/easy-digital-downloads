const toggles = document.querySelectorAll( '.edd-stripe-payment-method-toggle' );

toggles.forEach( function ( button ) {
	button.addEventListener( 'click', function ( e ) {
		e.preventDefault();
		toggleElement( this.dataset.toggle );
		toggles.forEach( function ( button ) {
			button.classList.remove( 'active' );
		} );
		this.classList.add( 'active' );
	} );
} );

function toggleElement( value ) {
	var elements = document.querySelectorAll( '.edd-stripe-payment-method' );
	for ( var i = 0; i < elements.length; i++ ) {
		elements[ i ].style.display = 'none';
	}

	var selectedElements;
	if ( ! value ) {
		selectedElements = elements;
	} else {
		selectedElements = document.querySelectorAll( '.edd-stripe-payment-method--' + value );
	}
	for ( var i = 0; i < selectedElements.length; i++ ) {
		selectedElements[ i ].style.display = 'block';
	}
}
