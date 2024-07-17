const newEmail = document.getElementById( 'edd-emails__add' );
const overlay = document.querySelector( '.edd-emails__add-new__overlay' );
if ( newEmail ) {
	newEmail.addEventListener( 'click', e => {
		e.preventDefault();

		// if the overlay has a display:none, remove the style
		if ( overlay.style.display === 'none' ) {
			overlay.removeAttribute( 'style' );
		} else {
			overlay.style.display = 'none';
		}
	} );

	document.addEventListener( 'click', e => {
		if ( newEmail === e.target || overlay.style.display === 'none' ) {
			return;
		}
		if ( overlay.style.display !== 'none' ) {
			setTimeout( function () {
				if ( !e.target.closest( '.edd-emails__add-new' ) && !e.target.closest( '.edd-emails__add-new__overlay' ) ) {
					overlay.style.display = 'none';
				}
			}, 100 );
		}
	} );
}

const addNewEmail = document.querySelectorAll( 'button.edd-emails__add-new' );
if ( addNewEmail ) {
	addNewEmail.forEach( addNewEmail => {
		addNewEmail.addEventListener( 'click', e => {
			e.preventDefault();
			if ( !addNewEmail.classList.contains( 'edd-promo-notice__trigger' ) ) {
				window.location.href = EDDAdminEmails.link + '&email=' + addNewEmail.getAttribute( 'data-value' );
			} else {
				setTimeout( function () {
					overlay.style.display = 'none';
				}, 5000 );
			}
		} );
	} );
}
