jQuery( document ).ready( function( $ ) {
	// Hides the section content.
	$( '.edd-vertical-sections.use-js .section-content' ).hide();

	// Shows the first section's content.
	$( '.edd-vertical-sections.use-js .section-content:first-child' ).show();

	// Makes the 'aria-selected' attribute true for the first section nav item.
	$( '.edd-vertical-sections.use-js .section-nav :first-child' ).attr( 'aria-selected', 'true' );

	// Copies the current section item title to the box header.
	$( '.which-section' ).text( $( '.section-nav :first-child a' ).text() );

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
			rents.find( '.section-title' ).attr( 'aria-selected', 'false' );

			// Set the `aria-selected` attribute to true for this section nav item.
			them.parent().attr( 'aria-selected', 'true' );

			// Maybe re-Chosen
			rents.find( 'div.chosen-container' ).css( 'width', '100%' );

			// Copy the current section item title to the box header.
			$( '.which-section' ).text( them.text() );
		}
	); // click()
} );
