const adminPage = document.querySelector( '.edd-admin-page' );
let navWrapper = document.querySelector( '.edd-nav__wrapper' );

if ( adminPage ) {

	if ( navWrapper ) {
		// Move the subtitle inside the navWrapper.
		const subtitle = document.querySelector( '.subtitle:not(.edd-search-query)' );
		if ( subtitle ) {
			navWrapper.appendChild( subtitle );
		}
	}

	// Move the notices after the navWrapper.
	const adminNotices = document.querySelectorAll( '.notice:not(.inline)' );
	if ( adminNotices ) {
		setTimeout( () => {
			if ( navWrapper ) {
				const subNav = document.querySelector( '.edd-sub-nav__wrapper' );
				if ( subNav ) {
					navWrapper = subNav;
				}
				const navWrapperParent = navWrapper.parentNode;
				adminNotices.forEach( notice => {
					navWrapperParent.insertBefore( notice, navWrapper.nextSibling );
				} );
			}
			adminNotices.forEach( notice => {
				// If the notice doesn't have the 'hidden' class, display it.
				if ( ! notice.classList.contains( 'hidden' ) ) {
					notice.style.display = 'block';
				}
			} );
		}, 1000 );
	}
}
