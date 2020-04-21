jQuery( document ).ready( function( $ ) {
	// Hides the section content.
	$( '.edd-vertical-sections.use-js .section-content' ).hide();

	var hash = window.location.hash;
	if ( hash && hash.includes( 'edd_' ) ) {
		// Show the section content related to the URL.
		$( '.edd-vertical-sections.use-js' ).find( hash ).show();

		// Set the aria-selected for section titles to be false
		$( '.edd-vertical-sections.use-js .section-title' ).attr( 'aria-selected', 'false' );

		// Set aria-selected true on the related link.
		$( '.edd-vertical-sections.use-js' ).find( '.section-title a[href="' + hash + '"]' ).parents( '.section-title' ).attr( 'aria-selected', 'true' );

	} else {
		// Shows the first section's content.
		$( '.edd-vertical-sections.use-js .section-content:first-child' ).show();

		// Makes the 'aria-selected' attribute true for the first section nav item.
		$( '.edd-vertical-sections.use-js .section-nav li:first-child' ).attr( 'aria-selected', 'true' );
	}

	// When a section nav item is clicked.
	$( '.edd-vertical-sections.use-js .section-nav li a' ).on( 'click',
		function( j ) {
			// Prevent the default browser action when a link is clicked.
			j.preventDefault();

			// Get the `href` attribute of the item.
			const them = $( this ),
				href = them.attr( 'href' ),
				rents = them.parents( '.edd-vertical-sections' );

			// Hide all section content.
			rents.find( '.section-content' ).hide();

			// Find the section content that matches the section nav item and show it.
			rents.find( href ).show();

			// Set the `aria-selected` attribute to false for all section nav items.
			rents.find( '.section-title' ).attr( 'aria-selected', 'false' ).removeClass( 'section-title--is-active' );

			// Set the `aria-selected` attribute to true for this section nav item.
			them.parent().attr( 'aria-selected', 'true' ).addClass( 'section-title--is-active' );

			// Maybe re-Chosen
			rents.find( 'div.chosen-container' ).css( 'width', '100%' );

			// Add the current "link" to the page URL
			window.history.pushState( 'object or string', '', href );
		}
	); // click()
} );
